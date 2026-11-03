<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StuffingCompleteReminder extends Mailable
{
    use Queueable, SerializesModels;

    /** @var object user PIC (login, email) */
    public $pic;

    /** @var array daftar shipment stuffing 100% & status Confirm */
    public $shipments;

    /** @var string|null alamat asli kalau email dialihkan (mode uji) */
    public $originalTo;

    public function __construct($pic, array $shipments, ?string $originalTo = null)
    {
        $this->pic = $pic;
        $this->shipments = $shipments;
        $this->originalTo = $originalTo;
    }

    public function build()
    {
        $count = count($this->shipments);

        return $this->subject("[EXIM] {$count} shipment stuffing 100% - status masih Confirm")
            ->view('emails.stuffing-complete');
    }
}
