@extends('adminlte::components.form.input-group-component')

{{-- Set errors bag internallly --}}

@php($setErrorsBag($errors ?? null))

{{-- Set input group item section --}}

@section('input_group_item')

    {{-- A Philippine mobile number: eleven digits, the leading 0 kept --}}
    <input id="{{ $id }}" name="{{ $name }}" type="tel" inputmode="numeric"
        minlength="11" maxlength="11" pattern="09[0-9]{9}" autocomplete="tel"
        placeholder="{{ $attributes->get('placeholder', '09XXXXXXXXX') }}"
        data-thha-contact
        value="{{ $getOldValue($errorKey, $attributes->get('value')) }}"
        {{ $attributes->except(['value', 'placeholder'])->merge(['class' => $makeItemClass()]) }}>

@overwrite
