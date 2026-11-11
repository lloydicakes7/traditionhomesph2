@php $icon = $icon ?? null; @endphp

<section class="bg-secondary p-5 mb-5">
    <div class="container">
        <div class="text-center">
            <h1 class="h2 mb-2">
                @if ($icon)
                    <i class="{{ $icon }} mr-1"></i>
                @endif{{ $title }}
            </h1>

            @if (! empty($description))
                <p class="mb-0 mx-auto" style="max-width: 60ch;">{{ $description }}</p>
            @endif
        </div>
    </div>
</section>
