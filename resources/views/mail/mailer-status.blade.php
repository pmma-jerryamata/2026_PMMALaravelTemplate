@component('mail::message')
@if($isCompleted)
Your support request has been completed. Below are the details:
@else
Your support request has been updated. Below are the latest details:
@endif

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
@if($isCompleted)
<span class="no-print">We kindly invite you to share your feedback on your recent experience with our tech support by completing a brief survey below.
    @component('mail::button', ['url' => 'https://support.pmma.edu.ph/feedback', 'color' => 'success'])
    Rate Our Service
    @endcomponent
</span>
@endif
</div>
Thank you!<br/><br/>
<b>Information Technology Services Unit</b>
@endcomponent
