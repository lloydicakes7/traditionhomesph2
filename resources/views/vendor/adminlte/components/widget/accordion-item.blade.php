<div {{ $attributes->merge(['class' => 'card' . (isset($theme) ? ' card-' . $theme : '')]) }}>
    <div class="card-header @if(isset($theme)) bg-{{ $theme }} @endif" id="heading-{{ $collapseId() }}">
        <h5 class="mb-0">
            <button class="btn btn-link text-reset text-decoration-none d-flex align-items-center w-100 text-left p-0"
                    type="button"
                    data-toggle="collapse"
                    data-target="#{{ $collapseId() }}"
                    aria-expanded="{{ isset($show) && $show ? 'true' : 'false' }}"
                    aria-controls="{{ $collapseId() }}">
                @isset($icon)<i class="{{ $icon }} mr-2 flex-shrink-0"></i>@endisset
                {{-- The title wraps instead of running past the card: a bare
                     `.btn` keeps `white-space: nowrap`, which hides long
                     headings off the edge on small screens. --}}
                <span class="flex-grow-1" style="white-space: normal; min-width: 0; word-break: break-word;">{{ $title }}</span>
                <i class="fas fa-chevron-down collapse-indicator ml-2 flex-shrink-0" style="font-size:12px;transition:transform .2s ease;"></i>
            </button>
        </h5>
    </div>

    <div id="{{ $collapseId() }}"
         class="collapse @if(isset($show) && $show) show @endif"
         aria-labelledby="heading-{{ $collapseId() }}"
         @isset($accordionId) data-parent="#{{ $accordionId }}" @endisset>
        <div class="card-body">
            {{ $slot }}
        </div>
    </div>
</div>
