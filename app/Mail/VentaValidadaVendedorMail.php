<?php

namespace App\Mail;

use App\Models\Orden;
use App\Models\Producto;
use App\Models\User; // Comprador
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class VentaValidadaVendedorMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Orden $orden;
    public Producto $productoVendido;
    public int $cantidadVendida;
    public User $comprador;

    /**
     * Create a new message instance.
     */
    public function __construct(Orden $orden, Producto $productoVendido, int $cantidadVendida, User $comprador)
    {
        $this->orden = $orden;
        $this->productoVendido = $productoVendido;
        $this->cantidadVendida = $cantidadVendida;
        $this->comprador = $comprador;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: '¡Venta Validada! Producto: ' . $this->productoVendido->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.vendedor.venta_validada',
            with: [
                'ordenId' => $this->orden->id,
                'nombreProducto' => $this->productoVendido->name,
                'cantidad' => $this->cantidadVendida,
                'nombreComprador' => $this->comprador->name,
                'emailComprador' => $this->comprador->email,
                // Podrías añadir la dirección de envío si la tienes y es relevante
                // 'direccionEnvio' => $this->orden->shipping_address ?? 'No especificada en la orden',
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
