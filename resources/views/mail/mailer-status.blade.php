@component('mail::message')
Your support request has been processed. Below are the details:

|                          |                           |
|--------------------------|---------------------------|
| **Ticket Number**        | : {{ $ticketNumber }}       |
| **Request Status**       | : {{ $status }}             |
| **Date of Request**      | : {{ $dateRequest }}        |
| **Name**                 | : **{{ $name }}**             |
| **Department**           | : {{ $department }}         |
| **Tasks/Jobs to be Done**| : **{{ $task }}** - {{ $specifics }} |
| **Technician Findings**  | : {{ $findings }}           |
| **Checked By**           | : {{ $technician }}         |
| **Date Accomplished**    | : {{ $dateAccomplished }}   |
<br />
<div style="text-align: center; margin-top: 0px;">
    <style>
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
<span class="no-print">We kindly invite you to share your feedback on your recent experience with our tech support by completing a brief survey below.
    @component('mail::button', ['url' => 'https://support.pmma.edu.ph/feedback', 'color' => 'success'])
    Rate Our Service
    @endcomponent
</span>
</div>
Thank you!<br/><br/>
<b>Information Technology Services Unit</b>
@endcomponent