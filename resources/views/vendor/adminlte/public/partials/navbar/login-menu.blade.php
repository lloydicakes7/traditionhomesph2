@php
    $alignRight = $alignRight ?? true;
    $single = count($menuLinks) === 1;
@endphp

@if ($single)

    {{-- One entry point: every account signs in on the same screen. --}}
    <a href="{{ $menuLinks[0]['href'] }}" class="btn btn-primary">
        <i class="fas fa-fw fa-sign-in-alt mr-1"></i>{{ $menuLinks[0]['title'] }}
    </a>

@else

    <div class="dropdown">
        <button type="button" class="btn btn-primary dropdown-toggle" id="{{ $menuId }}" data-toggle="dropdown"
            aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-fw fa-sign-in-alt mr-1"></i>Login
        </button>

        <div class="dropdown-menu p-2 @if ($alignRight) dropdown-menu-right @endif"
            style="min-width: 290px;" aria-labelledby="{{ $menuId }}">
            @foreach ($menuLinks as $item)
                <a class="dropdown-item d-flex align-items-center rounded py-2" href="{{ $item['href'] }}">
                    <span class="d-flex align-items-center justify-content-center rounded mr-3 {{ $item['tile'] }}"
                        style="width: 40px; height: 40px; flex: 0 0 40px;">
                        <i class="{{ $item['icon'] }}"></i>
                    </span>

                    <span class="text-wrap">
                        <span class="d-block font-weight-bold">{{ $item['title'] }}</span>
                        <small class="d-block text-muted">{{ $item['subtitle'] }}</small>
                    </span>
                </a>
            @endforeach
        </div>
    </div>

@endif
