<?php

namespace Modules\Checkout\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Modules\Checkout\Support\MailLogoEmbedder;
use Modules\Order\Entities\Order;
use Modules\Order\Services\OrderWhatsAppPdfService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class Invoice extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The instance of the order.
     *
     * @var Order
     */
    public $order;


    /**
     * Create a new message instance.
     *
     * @param Order $order
     *
     * @return void
     */
    public function __construct($order)
    {
        $this->order = $order;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        app()->setLocale($this->order->locale);

        $this->order->load([
            'products.variations',
            'products.options.option',
            'products.options.values',
            'coupon',
            'taxes',
            'transaction',
            'beautician',
            'spaBranch',
        ]);

        $logo = app(MailLogoEmbedder::class)->embed($this);
        $pdf = app(OrderWhatsAppPdfService::class);

        return $this->subject(trans('storefront::invoice.subject', ['id' => $this->order->id]))
            ->view("storefront::emails.{$this->getViewName()}", [
                'logo' => $logo,
                'themeColor' => mail_theme_color(),
            ])
            ->attachData(
                $pdf->invoicePdfBinary($this->order),
                sprintf('invoice-%d.pdf', $this->order->id),
                ['mime' => 'application/pdf'],
            )
            ->attachData(
                $pdf->receiptPdfBinary($this->order),
                sprintf('receipt-%d.pdf', $this->order->id),
                ['mime' => 'application/pdf'],
            );
    }


    private function getViewName()
    {
        return 'invoice' . (is_rtl($this->order->locale) ? '_rtl' : '');
    }
}
