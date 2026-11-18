@component('mail::message')
<div style="font-family: Arial, sans-serif; color: #1f2937;">
    <div style="text-align: center; margin-bottom: 22px;">
        <strong style="display: block; font-size: 16px;">Philippine Merchant Marine Academy</strong>
        <strong style="display: block; font-size: 14px;">Information and Communications Technology Office (ICTO)</strong>
        <strong style="display: block; margin-top: 13px; font-size: 18px;">HELPDESK - TICKETING SYSTEM</strong>
    </div>
    <p style="margin: 0 0 18px; font-size: 16px;">Your Ticket Number is <strong>{{ $ticketNumber }}</strong></p>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; font-size: 14px;">
        <tr><td style="padding: 7px 12px 7px 0; width: 35%; font-weight: 700;">Department</td><td style="padding: 7px 0;">{{ $department }}</td></tr>
        <tr><td style="padding: 7px 12px 7px 0; font-weight: 700;">Requestor</td><td style="padding: 7px 0;">{{ $name }}</td></tr>
        <tr><td style="padding: 7px 12px 7px 0; font-weight: 700;">Category</td><td style="padding: 7px 0;">{{ $task }}</td></tr>
        <tr><td style="padding: 7px 12px 7px 0; font-weight: 700; vertical-align: top;">Findings</td><td style="padding: 7px 0; white-space: pre-line;">{{ $findings ?: 'No findings provided.' }}</td></tr>
        <tr><td style="padding: 7px 12px 7px 0; font-weight: 700;">Technician</td><td style="padding: 7px 0;">{{ $technician }}</td></tr>
        <tr><td style="padding: 7px 12px 7px 0; font-weight: 700;">Last Updated</td><td style="padding: 7px 0;">{{ \Illuminate\Support\Carbon::parse($dateAccomplished)->format('m/d/Y h:iA') }}</td></tr>
        <tr><td style="padding: 7px 12px 7px 0; font-weight: 700;">Status:</td><td style="padding: 7px 0; font-weight: 700; color: {{ $isCompleted ? '#15803d' : '#b45309' }};">{{ strtoupper($status) }}</td></tr>
    </table>
    <p style="margin: 25px 0 0; text-align: center; font-size: 13px;">For inquiries: <a href="mailto:itsupport@pmma.edu.ph">itsupport@pmma.edu.ph</a></p>
</div>
@endcomponent
