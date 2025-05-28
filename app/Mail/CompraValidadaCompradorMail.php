<?php

namespace App\Mail;

use App\Models\Orden;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection; // Para la colección de vendedores
use Illuminate\Mail\Mailables\Address;

class CompraValidadaCompradorMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Orden $orden;
    public Collection $vendedores; // Colección de objetos User (vendedores)

    /**
     * Create a new message instance.
     */
    public function __construct(Orden $orden, Collection $vendedores)
    {
        $this->orden = $orden;
        $this->vendedores = $vendedores;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Confirmación de Compra Validada - Orden #' . $this->orden->id,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.comprador.compra_validada',
            with: [
                'nombreComprador' => $this->orden->buyer->name,
                'ordenId' => $this->orden->id,
                'montoTotal' => $this->orden->total_amount,
                'productosOrden' => $this->orden->products, // Pasa la colección de productos de la orden
                'listaVendedores' => $this->vendedores,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
