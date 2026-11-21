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
    @php($registrarCharter = asset('assets/documents/pmma-citizens-charter-2026-registrar-unit.pdf'))
    @php($fullCharter = 'https://drive.google.com/file/d/1xDh1_8OvdRoJRLAniFjgcBfl6V2l1IwI')

    <div class="registrar-charter">
        <section class="portal-panel registrar-service-panel" aria-labelledby="registrar-charter-heading">
            <h2 id="registrar-charter-heading">Registrar Unit External Services</h2>
            <p>Pages 19–26 cover enrollment procedures and requests for school documents.</p>
            <p><a href="{{ $registrarCharter }}" target="_blank" rel="noopener">Open Registrar Unit PDF (pages 19–26)</a></p>
            <iframe src="{{ $registrarCharter }}" title="PMMA Citizen’s Charter 2026, Registrar Unit, pages 19–26" class="registrar-charter-viewer"></iframe>
        </section>

        <section class="portal-panel registrar-service-panel" aria-labelledby="full-charter-heading">
            <h2 id="full-charter-heading">Full PMMA Citizen’s Charter 2026</h2>
            <p><a href="{{ $fullCharter }}/view" target="_blank" rel="noopener">Open the full Charter in Google Drive</a></p>
            <iframe src="{{ $fullCharter }}/preview" title="Full PMMA Citizen’s Charter 2026" class="registrar-charter-viewer" loading="lazy"></iframe>
        </section>
    </div>
@endsection
