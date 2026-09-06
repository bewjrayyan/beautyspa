<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Modules\Beautician\Entities\Beautician;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\User\Entities\User;

class PosBookingService
{
    public function __construct(
        private ManualBookingService $manualBookings,
        private TreatmentBookingActivityLogger $activityLogger,
        private ManualBookingPaymentReceiptService $paymentReceipts,
    ) {
    }

    public function create(array $data, User $actor): TreatmentBooking
    {
        $directIdempotency = ! isset($data['_pos_request_key']) && isset($data['request_key']);

        if ($directIdempotency) {
            $hash = $this->payloadHash($data);
            $existing = $this->existingBatch((string) $data['request_key'], $hash, $actor, 1);
            if ($existing !== null) {
                return $existing[0];
            }
            $data['_pos_request_key'] = (string) $data['request_key'];
            $data['_pos_line_index'] = 0;
            $data['_pos_payload_hash'] = $hash;
        }

        if (! isset($data['_payment_receipt_file_id']) && empty($data['payment_receipt'])) {
            throw new \InvalidArgumentException('An offline payment receipt is required.');
        }

        $data['payment_status'] = TreatmentBooking::PAYMENT_FULL_PAID;
        $customer = $this->customer($data['customer_id'], $actor);
        $this->assertBeauticianScope($actor, (int) $data['beautician_id']);

        $payload = $this->customerPayload($data, $customer);
        $source = $actor->isBeauticianOnly()
            ? TreatmentBooking::SOURCE_PORTAL_MANUAL
            : TreatmentBooking::SOURCE_ADMIN_MANUAL;

        try {
            return $this->manualBookings->create($payload, $actor, $source)
                ->load(['customer', 'beautician', 'product', 'category']);
        } catch (QueryException $exception) {
            if ($directIdempotency) {
                $existing = $this->existingBatch((string) $data['request_key'], (string) $data['_pos_payload_hash'], $actor, 1);
                if ($existing !== null) {
                    return $existing[0];
                }
            }

            throw $exception;
        }
    }

    /** @return list<TreatmentBooking> */
    public function createMany(array $data, User $actor): array
    {
        $requestKey = (string) $data["request_key"];
        $payloadHash = $this->payloadHash($data);
        $existing = $this->existingBatch($requestKey, $payloadHash, $actor, count($data["items"] ?? []));

        if ($existing !== null) {
            return $existing;
        }

        try {
            return DB::transaction(function () use ($data, $actor, $requestKey, $payloadHash) {
                $bookings = [];
                $holds = [];

                $receiptFileId = null;
                foreach ($data['items'] ?? [] as $index => $item) {
                    $customerHolds = array_map(function (array $hold) use ($item) {
                        $hold['beautician_id'] = (int) $item['beautician_id'];

                        return $hold;
                    }, $holds);
                    $payload = array_merge($data, $item, [
                        '_schedule_holds' => $customerHolds,
                        '_pos_request_key' => $requestKey,
                        '_pos_line_index' => $index,
                        '_pos_payload_hash' => $payloadHash,
                    ]);
                    if ($receiptFileId) {
                        $payload['_payment_receipt_file_id'] = $receiptFileId;
                    }

                    $booking = $this->create($payload, $actor);
                    $receiptFileId ??= $booking->payment_receipt_file_id;
                    $bookings[] = $booking;

                    if (! $booking->isTbaSchedule()) {
                        $holds[] = [
                            'beautician_id' => (int) $booking->beautician_id,
                            'appointment_date' => $booking->appointment_date->toDateString(),
                            'appointment_time' => substr((string) $booking->appointment_time, 0, 5),
                            'product_id' => (int) $booking->product_id,
                            'duration_minutes' => max(1, (int) ($booking->duration_minutes_snapshot ?: 60)),
                        ];
                    }
                }

                return $bookings;
            });
        } catch (QueryException $exception) {
            $existing = $this->existingBatch($requestKey, $payloadHash, $actor, count($data['items'] ?? []));
            if ($existing !== null) {
                return $existing;
            }

            throw $exception;
        }
    }

