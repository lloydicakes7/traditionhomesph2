<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>@yield('title', isset($document) ? $document->label() : 'Document')</title>
    @include('pdf.partials.styles')
    @yield('styles')
</head>
<body class="doc">

@php
    $hasRunning = trim($__env->yieldContent('running_header')) !== '';
@endphp

@if ($hasRunning || ! empty($isSample))
    <div class="running">
        <table>
            <tr>
                <td class="r-left">@yield('running_header')</td>
                <td class="r-mid">{{ ! empty($isSample) ? 'SAMPLE - SAMPLE DATA' : '' }}</td>
                <td class="r-right">@if ($hasRunning)Page <span class="pagenum"></span>@endif</td>
            </tr>
        </table>
    </div>
@endif

@yield('page')
</body>
</html>
