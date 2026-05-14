@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="py-3 mb-0">
            <a class="text-muted fw-light" href="{{ route('learning-progress.index') }}">{{ __('menu.learning_progress') }} /</a>
            {{ __('label.edit') }}
        </h4>
        <a href="{{ route('learning-progress.index') }}" class="btn btn-secondary text-white fw-medium">
            {{ __('button.back') }}
        </a>
    </div>

    <div class="card mb-4">
        <form action="{{ route('learning-progress.update', $progress) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="mb-3">
                    <x-forms.input-select2 name="student_id" required :value="$progress->student_id"
                        :options="$students->map(fn($s) => [$s->id, $s->name])->toArray()"/>
                </div>
                <div class="mb-3">
                    <x-forms.input name="date" type="date" required :value="$progress->date->format('Y-m-d')"/>
                </div>
                <div class="mb-3">
                    <x-forms.input name="subject" required :value="$progress->subject"/>
                </div>
                <div class="mb-3">
                    <x-forms.input name="milestone" required :value="$progress->milestone"/>
                </div>
                <div class="mb-3">
                    <x-forms.input name="score" type="number" :value="$progress->score"/>
                </div>
                <div class="mb-3">
                    <x-forms.input-textarea name="notes" :value="$progress->notes"/>
                </div>
                <button type="submit" class="btn btn-primary me-2">{{ __('button.submit') }}</button>
                <button type="reset" class="btn btn-outline-secondary">{{ __('button.reset') }}</button>
            </div>
        </form>
    </div>
@endsection
