{{-- Default Content Wrapper — the portal's counterpart of the admin panel's --}}
<div class="content-wrapper">

    {{-- Content Header --}}
    @hasSection('content_header')
        <div class="content-header">
            <div class="container-fluid">
                @yield('content_header')
            </div>
        </div>
    @endif

    {{-- Main Content --}}
    <div class="content">
        <div class="container-fluid">
            @stack('content')
            @yield('content')
        </div>
    </div>

</div>
