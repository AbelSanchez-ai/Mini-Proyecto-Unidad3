@extends('layouts.app')

@section('content')
    <div class="container text-center mt-5">
        <div class="d-flex align-items-center">
            <img src="{{ asset('images/logo4.png') }}" alt="Elektro Logo" class="rounded-circle" width="80" height="80">
            <h1 class="display-4 ms-3">Sobre Elektro</h1>
        </div>
        <p class="lead">En <strong>Elektro</strong>, nos especializamos en ofrecer productos electrónicos de alta calidad para mejorar tu día a día.</p>
    
        <h2 class="mt-4">Nuestra Misión</h2>
        <p>Brindar tecnología innovadora y accesible para todos, con un enfoque en calidad, servicio y satisfacción del cliente.</p>
    
        <h2 class="mt-4">Contáctanos</h2>
        <p><strong>Email:</strong> <a href="mailto:elektro@gmail.com">elektro@gmail.com</a></p>
        <p><strong>Teléfono:</strong> +52 961 240 0213</p>
        <p><strong>Ubicación:</strong> Av, Central, 42, Col. Centro, Tuxtla Gutierrez</p>
    </div>
@endsection