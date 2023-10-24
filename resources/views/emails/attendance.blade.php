<x-mail::message>
# Attendance reviewed

@if($status == 1)
Your attendance has been approved!
@else
Your attendance has been rejected!
@endif
<br>
Message: {{$result}} 

Regard,<br>
{{ $name }}
</x-mail::message>
