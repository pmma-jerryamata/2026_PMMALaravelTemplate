@component('mail::message')
New Job Request
 
**Ticket Number : {{ $ticket }}**
 
@component('mail::button', ['url' => 'https://support.pmma.edu.ph/tech', 'color' => 'success'])
View Ticket Queue
@endcomponent
@endcomponent