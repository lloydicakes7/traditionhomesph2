@php
    $logoUrl = \App\Utility\StorageHelper::photoUrl(
        $logo_path ?? '',
        asset(config('adminlte.logo_img', 'image/logo.jpg'))
    );

    $currentUrl = url()->current();
    $contentMenu = $contentMenu ?? [];
    $aboutPages = $contentMenu['pages'] ?? [];
    $aboutGroups = $contentMenu['groups'] ?? [];
@endphp

<nav class="main-header sticky-top navbar navbar-expand-xl navbar-light bg-white py-3 thha-public-navbar">
    <div class="container">

        <a href="{{ route('home') }}" class="navbar-brand d-flex align-items-center py-0">
            <img src="{{ $logoUrl }}" alt="{{ $name }} logo"
                class="brand-image elevation-3 rounded mr-2">
            <span class="h5 mb-0 text-dark">{{ $short_name }}</span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-toggle="collapse"
            data-target="#publicNavbar" aria-controls="publicNavbar" aria-expanded="false"
            aria-label="Toggle navigation">
            <i class="fas fa-fw fa-bars"></i>
        </button>

        <div class="collapse navbar-collapse" id="publicNavbar">
            <ul class="navbar-nav align-items-xl-center ml-auto py-2 py-xl-0 small">
                @foreach ($publicLinks as $link)
                    @php $isActive = $currentUrl === $link['href']; @endphp

                    <li class="nav-item">
                        <a href="{{ $link['href'] }}"
                            class="nav-link font-weight-bold px-3 px-xl-2 {{ $isActive ? 'text-primary' : 'text-secondary' }}"
                            @if ($isActive) aria-current="page" @endif>
                            {{ $link['label'] }}
                        </a>
                    </li>
                @endforeach

                {{-- Published pages and their groups live under About Us. --}}
                @if (! empty($aboutPages) || ! empty($aboutGroups))
                    <li class="nav-item dropdown">
                        <div class="dropdown-menu border-0 shadow-sm">
                            @foreach ($aboutPages as $link)
                                <a class="dropdown-item" href="{{ $link['href'] }}">{{ $link['label'] }}</a>
                            @endforeach

                            @foreach ($aboutGroups as $group)
                                <div class="dropdown-submenu">
                                    <a href="#" class="dropdown-item dropdown-toggle"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        {{ $group['label'] }}
                                    </a>

                                    <div class="dropdown-menu border-0 shadow-sm">
                                        @foreach ($group['links'] as $link)
                                            <a class="dropdown-item" href="{{ $link['href'] }}">{{ $link['label'] }}</a>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <a href="#" class="nav-link font-weight-bold px-3 px-xl-2 text-secondary dropdown-toggle"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            About Us
                        </a>
                    </li>
                @endif
            </ul>

            <div class="mt-2 mt-xl-0 ml-xl-4">
                @include('adminlte::public.partials.navbar.login-menu', ['menuId' => 'publicSignIn'])
            </div>
        </div>

    </div>
</nav>
