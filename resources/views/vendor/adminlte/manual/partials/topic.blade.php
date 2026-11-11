@if ($active)
    <div class="row">
        <div class="col-lg-9">
            <div class="card shadow-sm">
                <div class="card-body">

                    <h2 class="h4 font-weight-bold mb-4">
                        <i class="{{ $active['icon'] ?? 'fas fa-fw fa-book' }} mr-2 text-primary"></i>
                        {{ $active['title'] }}
                    </h2>

                    @foreach ($active['sections'] as $index => $section)
                        <section class="manual-section {{ $loop->last ? '' : 'pb-4 mb-4 border-bottom' }}"
                            id="section-{{ $index }}">

                            <h3 class="h5 font-weight-bold">{{ $section['title'] }}</h3>

                            @if (! empty($section['body']))
                                <p class="mb-2">{{ $section['body'] }}</p>
                            @endif

                            @if (! empty($section['prerequisites']))
                                <p class="manual-label mb-1">{{ __('manual::common.before_you_start') }}</p>
                                <ul class="mb-2 pl-4">
                                    @foreach ($section['prerequisites'] as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            @if (! empty($section['steps']))
                                <ol class="mb-2 pl-4">
                                    @foreach ($section['steps'] as $step)
                                        <li class="mb-1">{{ $step }}</li>
                                    @endforeach
                                </ol>
                            @endif

                            @if (! empty($section['result']))
                                <div class="callout callout-success py-2 px-3 mb-3">
                                    <p class="manual-label mb-1">{{ __('manual::common.what_you_should_see') }}</p>
                                    <ul class="mb-0 pl-4">
                                        @foreach ($section['result'] as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if (! empty($section['fields']))
                                <p class="manual-label mb-1">{{ __('manual::common.screen_reference') }}</p>
                                <dl class="row mb-2">
                                    @foreach ($section['fields'] as $name => $description)
                                        <dt class="col-sm-4 font-weight-normal text-muted">{{ $name }}</dt>
                                        <dd class="col-sm-8 mb-1">{{ $description }}</dd>
                                    @endforeach
                                </dl>
                            @endif

                            @if (! empty($section['notes']))
                                <p class="manual-label mb-1">{{ __('manual::common.notes') }}</p>
                                <ul class="mb-0 pl-4 text-muted">
                                    @foreach ($section['notes'] as $note)
                                        <li>{{ $note }}</li>
                                    @endforeach
                                </ul>
                            @endif

                        </section>
                    @endforeach

                </div>
            </div>
        </div>

        @include('adminlte::manual.partials.toc')
    </div>
@else
    <div class="alert alert-info">
        <i class="fas fa-fw fa-info-circle mr-1"></i>
        {{ __('manual::common.no_topics') }}
    </div>
@endif
