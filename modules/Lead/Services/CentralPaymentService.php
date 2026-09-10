<?php

declare(strict_types=1);

namespace Modules\Lead\Services;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Modules\Beautician\Entities\Beautician;
use Modules\Order\Entities\Order;
use Modules\Payment\Services\PaymentMethodLabel;
use Modules\SpaBranch\Entities\SpaBranch;

final class CentralPaymentService
{
    /**
     * @param  array{
     *     q?:string|null,
     *     customer_id?:int|string|null,
     *     status?:string|null,
     *     branch?:int|string|null,
     *     beautician?:int|string|null,
     *     from?:Carbon|null,
     *     to?:Carbon|null,
     *     per_page?:int
     * }  $filters
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $perPage = max(1, min(100, (int) ($filters['per_page'] ?? 25)));

        $query = $this->baseQuery($filters)
            ->with([
                'spaBranch:id,name,code',
                'beautician:id,first_name,last_name',
                'transaction',
                'paymentProof:id,filename,mime,extension',
            ])
            ->latest('id');

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array{
     *     customer_id?:int|string|null,
     *     branch?:int|string|null,
     *     from?:Carbon|null,
     *     to?:Carbon|null
     * }  $filters
     * @return array{
     *     pending:int,
     *     processing:int,
     *     paid:int,
     *     paid_today:int,
     *     hold:int,
     *     refunded:int,
     *     paid_amount:float,
     *     pending_amount:float,
     *     queue:int
     * }
     */
    public function summary(array $filters = []): array
    {
        $base = $this->scopedOrders($filters);

        $counts = (clone $base)
            ->selectRaw('payment_status, COUNT(*) as c')
            ->groupBy('payment_status')
            ->pluck('c', 'payment_status');

        $pending = (int) ($counts[Order::PAYMENT_PENDING] ?? 0);
        $processing = (int) ($counts[Order::PAYMENT_PROCESSING] ?? 0);
        $paid = (int) ($counts[Order::PAYMENT_PAID] ?? 0);
        $hold = (int) ($counts[Order::PAYMENT_CANCELED] ?? 0);
        $refunded = (int) ($counts[Order::PAYMENT_REFUNDED] ?? 0);

        $paidAmount = (float) (clone $base)
            ->where('payment_status', Order::PAYMENT_PAID)
            ->sum('total');

        $pendingAmount = (float) (clone $base)
            ->whereIn('payment_status', [Order::PAYMENT_PENDING, Order::PAYMENT_PROCESSING])
            ->sum('total');

        $paidToday = (int) (clone $base)
            ->where('payment_status', Order::PAYMENT_PAID)
            ->whereDate('updated_at', Carbon::today())
            ->count();

        return [
            'pending' => $pending,
            'processing' => $processing,
            'paid' => $paid,
            'paid_today' => $paidToday,
            'hold' => $hold,
            'refunded' => $refunded,
            'paid_amount' => round($paidAmount, 2),
            'pending_amount' => round($pendingAmount, 2),
            'queue' => $pending + $processing,
        ];
    }

    /**
     * @return list<array{value:string,label:string}>
     */
    public function statusOptions(): array
    {
        return [
            ['value' => 'all', 'label' => trans('lead::central.payments.tab_all')],
            ['value' => Order::PAYMENT_PENDING, 'label' => trans('lead::central.payments.tab_pending')],
            ['value' => Order::PAYMENT_PROCESSING, 'label' => trans('lead::central.payments.tab_processing')],
            ['value' => Order::PAYMENT_PAID, 'label' => trans('lead::central.payments.tab_paid')],
            ['value' => Order::PAYMENT_CANCELED, 'label' => trans('lead::central.payments.tab_hold')],
            ['value' => Order::PAYMENT_REFUNDED, 'label' => trans('lead::central.payments.tab_refunded')],
            ['value' => 'queue', 'label' => trans('lead::central.payments.tab_queue')],
        ];
    }

