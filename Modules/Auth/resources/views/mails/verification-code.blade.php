<x-mail::message>
    # Hello!!!

    your verification code is:

   #{{$code}}

    this is code is valid until {{$expireAt}}

    Thanks,
    {{ config('app.name') }}
</x-mail::message>
