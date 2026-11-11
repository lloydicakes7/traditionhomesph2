@if (! empty($toc))
    <div class="col-lg-3 d-none d-lg-block">
        <div class="card card-outline card-primary manual-toc sticky-top" style="top: 5rem;">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-fw fa-list-ul mr-1"></i> {{ __('manual::common.on_this_page') }}
                </h3>
            </div>
            <div class="card-body p-0">
                <ul class="nav flex-column">
                    @foreach ($toc as $index => $section)
                        <li class="nav-item">
                            <a class="nav-link py-1 px-3" href="#section-{{ $index }}">
                                {{ $section['title'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif
