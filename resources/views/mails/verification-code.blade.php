<x-mail::message>
    # your verification code
    ## {{$code}}
    and is validated unit {{$expiredAt}}

    Your order has been shipped!



    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>
