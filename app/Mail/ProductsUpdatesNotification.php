<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProductsUpdatesNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($msj)
    {
        $this->msj = $msj;
        $this->date = $date = date('Y-m-d H:i:s');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('info@dadasell.app')
                ->subject('Actualizacion completada: '.$this->date)
                ->markdown('emails.notifications.productsUpdated')
                ->with(['msj' => $this->msj]);
    }
}
