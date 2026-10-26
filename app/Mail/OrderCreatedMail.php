<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $customerName;
    public $shopSetting;

    public function __construct(Order $order, $customerName)
    {
        $this->order = $order;
        $this->customerName = $customerName;
        $this->shopSetting = \App\Models\Setting::where('branch_id', $order->branch_id)->first();
    }

    public function build()
    {
        return $this->subject('Order #' . $this->order->order_number . ' Created')
            ->view('emails.order_created')
            ->with([
                'order' => $this->order,
                'customerName' => $this->customerName,
                'shopSetting' => $this->shopSetting,
            ]);
    }
}
