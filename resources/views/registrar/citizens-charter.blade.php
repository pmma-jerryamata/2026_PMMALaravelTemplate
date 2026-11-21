@extends('layouts.registrar')

@section('title', 'Citizen’s Charter 2026')

@section('heading')
    <section class="support-intro registrar-intro" aria-labelledby="charter-heading">
        <div class="container">
            <div>
                <h1 id="charter-heading">Citizen’s Charter 2026</h1>
            </div>
        </div>
    </section>
@endsection

@section('content')
    @php($services = require resource_path('data/registrar-charter.php'))

    <div class="registrar-charter">
        <div>
            <h2 id="registrar-charter-heading">Registrar Unit External Services</h2>
            <p><a href="{{ asset('assets/documents/pmma-citizens-charter-2026-registrar-unit.pdf') }}">View the original charter pages 19–26</a></p>
            <p class="registrar-charter-scroll-hint">Scroll the transaction tables sideways to see all columns.</p>
        </div>

        @foreach ($services as $service)
            <section class="portal-panel registrar-service-panel registrar-charter-service" aria-labelledby="{{ $service['id'] }}-heading">
                <h3 id="{{ $service['id'] }}-heading">{{ $service['title'] }}</h3>
                <p>{{ $service['description'] }}</p>

                <dl class="registrar-charter-details">
                    <div><dt>Classification</dt><dd>Simple</dd></div>
                    <div><dt>Type of Transaction</dt><dd>G2C (Government to Citizen)</dd></div>
                    <div><dt>Who May Avail</dt><dd>{{ $service['who'] }}</dd></div>
                </dl>

                <table class="registrar-charter-table">
                    <caption>Checklist of Requirements</caption>
                    <thead>
                        <tr><th scope="col">Requirement</th><th scope="col">Where to Secure</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($service['requirements'] as $requirement)
                            <tr><td>{{ $requirement }}</td><td>Registrar Unit</td></tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="registrar-charter-scroll" role="region" aria-labelledby="{{ $service['id'] }}-steps" tabindex="0">
                    <table class="registrar-charter-table registrar-charter-steps">
                        <caption id="{{ $service['id'] }}-steps">Transaction Steps</caption>
                        <thead>
                            <tr>
                                <th scope="col">Step</th>
                                <th scope="col">Client Steps</th>
                                <th scope="col">Agency Action</th>
                                <th scope="col">Fees to Be Paid</th>
                                <th scope="col">Processing Time</th>
                                <th scope="col">Person Responsible</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($service['steps'] as $step)
                                <tr>
                                    <th scope="row">{{ $loop->iteration }}</th>
                                    <td>
                                        @foreach ($step['client'] as $action)
                                            <p>{{ $action }}</p>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach ($step['agency'] as $action)
                                            <p>{{ $loop->parent->iteration }}.{{ $loop->iteration }}. {{ $action }}</p>
                                        @endforeach
                                    </td>
                                    <td>{{ $step['fee'] }}</td>
                                    <td>{{ $step['time'] }}</td>
                                    <td>{{ $step['staff'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th scope="row" colspan="3">Total</th>
                                <td>{{ $service['total_fee'] }}</td>
                                <td>{{ $service['total_time'] }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @isset($service['note'])
                    <p class="registrar-charter-note">{{ $service['note'] }}</p>
                @endisset
            </section>
        @endforeach
    </div>
@endsection
