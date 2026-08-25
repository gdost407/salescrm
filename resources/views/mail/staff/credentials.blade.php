<x-mail::message>
Your staff account has been created.

**Email:** {{ $user->email }}

**Temporary password:** {{ $temporaryPassword }}

Please sign in and change this password as soon as possible.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
