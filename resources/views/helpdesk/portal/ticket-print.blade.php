<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ticket #{{ $ticket->id }} | PMMA ICTO Helpdesk</title>
    <style>
        @page { size: A4 portrait; margin: 12mm; }
        * { box-sizing: border-box; }
        body { max-width: 186mm; margin: 16px auto; padding: 0 8px; color: #17223b; font: 14px/1.5 Arial, sans-serif; }
        img { display: block; height: auto; }
        header, footer { width: 100%; overflow: hidden; }
        /* The print header artwork has a transparent edge. Expand it in its
           container so the colored banner aligns with the report table. */
        header img { width: 110%; max-width: none; margin-left: -5%; }
        footer img { width: 100%; }
        h1 { font-size: 22px; margin: 8px 0 14px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #9aa9c4; padding: 8px; vertical-align: top; overflow-wrap: anywhere; }
        th { width: 28%; text-align: left; }
        td { white-space: pre-wrap; }
        .email-meta { margin-top: 16px; border-top: 1px solid #dbe2ed; border-bottom: 1px solid #dbe2ed; padding: 8px 0; font-size: 12px; line-height: 1.45; break-inside: avoid; }
        .email-meta p { margin: 2px 0; overflow-wrap: anywhere; }
        .email-meta strong { display: inline-block; min-width: 54px; }
        footer { margin-top: 16px; break-inside: avoid; }
        .print-controls { margin-bottom: 12px; }
        @media print { body { margin: 0; padding: 0; } .print-controls { display: none; } }
    </style>
</head>
<body>
    <div class="print-controls"><button type="button" onclick="window.print()">Print report</button></div>
    <header><img src="{{ asset('helpdesk/images/ticket-print-header.png') }}" alt="Philippine Merchant Marine Academy · ICTO Helpdesk"></header>
    <h1>Ticket #{{ $ticket->id }} — Support Request Report</h1>
    @php
        $formatDate = fn ($date) => $date?->copy()->timezone('Asia/Manila')->format('m/d/Y h:i A').' PHT';
        $emailAction = match (strtolower((string) $ticket->status)) {
            'completed' => 'Completed',
            'cancelled', 'canceled' => 'Canceled',
            default => 'Updated',
        };
        $senderName = strtoupper((string) config('mail.from.name', 'PMMA ICTO Helpdesk'));
        $senderAddress = (string) config('mail.from.address', '');
        $requestorName = $ticket->employee?->name ?? 'Requestor';
        $requestorEmail = $ticket->employee?->email ?? '';
        $emailDate = $ticket->updated_at ?? $ticket->created_at;
        $fields = [
            'Status' => $ticket->status,
            'Department' => $ticket->employee?->department?->name,
            'Requestor' => $ticket->employee?->name,
            'Category' => $ticket->task?->name,
            'Concern Details' => $ticket->support_specifics,
            'Technician' => $ticket->technician?->name ?? 'Unassigned',
            'Findings / Action Taken' => $ticket->findings,
            'Submission Date' => $ticket->created_at ? $formatDate($ticket->created_at) : null,
            'Last Updated Date' => $ticket->updated_at ? $formatDate($ticket->updated_at) : null,
        ];
    @endphp
    <table>
        @foreach ($fields as $label => $value)
            <tr><th scope="row">{{ $label }}</th><td>{{ $value ?? '—' }}</td></tr>
        @endforeach
    </table>
    <section class="email-meta" aria-label="Ticket email details">
        <p><strong>Subject</strong> PMMA ICTO Helpdesk | Ticket #{{ $ticket->id }} {{ $emailAction }}</p>
        <p><strong>From</strong> {{ $senderName }}@if($senderAddress) &lt;{{ $senderAddress }}&gt;@endif</p>
        <p><strong>Date</strong> {{ $emailDate ? $formatDate($emailDate) : '—' }}</p>
        <p><strong>To</strong> {{ $requestorName }}@if($requestorEmail) &lt;{{ $requestorEmail }}&gt;@endif</p>
    </section>
    <footer><img src="{{ asset('helpdesk/images/ticket-mail-footer.png') }}" alt="For inquiries: itsupport@pmma.edu.ph"></footer>
</body>
</html>
