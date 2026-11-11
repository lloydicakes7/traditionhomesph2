@extends('adminlte::components.form.input-group-component')

{{-- Set errors bag internallly --}}

@php($setErrorsBag($errors ?? null))

{{-- Set input group item section --}}

@section('input_group_item')

    {{-- A Philippine mobile number: eleven digits at most, the leading 0 kept --}}
    <input id="{{ $id }}" name="{{ $name }}" type="tel" inputmode="numeric"
        maxlength="11" pattern="0[0-9]{10}" autocomplete="tel" data-thha-contact
        value="{{ $getOldValue($errorKey, $attributes->get('value')) }}"
        {{ $attributes->except('value')->merge(['class' => $makeItemClass()]) }}>

@overwrite
