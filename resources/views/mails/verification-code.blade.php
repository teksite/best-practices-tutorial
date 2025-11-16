<x-mail::message>
    # Hi

    Your verification code for {{$type}} is

    **{{$code}}**

    and is validate until {{$expiredAt}}


    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>
