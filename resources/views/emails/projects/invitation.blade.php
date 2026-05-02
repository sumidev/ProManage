<x-mail::message>
# You have been invited!

Hello,

**{{ $invitation->inviter->first_name }} {{ $invitation->inviter->last_name }}** has invited you to collaborate on the project **"{{ $invitation->project->name }}"** in ProManage.

Click the button below to accept the invitation and join the project.

<x-mail::button :url="$acceptUrl" color="primary">
Accept Invitation
</x-mail::button>

If you do not wish to join this project, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>