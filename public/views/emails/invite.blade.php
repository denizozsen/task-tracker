<?php
    /** @var \App\User $sender */
?>

<div>

    <p>Hello,</p>

    <p>
        {{ $sender->name }} ({{ $sender->email  }}) is inviting you to register with the Task Tracker service!
        Please follow this link if you wish to do so: {{URL::to('/#/signup')}}
    </p>

</div>
