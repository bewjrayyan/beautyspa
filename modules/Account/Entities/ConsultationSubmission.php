<?php

namespace Modules\Account\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Order\Entities\Order;
use Modules\Order\Entities\OrderProduct;
use Modules\Product\Entities\Product;
use Modules\Support\Eloquent\Model;
use Modules\Beautician\Entities\Beautician;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\User\Entities\User;

class ConsultationSubmission extends Model
{
    protected $fillable = [
        'template_id',
        'user_id',
        'product_id',
        'order_id',
        'order_product_id',
        'treatment_booking_id',
        'beautician_id',
        'sent_by_user_id',
        'public_token',
        'customer_name',
        'customer_email',
        'customer_phone',
        'sent_at',
        'opened_at',
        'revoked_at',
        'template_version',
        'form_title',
        'form_intro',
        'consent_text',
        'questions_snapshot',
        'answers',
        'signature_data',
        'consent_accepted',
        'legal_documents_snapshot',
        'submitted_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'questions_snapshot' => 'array',
        'answers' => 'array',
        'consent_accepted' => 'boolean',
        'legal_documents_snapshot' => 'array',
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'revoked_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(ConsultationFormTemplate::class, 'template_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withoutGlobalScope('active')->withTrashed();
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class)->withTrashed();
    }

    public function orderProduct(): BelongsTo
    {
        return $this->belongsTo(OrderProduct::class);
    }

    public function treatmentBooking(): BelongsTo
    {
        return $this->belongsTo(TreatmentBooking::class);
    }

    public function beautician(): BelongsTo
    {
        return $this->belongsTo(Beautician::class);
    }

    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by_user_id');
    }

    public function isCompleted(): bool
    {
        return $this->submitted_at !== null;
    }

    public function scopeWithConsultationContext(Builder $query): Builder
    {
        return $query->with([
            'treatmentBooking.product',
            'treatmentBooking.beautician.spaBranches',
            'beautician',
            'product',
            'order.spaBranch',
            'order.beautician',
            'orderProduct',
        ]);
    }
}
