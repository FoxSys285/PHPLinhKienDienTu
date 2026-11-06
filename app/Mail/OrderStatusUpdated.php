<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order, public string $headline, public string $detail) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->headline.' · '.$this->order->order_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $orderUrl = match (true) {
            (bool) $this->order->user_id => route('account.orders.show', $this->order),
            (bool) $this->order->guest_access_token => route('checkout.guest-order.show', ['order' => $this->order, 'token' => $this->order->guest_access_token]),
            default => route('home'),
        };

        return new Content(
            markdown: 'mail.order-status-updated',
            with: [
                'order' => $this->order,
                'headline' => $this->headline,
                'detail' => $this->detail,
                'orderUrl' => $orderUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
