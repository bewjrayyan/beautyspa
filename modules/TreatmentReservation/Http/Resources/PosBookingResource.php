<?php

namespace Modules\TreatmentReservation\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PosBookingResource extends JsonResource
{
    public function toArray($request): array
    {
        $customer = $this->customer;
        $beautician = $this->beautician;
        $product = $this->product;

        return [
            'id' => $this->id,
            'status' => $this->status,
            'source' => $this->source,
            'appointment_date' => $this->appointment_date?->toDateString(),
            'appointment_time' => $this->appointment_time,
            'schedule_status' => $this->schedule_status,
            'duration_minutes' => (int) $this->duration_minutes_snapshot,
            'total' => (float) $this->total,
            'currency' => $this->currency,
            'payment_status' => $this->payment_status,
            'notes' => $this->notes,
            'customer' => $customer ? [
                'id' => $customer->id,
                'name' => trim($customer->first_name . ' ' . $customer->last_name),
                'phone' => $customer->phone,
                'email' => $customer->email,
            ] : [
                'id' => $this->customer_id,
                'name' => trim($this->customer_first_name . ' ' . $this->customer_last_name),
                'phone' => $this->customer_phone,
                'email' => $this->customer_email,
            ],
            'treatment' => $product ? [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) ($product->selling_price?->amount() ?? $this->total),
                'category_id' => $this->treatment_category_id,
            ] : null,
            'beautician' => $beautician ? [
                'id' => $beautician->id,
                'name' => $beautician->name,
                'job_title' => $beautician->job_title,
            ] : null,
            'spa_branch_id' => $this->spa_branch_id,
        ];
    }
}
