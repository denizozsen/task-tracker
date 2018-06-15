<?php
    /** @var \App\User $user */
    /** @var string $verificationCode */
?>

<div>

    <p>Dear {{ $user->name }},</p>

    <p>
        Thank you for signing up to the Task Tracker service!
        Please enter the below verification data on the following page: {{URL::to('/#/verification')}}
    </p>
    <p>
        Email: <strong>{{ $user->email  }}</strong>
    </p>
    <p>
        Verification Code: <strong>{{ $verificationCode }}</strong>
    </p>

</div>
