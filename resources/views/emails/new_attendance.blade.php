<x-mail::message>
# There is a new attendance!

From email: {{$email}} <br>

Attendance type: {{$type_name}} <br>

Time: {{$start_date}} to {{$end_date}}, from {{$start_time}} to {{$end_time}} <br>

Reason: {{$reason}} <br>

Thanks,<br>
{{ $name }}
</x-mail::message>
