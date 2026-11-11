<div>
    @if ($household === null)
        <div class="card">
            <div class="card-body text-center text-muted py-5">
                {{ __('rbi.hub.no_household') }}
            </div>
        </div>
    @else
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">{{ __('rbi.hub.own_household') }}</h5>
            </div>
            <div class="card-body">
                <dl class="row mb-3">
                    <dt class="col-sm-4">{{ __('rbi.hub.columns.household') }}</dt>
                    <dd class="col-sm-8">{{ $household->rbi_name }}</dd>

                    <dt class="col-sm-4">{{ __('rbi.hub.columns.unit') }}</dt>
                    <dd class="col-sm-8">{{ $household->property ? $household->property->block_lot_label : '—' }}</dd>

                    <dt class="col-sm-4">{{ __('rbi.hub.columns.head') }}</dt>
                    <dd class="col-sm-8">{{ $head ? $head->full_name : '—' }}</dd>
                </dl>

                <a href="{{ route('portal.rbi.form-a', $household) }}" target="_blank" rel="noopener"
                    class="btn btn-sm btn-primary">
                    {{ __('rbi.hub.form_a') }}
                </a>
                <a href="{{ route('portal.rbi.form-a.download', $household) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-fw fa-download"></i> {{ __('rbi.hub.download') }}
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('rbi.hub.members') }}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <tbody>
                            @if ($head)
                                <tr>
                                    <td>{{ $head->full_name }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('portal.rbi.form-b', $head) }}" target="_blank" rel="noopener"
                                            class="btn btn-sm btn-outline-primary">{{ __('rbi.hub.form_b') }}</a>
                                        <a href="{{ route('portal.rbi.form-b.download', $head) }}"
                                            class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-fw fa-download"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endif

                            @foreach ($members as $member)
                                @if ($member->resident)
                                    <tr >
                                        <td>{{ $member->resident->full_name }}</td>
                                        <td class="text-right">
                                            <a href="{{ route('portal.rbi.form-b', $member->resident) }}"
                                                target="_blank" rel="noopener"
                                                class="btn btn-sm btn-outline-primary">{{ __('rbi.hub.form_b') }}</a>
                                            <a href="{{ route('portal.rbi.form-b.download', $member->resident) }}"
                                                class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-fw fa-download"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
