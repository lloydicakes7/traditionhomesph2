@extends('adminlte::components.form.input-group-component')

@php($setErrorsBag($errors ?? null))

@section('input_group_item')

    <input id="{{ $id }}" name="{{ $name }}" type="password"
        value="{{ $getOldValue($errorKey, $attributes->get('value')) }}"
        {{ $attributes->except('value')->merge(['class' => $makeItemClass()]) }}>

    <div class="input-group-append">
        <button type="button" class="input-group-text" data-thha-password-toggle
            title="Show password" aria-label="Show password" aria-pressed="false">
            <i class="fas fa-fw fa-eye"></i>
        </button>
    </div>

@overwrite