    public function update(TreatmentBooking $booking, array $data, User $actor): TreatmentBooking
    {
        return DB::transaction(function () use ($booking, $data, $actor) {
            $this->assertManual($booking);
            $previousReceiptId = $booking->payment_receipt_file_id;

            if (! $booking->payment_receipt_file_id && empty($data['payment_receipt'])) {
                throw new \InvalidArgumentException('An offline payment receipt is required.');
            }

            $requestedStatus = $data['status'] ?? null;
            $fields = array_diff_key($data, ['status' => true]);

            if ($fields !== []) {
                $fields['payment_status'] = TreatmentBooking::PAYMENT_FULL_PAID;
                $customer = $this->customer($data['customer_id'] ?? $booking->customer_id, $actor);
                $payload = array_merge([
                    'customer_id' => $booking->customer_id,
                    'customer_first_name' => $booking->customer_first_name,
                    'customer_last_name' => $booking->customer_last_name,
                    'customer_phone' => $booking->customer_phone,
                    'customer_email' => $booking->customer_email,
                    'beautician_id' => $booking->beautician_id,
                    'spa_branch_id' => $booking->spa_branch_id,
                    'product_id' => $booking->product_id,
                    'options' => $booking->product_options ?? [],
                    'variations' => $booking->product_variations ?? [],
                    'appointment_date' => $booking->appointment_date?->toDateString(),
                    'appointment_time' => $booking->appointment_time,
                    'schedule_later' => $booking->isTbaSchedule(),
                    'payment_status' => $booking->payment_status,
                    'notes' => $booking->notes,
                ], $fields);

                $this->assertBeauticianScope($actor, (int) $payload['beautician_id']);
                $payload = $this->customerPayload($payload, $customer);
                $this->manualBookings->update($booking, $payload, $actor, ! $actor->isBeauticianOnly());
                if ($previousReceiptId && (int) $booking->fresh()->payment_receipt_file_id !== (int) $previousReceiptId) {
                    DB::afterCommit(fn () => $this->paymentReceipts->deleteIfUnused((int) $previousReceiptId));
                }
            }

            if ($requestedStatus !== null) {
                $this->transition($booking->fresh(), $requestedStatus, $actor);
            }

            return $booking->fresh(['customer', 'beautician', 'product', 'category']);
        });
    }

    public function cancel(TreatmentBooking $booking, User $actor): TreatmentBooking
    {
        DB::transaction(function () use ($booking, $actor) {
            $this->assertManual($booking);
            $this->transition($booking, TreatmentBooking::STATUS_CANCELED, $actor);
        });

        return $booking->fresh(['customer', 'beautician', 'product', 'category']);
    }

    public function transition(TreatmentBooking $booking, string $status, User $actor): TreatmentBooking
    {
        $allowed = [
            TreatmentBooking::STATUS_PENDING => [TreatmentBooking::STATUS_IN_PROGRESS, TreatmentBooking::STATUS_CANCELED],
            TreatmentBooking::STATUS_IN_PROGRESS => [TreatmentBooking::STATUS_COMPLETED, TreatmentBooking::STATUS_CANCELED],
        ];
        $from = $booking->status;

        if (! isset($allowed[$from]) || ! in_array($status, $allowed[$from], true)) {
            throw new \InvalidArgumentException('Invalid booking status transition.');
        }

        if ($status === TreatmentBooking::STATUS_IN_PROGRESS && $booking->requiresScheduleBeforeStart()) {
            throw new \InvalidArgumentException('Assign an appointment date and time before starting treatment.');
        }

        $booking->update(['status' => $status]);
        $this->activityLogger->logStatusChange($booking, $from, $status, $actor->id);

        return $booking;
    }

