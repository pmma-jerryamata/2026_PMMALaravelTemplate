@extends('layouts.registrar')

@section('heading')
    <section class="support-intro registrar-intro" aria-labelledby="registrar-heading">
        <div class="container">
            <div>
                <p class="registrar-kicker">{{ config('app.name') }}</p>
                <h1 id="registrar-heading">{{ config('branding.office') }}</h1>
            </div>
        </div>
    </section>
@endsection
