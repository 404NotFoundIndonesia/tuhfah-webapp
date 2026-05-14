@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="py-3 mb-0">
            <a class="text-muted fw-light" href="{{ route('inventory.index') }}">{{ __('menu.inventory') }} /</a>
            {{ __('label.new') }}
        </h4>
        <a href="{{ route('inventory.index') }}" class="btn btn-secondary text-white fw-medium">
            {{ __('button.back') }}
        </a>
    </div>

    <div class="card mb-4">
        <form action="{{ route('inventory.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="mb-3">
                    <x-forms.input name="name" required :value="null"/>
                </div>
                <div class="mb-3">
                    <x-forms.input name="quantity" type="number" required :value="0" min="0"/>
                </div>
                <div class="mb-3">
                    <x-forms.input-select2 name="condition" required
                        :options="collect($conditions)->map(fn($c) => [$c->value, __('label.'.$c->value)])->toArray()"
                        :value="null"/>
                </div>
                <div class="mb-3">
                    <x-forms.input name="acquisition_date" type="date" required :value="date('Y-m-d')"/>
                </div>
                <div class="mb-3">
                    <x-forms.input-textarea name="notes" :value="null" :rows="3"/>
                </div>
                <button type="submit" class="btn btn-primary me-2">{{ __('button.submit') }}</button>
                <button type="reset" class="btn btn-outline-secondary">{{ __('button.reset') }}</button>
            </div>
        </form>
    </div>
@endsection
