<?php

namespace Modules\Account\Http\Controllers;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Modules\Checkout\Services\OrderGoogleCalendarUrl;
use Modules\Media\Entities\File;
use Modules\Order\Entities\Order;
use Modules\Order\Services\SendOrderBeauticianNotification;

class AccountOrdersController
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $orders = auth()->user()
            ->orders()
            ->with(['products', 'beautician'])
            ->latest()
            ->paginate(20);

        return view('storefront::public.account.orders.index', compact('orders'));
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show(int $id, OrderGoogleCalendarUrl $calendarUrl)
    {
        $order = $this->findUserOrder($id, [
            'products.product',
            'products.variations',
            'products.options.option',
            'products.options.values',
            'coupon',
            'taxes',
            'transaction',
            'beautician',
        ]);

        $hasTreatmentBooking = $this->hasTreatmentBooking($order);
        $canNotifyBeautician = $hasTreatmentBooking
            && $order->beautician_id
            && setting('whatsapp_completed_beautician_enabled', true);
        $googleCalendarUrl = $calendarUrl->forOrder($order);

        return view('storefront::public.account.orders.show', compact(
            'order',
            'hasTreatmentBooking',
            'canNotifyBeautician',
            'googleCalendarUrl',
        ));
    }


    public function invoice(int $id)
    {
        $order = $this->findUserOrder($id, [
            'products.variations',
            'products.options.option',
            'products.options.values',
            'coupon',
            'taxes',
            'transaction',
            'beautician',
        ]);

        $logo = null;
        $logoId = setting('storefront_header_logo');

        if ($logoId) {
            $logo = File::find($logoId)?->path;
        }

        return view('order::admin.orders.print.show', compact('order', 'logo'));
    }


    public function receipt(int $id)
    {
        $order = $this->findUserOrder($id, [
            'products.variations',
            'products.options.option',
            'products.options.values',
            'coupon',
            'taxes',
            'transaction',
            'beautician',
        ]);

        $logo = null;
        $logoId = setting('storefront_header_logo');

        if ($logoId) {
            $logo = File::find($logoId)?->path;
        }

        return view('storefront::public.account.orders.receipt', compact('order', 'logo'));
    }


    public function notifyBeautician(int $id, SendOrderBeauticianNotification $notification): RedirectResponse
    {
        $order = $this->findUserOrder($id, ['beautician']);

        if (! $order->beautician_id) {
            return redirect()
                ->route('account.orders.show', $id)
                ->with('error', trans('storefront::order_complete.beautician_notify_no_beautician'));
        }

        try {
            $notification->send($order);

            return redirect()
                ->route('account.orders.show', $id)
                ->with('success', trans('storefront::order_complete.beautician_notify_sent'));
        } catch (Exception $e) {
            return redirect()
                ->route('account.orders.show', $id)
                ->with('error', $e->getMessage());
        }
    }


    private function findUserOrder(int $id, array $with = []): Order
    {
        return auth()->user()
            ->orders()
            ->with($with)
            ->where('id', $id)
            ->firstOrFail();
    }


    private function hasTreatmentBooking(Order $order): bool
    {
        if ($order->beautician_id || $order->appointment_date) {
            return true;
        }

        return $order->products->contains(fn ($line) => (bool) $line->product?->is_virtual);
    }
}
