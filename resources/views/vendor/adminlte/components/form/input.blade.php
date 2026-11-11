@extends('adminlte::components.form.input-group-component')

{{-- Set errors bag internallly --}}

@php($setErrorsBag($errors ?? null))

{{-- Set input group item section --}}

@section('input_group_item')

    {{-- Input --}}

    {{-- The value is printed once: the attribute bag is asked for it above and
         then skipped, so a field handed a value does not render it twice. --}}
    <input id="{{ $id }}" name="{{ $name }}"
        value="{{ $getOldValue($errorKey, $attributes->get('value')) }}"
        {{ $attributes->except('value')->merge(['class' => $makeItemClass()]) }}>

@overwrite
