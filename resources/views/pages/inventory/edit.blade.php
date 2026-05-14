@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="py-3 mb-0">
            <a class="text-muted fw-light" href="{{ route('inventory.index') }}">{{ __('menu.inventory') }} /</a>
            {{ __('label.edit') }}
        </h4>
        <a href="{{ route('inventory.index') }}" class="btn btn-secondary text-white fw-medium">
            {{ __('button.back') }}
        </a>
    </div>

    <div class="card mb-4">
        <form action="{{ route('inventory.update', $inventory) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="mb-3">
                    <x-forms.input name="name" required :value="$inventory->name"/>
                </div>
                <div class="mb-3">
                    <x-forms.input name="quantity" type="number" required :value="$inventory->quantity" min="0"/>
                </div>
                <div class="mb-3">
                    <x-forms.input-select2 name="condition" required
                        :options="collect($conditions)->map(fn($c) => [$c->value, __('label.'.$c->value)])->toArray()"
                        :value="$inventory->condition->value"/>
                </div>
                <div class="mb-3">
                    <x-forms.input name="acquisition_date" type="date" required :value="$inventory->acquisition_date->format('Y-m-d')"/>
                </div>
                <div class="mb-3">
                    <x-forms.input-textarea name="notes" :value="$inventory->notes" :rows="3"/>
                </div>
                <button type="submit" class="btn btn-primary me-2">{{ __('button.submit') }}</button>
                <a href="{{ route('inventory.show', $inventory) }}" class="btn btn-outline-secondary">{{ __('button.back') }}</a>
            </div>
        </form>
    </div>
@endsection
