@component('mail::message')
@if (! empty($greeting))
<h1>{{ $greeting }}</h1>
@elseif ($level === 'error')
<h1>Important notice</h1>
@else
<h1>Greetings</h1>
@endif

@foreach ($introLines as $line)
<p>{{ $line }}</p>
@endforeach

@isset($actionText)
@php
    $color = in_array($level, ['success', 'error'], true) ? $level : 'primary';
@endphp
@component('mail::button', ['url' => $actionUrl, 'color' => $color])
{{ $actionText }}
@endcomponent
@endisset

@foreach ($outroLines as $line)
<p>{{ $line }}</p>
@endforeach

@if (! empty($salutation))
<p>{{ $salutation }}</p>
@else
<p>Best regards, <br>{{ config('app.name') }}</p>
@endif

@isset($actionText)
@slot('subcopy')
<p>{{ $actionText }}: {{ $displayableActionUrl }}</p>
@endslot
@endisset
@endcomponent
