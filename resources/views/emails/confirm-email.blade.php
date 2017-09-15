@component('mail::message')
# One Last Step

We need you to confirm your email address. You get it, right? Cool.

@component('mail::button', ['url' => route('register.confirm-email', ['token'=>$user->confirmation_token])])
Confirm email
@endcomponent

Thanks,<br>
The Kasivibe team
@endcomponent
