@php
    $statusLabel = strtoupper($status);
    if ($statusLabel === 'CANCELLED') {
        $statusLabel = 'CANCELED';
    }
    $statusColor = match ($statusLabel) {
        'COMPLETED' => '#00bf63',
        'CANCELED' => '#ff3131',
        default => '#ef7900',
    };
    $rows = ['Department' => $department, 'Requestor' => $requestor, 'Category' => $category];
    $rows += isset($submittedAt)
        ? ['Details' => $details, 'Technician' => $technician, 'Submitted' => $submittedAt]
        : ['Technician' => $technician, 'Findings' => $findings,
            'Last Updated' => $dateAccomplished ? \Illuminate\Support\Carbon::parse($dateAccomplished)->format('m/d/Y h:iA') : null];
@endphp
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body style="margin:0;background:#fff;font-family:Arial,Helvetica,sans-serif;color:#000">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;margin:0 auto;background:#fdfdfd">
    <tr><td><img width="640" style="display:block;width:100%;height:auto" src="{{ asset('helpdesk/images/ticket-mail-header.png') }}" alt="Philippine Merchant Marine Academy · Information and Communications Technology Office (ICTO) · Helpdesk - Ticketing System"></td></tr>
    <tr><td><img width="640" style="display:block;width:100%;height:auto" src="{{ asset('helpdesk/images/ticket-mail-ticket-label.png') }}" alt="Your Ticket Number is"></td></tr>
    <tr><td align="center">
        <table role="presentation" width="71%" cellspacing="0" cellpadding="0"><tr>
            <td align="center" bgcolor="#143cd5" style="background:linear-gradient(#16179c,#087aff);border-radius:0 0 20px 20px;color:#fff;font-size:52px;font-size:clamp(48px,16vw,126px);line-height:1;font-weight:bold;padding:0 4px 12px;overflow-wrap:anywhere">{{ $ticketNumber }}</td>
        </tr></table>
    </td></tr>
    <tr><td style="padding:18px 10% 0">
        <table width="100%" cellspacing="0" cellpadding="0" style="table-layout:fixed;font-size:16px;font-size:clamp(14px,3.1vw,24px);line-height:1.15">
            @foreach ($rows as $label => $value)
                <tr><th scope="row" align="left" valign="top" width="45%" style="padding:4px 8px 4px 0">{{ $label }}</th>
                <td valign="top" style="padding:4px 0;overflow-wrap:anywhere">{!! nl2br(e($value ?: '—')) !!}</td></tr>
            @endforeach
        </table>
    </td></tr>
    <tr><td align="center" style="padding:26px 8px 10px;font-size:24px;font-weight:bold">
        Status:<br><span style="font-size:30px;color:{{ $statusColor }}">{{ $statusLabel }}</span>
    </td></tr>
    <tr><td align="center" style="padding:12px 16px">
        Please accomplish this Customer Satisfaction Survey:
        <a href="https://forms.cloud.microsoft/r/ewF3yD5GEm">https://forms.cloud.microsoft/r/ewF3yD5GEm</a>
    </td></tr>
    <tr><td><a href="mailto:itsupport@pmma.edu.ph"><img width="640" style="display:block;width:100%;height:auto" src="{{ asset('helpdesk/images/ticket-mail-footer.png') }}" alt="For inquiries: itsupport@pmma.edu.ph"></a></td></tr>
    <tr><td align="center" style="padding:16px">
        <a href="{{ URL::temporarySignedRoute('tickets.print', now()->addDays(30), ['ticket' => $ticketNumber]) }}" style="display:inline-block;background:#0032a0;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:bold">Print</a>
    </td></tr>
</table>
</body>
</html>
