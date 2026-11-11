@extends('layouts.admin')

@section('title', $title)

@section('breadcrumb')
    <li class="breadcrumb-item">{{ __('rbi.hub.title') }}</li>
    <li class="breadcrumb-item active">{{ __('rbi.hub.form_c') }}</li>
@endsection

@section('page-content')
    <div class="row">
        {{-- ===== Filters ===== --}}
        <div class="col-12 col-lg-4">
            <x-adminlte-card :title="$title" icon="fas fa-fw fa-sliders-h">
                <form method="GET" action="{{ route('rbi.form-c') }}">
                    <x-adminlte-input name="year" type="number" min="2000" max="2100" igroup-size="sm"
                        label="{{ __('rbi.hub.year') }}" :value="$filters['year']" />

                    <x-adminlte-select name="semester" igroup-size="sm" label="{{ __('rbi.hub.semester') }}">
                        @foreach ($semesters as $value => $label)
                            <option value="{{ $value }}" @if ($filters['semester']->value === $value) selected @endif>
                                {{ $label }}
                            </option>
                        @endforeach
                    </x-adminlte-select>

                    <x-adminlte-button type="submit" theme="secondary" icon="fas fa-fw fa-sync-alt"
                        label="{{ __('rbi.hub.update') }}" class="btn-sm" />
                </form>

                <p class="text-muted small mt-3 mb-0">{{ __('rbi.hub.deadline_hint') }}</p>
            </x-adminlte-card>
        </div>

        @include('admin.rbi.partials.preview-card')
    </div>
@endsection
