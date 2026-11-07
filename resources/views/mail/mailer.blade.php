@component('mail::message')
Hello,
 
Thank you for submitting your request through our electronic job request form. We have successfully received your request.
 
**Ticket Number : {{ $ticket }}**
 
Our team will send support the soonest possible time.
 
 
Best regards,<br /><br />
<b>Information Technology Services Unit</b>
<div style="text-align: center; margin-top: 0px;">
    <style>
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
<span class="no-print">
@component('mail::button', ['url' => 'https://support.pmma.edu.ph/ticket', 'color' => 'success'])
View Ticket Queue
@endcomponent
</span>
@endcomponent