    /**
     * @return list<array{id:int,name:string}>
     */
    public function beauticianOptions(): array
    {
        return Beautician::query()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name'])
            ->map(static fn (Beautician $b) => [
                'id' => (int) $b->id,
                'name' => trim($b->first_name.' '.$b->last_name) ?: ('#'.$b->id),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{id:int,code:string,name:string}>
     */
    public function branchOptions(): array
    {
        return SpaBranch::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name'])
            ->map(static fn (SpaBranch $b) => [
                'id' => (int) $b->id,
                'code' => (string) ($b->code ?: ''),
                'name' => (string) $b->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Order $order, bool $canViewProof = false): array
    {
        $customer = trim((string) $order->customer_full_name);
        if ($customer === '') {
            $customer = trim(trim((string) $order->billing_first_name).' '.trim((string) $order->billing_last_name));
        }
        $hasCustomerName = $customer !== '';
        if ($customer === '') {
            $customer = trans('lead::central.payments.guest');
        }

        $phone = (string) ($order->customer_phone ?: '');
        $branch = $order->spaBranch;
        $beautician = $order->beautician;
        $beauticianName = $beautician
            ? trim($beautician->first_name.' '.$beautician->last_name)
            : '';

        $amount = $order->total;
        $amountValue = is_object($amount) && method_exists($amount, 'amount')
            ? (float) $amount->amount()
            : (float) $amount;

        $method = (string) $order->getRawOriginal('payment_method');
        $ref = trim((string) ($order->transaction?->transaction_id ?: ''));
        $status = (string) ($order->payment_status ?: Order::PAYMENT_PENDING);
        $hasProof = $order->hasPaymentProof() && $order->paymentProof !== null;
        $proof = $canViewProof ? $order->paymentProof : null;
        $proofData = null;
        if ($proof) {
            $mime = strtolower((string) $proof->mime);
            $extension = strtolower((string) $proof->extension);
            $kind = in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)
                ? 'image'
                : ($mime === 'application/pdf' ? 'pdf' : 'file');
            if ($mime === '') {
                $kind = in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)
                    ? 'image' : ($extension === 'pdf' ? 'pdf' : 'file');
            }
            $proofData = [
                'name' => (string) $proof->filename,
                'kind' => $kind,
                'url' => app(\Modules\Order\Services\OrderPaymentProofPublicUrlService::class)->whatsAppMediaUrl($proof, $order),
            ];
        }

        return [
            'id' => (int) $order->id,
            'code' => 'ORD-'.$order->id,
            'customer' => $customer,
            'phone' => $phone,
            'email' => (string) ($order->customer_email ?: ''),
            'initial' => mb_strtoupper(mb_substr($customer !== '' ? $customer : 'O', 0, 1)),
            'branch_id' => $order->spa_branch_id ? (int) $order->spa_branch_id : null,
            'branch' => $branch?->code ?: ($branch?->name ?: '—'),
            'branch_name' => $branch?->name ?: '—',
            'beautician_id' => $order->beautician_id ? (int) $order->beautician_id : null,
            'beautician' => $beauticianName !== '' ? $beauticianName : '—',
            'invoice' => 'INV-'.$order->id,
            'amount' => round($amountValue, 2),
            'payment_method' => $method,
            'payment_method_label' => PaymentMethodLabel::resolve($method) ?: $method,
            'ref' => $ref !== '' ? $ref : '—',
            'admin_note' => (string) ($order->transaction?->admin_note ?: ''),
            'has_proof' => $hasProof,
            'checklist' => [
                'identity' => $hasCustomerName && ($order->customer_id || trim($phone) !== '' || trim((string) $order->customer_email) !== '') ? 'completed' : 'pending',
                'invoice' => is_numeric($order->getRawOriginal('total')) && $amountValue >= 0 ? 'completed' : 'pending',
                'method' => trim($method) !== '' ? 'completed' : 'pending',
                'ref' => $ref !== '' ? 'completed' : (in_array(trim($method), ['', 'bank_transfer'], true) ? 'pending' : 'not_applicable'),
                'proof' => $hasProof ? 'completed' : (in_array(trim($method), ['', 'bank_transfer'], true) ? 'pending' : 'not_applicable'),
                'status' => $status === Order::PAYMENT_PAID ? 'completed' : 'pending',
            ],
            'proof' => $proofData,
            'customer_stage' => $hasProof
                ? trans('lead::central.payments.stage_proof')
                : trans('lead::central.payments.stage_declared'),
            'accountant_stage' => $this->accountantStage($status, $ref !== ''),
            'hq_stage' => $this->hqStage($status),
            'payment_status' => $status,
            'payment_status_label' => $order->paymentStatusLabel(),
            'order_status' => (string) $order->status,
            'created_at' => optional($order->created_at)?->toDateTimeString(),
            'updated_at' => optional($order->updated_at)?->toDateTimeString(),
            'needs_reference' => $method === 'bank_transfer'
                && in_array($status, [Order::PAYMENT_PENDING, Order::PAYMENT_PROCESSING], true),
        ];
    }

    private function accountantStage(string $status, bool $hasRef): string
    {
        return match ($status) {
            Order::PAYMENT_CANCELED => trans('lead::central.payments.stage_hold'),
            Order::PAYMENT_REFUNDED => trans('lead::central.payments.stage_refunded'),
            Order::PAYMENT_PAID => trans('lead::central.payments.stage_verified'),
            Order::PAYMENT_PROCESSING => trans('lead::central.payments.stage_reviewing'),
            default => trans('lead::central.payments.'.($hasRef ? 'stage_reference_saved' : 'stage_pending')),
        };
    }

    private function hqStage(string $status): string
    {
        return match ($status) {
            Order::PAYMENT_PAID => trans('lead::central.payments.stage_verified'),
            Order::PAYMENT_PROCESSING => trans('lead::central.payments.stage_hq_review'),
            Order::PAYMENT_CANCELED => trans('lead::central.payments.stage_hold'),
            Order::PAYMENT_REFUNDED => trans('lead::central.payments.stage_refunded'),
            default => trans('lead::central.payments.stage_pending'),
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function baseQuery(array $filters): Builder
    {
        $query = $this->scopedOrders($filters);

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $like = '%'.$q.'%';
            $query->where(function (Builder $inner) use ($q, $like): void {
                if (ctype_digit($q)) {
                    $inner->orWhere('id', (int) $q);
                }
                $inner->orWhere('customer_first_name', 'like', $like)
                    ->orWhere('customer_last_name', 'like', $like)
                    ->orWhere('customer_phone', 'like', $like)
                    ->orWhere('customer_email', 'like', $like)
                    ->orWhere('billing_first_name', 'like', $like)
                    ->orWhere('billing_last_name', 'like', $like)
                    ->orWhereHas('transaction', static function (Builder $tx) use ($like): void {
                        $tx->where('transaction_id', 'like', $like);
                    });
            });
        }

        $status = $filters['status'] ?? 'all';
        if ($status === 'queue') {
            $query->whereIn('payment_status', [Order::PAYMENT_PENDING, Order::PAYMENT_PROCESSING]);
        } elseif (is_string($status) && $status !== '' && $status !== 'all') {
            $query->where('payment_status', $status);
        }

        $beauticianId = $this->positiveIntOrNull($filters['beautician'] ?? null);
        if ($beauticianId !== null) {
            $query->where('beautician_id', $beauticianId);
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function scopedOrders(array $filters): Builder
    {
        $query = Order::query();

        $customerId = $this->positiveIntOrNull($filters['customer_id'] ?? null);
        if ($customerId !== null) {
            $query->where('customer_id', $customerId);
        }

        $branchId = $this->positiveIntOrNull($filters['branch'] ?? null);
        if ($branchId !== null) {
            $query->where('spa_branch_id', $branchId);
        }

        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;
        if ($from instanceof Carbon && $to instanceof Carbon) {
            $query->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);
        }

        return $query;
    }

    private function positiveIntOrNull(mixed $value): ?int
    {
        if ($value === null || $value === '' || $value === 'all') {
            return null;
        }

        $id = (int) $value;

        return $id > 0 ? $id : null;
    }
}
