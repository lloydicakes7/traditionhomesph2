@extends('layouts.admin')

@section('title', $title)

@section('breadcrumb')
    <li class="breadcrumb-item">{{ __('rbi.hub.title') }}</li>
    <li class="breadcrumb-item active">{{ __('rbi.hub.form_a') }}</li>
@endsection

@section('page-content')
    <div class="row">
        {{-- ===== Filters ===== --}}
        <div class="col-12 col-lg-4">
            <x-adminlte-card :title="__('rbi.hub.columns.household')" icon="fas fa-fw fa-home">
                <form method="GET" action="{{ route('rbi.form-a') }}">
                    <x-adminlte-select2 name="household" igroup-size="sm"
                        label="{{ __('rbi.hub.columns.household') }}"
                        :config="['placeholder' => __('rbi.hub.select_household')]">
                        <option value="">{{ __('rbi.hub.select_household') }}</option>
                        @foreach ($households as $id => $label)
                            <option value="{{ $id }}" @if ($selected && $selected->id === $id) selected @endif>
                                {{ $label }}
                            </option>
                        @endforeach
                    </x-adminlte-select2>

                    <x-adminlte-button type="submit" theme="secondary" icon="fas fa-fw fa-sync-alt"
                        label="{{ __('rbi.hub.update') }}" class="btn-sm" />
                </form>
            </x-adminlte-card>
        </div>

        @include('admin.rbi.partials.preview-card')
    </div>
@endsection
