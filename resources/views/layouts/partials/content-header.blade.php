<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="h4 mb-0 font-weight-bold">@yield('title')</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 p-0 bg-transparent">
                <li class="breadcrumb-item"><a href="{{ $home }}">Home</a></li>
                @hasSection('breadcrumb')
                    @yield('breadcrumb')
                @endif
                <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
            </ol>
        </nav>
    </div>
    <div class="text-muted small d-none d-md-block">
        <i class="fas fa-fw fa-calendar-alt mr-1"></i>
        {{ now()->format('F j, Y') }}
    </div>
</div>
