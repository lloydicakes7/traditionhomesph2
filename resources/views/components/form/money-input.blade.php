@extends('adminlte::components.form.input-group-component')

{{-- Set errors bag internallly --}}

@php($setErrorsBag($errors ?? null))

{{-- Set input group item section --}}

@section('input_group_item')

    {{-- The peso sign is the only adornment --}}
    <div class="input-group-prepend">
        <span class="input-group-text">{{ \App\Utility\MoneyHelper::symbol() }}</span>
    </div>

    {{-- A money amount: grouped thousands, two decimals, zero or more --}}
    <input id="{{ $id }}" name="{{ $name }}" type="text" inputmode="decimal" data-thha-money
        placeholder="{{ $attributes->get('placeholder', '0.00') }}"
        value="{{ $getOldValue($errorKey, $attributes->get('value')) }}"
        {{ $attributes->except(['value', 'placeholder'])->merge(['class' => $makeItemClass()]) }}>

@overwrite
