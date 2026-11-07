@props(['url'])
<tr>
<td class="header">
<a href="https://support.pmma.edu.ph/" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<div style="padding: 0px; text-align: center;">
<img src="https://pmma.edu.ph/wp-content/uploads/2025/01/Job-Request-Form-Header.png" class="logo" alt="INFORMATION TECHNOLOGY SERVICES UNIT" style="width: 550px; height: 80px;">
</div>
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