    public function queryFor(User $actor, array $filters): Builder
    {
        $query = TreatmentBooking::query()
            ->with(['customer', 'beautician', 'product', 'category'])
            ->whereIn('source', TreatmentBooking::manualSources());

        if ($actor->isBeauticianOnly()) {
            $profile = $actor->beauticianProfile;
            $query->where('beautician_id', $profile?->id ?: 0);
        }

        foreach (['status', 'beautician_id', 'customer_id'] as $field) {
            if (isset($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('appointment_date', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('appointment_date', '<=', $filters['date_to']);
        }

        return $query->latest('appointment_date')->latest('appointment_time');
    }

    public function scopeCustomers(Builder $query, User $actor): Builder
    {
        if (! $actor->isBeauticianOnly()) {
            return $query;
        }

        $beauticianId = (int) ($actor->beauticianProfile?->id ?: 0);

        return $query->where(function (Builder $scope) use ($beauticianId) {
            $scope->whereExists(function ($bookings) use ($beauticianId) {
                $bookings->selectRaw('1')
                    ->from('treatment_bookings')
                    ->whereColumn('treatment_bookings.customer_id', 'users.id')
                    ->where('treatment_bookings.beautician_id', $beauticianId);
            })->orWhereExists(function ($orders) use ($beauticianId) {
                $orders->selectRaw('1')
                    ->from('orders')
                    ->whereColumn('orders.customer_id', 'users.id')
                    ->where('orders.beautician_id', $beauticianId);
            });
        });
    }

    public function assertCustomerAccessible(User $customer, User $actor): void
    {
        if (! $this->scopeCustomers(User::query()->whereKey($customer->id), $actor)->exists()) {
            throw new \InvalidArgumentException('Beauticians may only book for customers assigned to them.');
        }
    }

    private function customer(int $id, User $actor): User
    {
        $customer = $this->scopeCustomers(User::query()->whereKey($id), $actor)
            ->whereHas('roles', fn ($query) => $query->whereKey(setting('customer_role')))
            ->first();

        if (! $customer || ! $customer->isActivated()) {
            throw new \InvalidArgumentException('The selected customer is not active.');
        }

        return $customer;
    }

    private function customerPayload(array $data, User $customer): array
    {
        return array_merge($data, [
            'customer_id' => $customer->id,
            'customer_first_name' => $customer->first_name,
            'customer_last_name' => $customer->last_name,
            'customer_phone' => $customer->phone,
            'customer_email' => $customer->email,
        ]);
    }

    private function assertBeauticianScope(User $actor, int $beauticianId): void
    {
        if (! $actor->isBeauticianOnly()) {
            return;
        }

        $profile = $actor->beauticianProfile;
        if (! $profile || (int) $profile->id !== $beauticianId) {
            throw new \InvalidArgumentException('Beauticians may only book for their assigned profile.');
        }
    }

    /** @return list<TreatmentBooking>|null */
    private function existingBatch(string $requestKey, string $payloadHash, User $actor, int $expected): ?array
    {
        $bookings = TreatmentBooking::query()->where('pos_request_key', $requestKey)->orderBy('pos_line_index')->get();

        if ($bookings->isEmpty()) {
            return null;
        }

        if ($bookings->count() !== $expected || $bookings->contains(fn ($booking) => (int) $booking->created_by_user_id !== (int) $actor->id || ! hash_equals((string) $booking->pos_payload_hash, $payloadHash))) {
            throw new \InvalidArgumentException('This booking request key has already been used with different details.');
        }

        return $bookings->map(fn ($booking) => $booking->load(['customer', 'beautician', 'product', 'category']))->all();
    }

    private function payloadHash(array $data): string
    {
        unset($data['payment_receipt'], $data['request_key']);
        $sort = function (&$value) use (&$sort): void {
            if (! is_array($value)) {
                return;
            }
            foreach ($value as &$child) {
                $sort($child);
            }
            if (! array_is_list($value)) {
                ksort($value);
            }
        };
        $sort($data);

        return hash('sha256', json_encode($data, JSON_THROW_ON_ERROR));
    }

    private function assertManual(TreatmentBooking $booking): void
    {
        if (! $booking->isManualBooking()) {
            throw new \InvalidArgumentException('Only POS/manual bookings can be managed through this API.');
        }
    }
}
