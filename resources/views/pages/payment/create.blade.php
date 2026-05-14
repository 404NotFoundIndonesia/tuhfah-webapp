@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="py-3 mb-0">
            <a class="text-muted fw-light" href="{{ route('payment.index') }}">{{ __('menu.payment') }} /</a>
            {{ __('label.new') }}
        </h4>
        <a href="{{ route('payment.index') }}" class="btn btn-secondary text-white fw-medium">
            {{ __('button.back') }}
        </a>
    </div>

    <div class="card mb-4">
        <form action="{{ route('payment.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="mb-3">
                    <x-forms.input-select2 name="student_id" required
                        :options="$students->map(fn($s) => [$s->id, $s->name])->toArray()"/>
                </div>
                <div class="mb-3">
                    <x-forms.input name="period" required :value="date('Y-m')"
                        placeholder="YYYY-MM"/>
                </div>
                <div class="mb-3">
                    <x-forms.input name="amount" type="number" required :value="null" step="0.01"/>
                </div>
                <div class="mb-3">
                    <x-forms.input name="due_date" type="date" required :value="date('Y-m-d')"/>
                </div>
                <button type="submit" class="btn btn-primary me-2">{{ __('button.submit') }}</button>
                <button type="reset" class="btn btn-outline-secondary">{{ __('button.reset') }}</button>
            </div>
        </form>
    </div>
@endsection
