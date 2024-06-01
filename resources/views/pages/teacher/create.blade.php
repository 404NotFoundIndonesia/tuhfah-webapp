@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="py-3 mb-0">
            <a class="text-muted fw-light" href="{{ route('teacher.index') }}">{{ __('menu.teacher') }} /</a>
            {{ __('label.new') }}
        </h4>
        <div>
            <a href="{{ route('teacher.index') }}" class="btn btn-secondary text-white fw-medium">
                {{ __('button.back') }}
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('teacher.store') }}" method="post">
                @csrf
                <div class="mb-3">
                    <x-forms.input name="name" required />
                </div>
                <div class="mb-3">
                    <x-forms.input name="email" type="email" required />
                </div>
                <div class="mb-3">
                    <x-forms.input-select2 name="gender" required :options="[ ['male', __('label.male')], ['female', __('label.female')] ]" />
                </div>
                <div class="mb-3">
                    <x-forms.input name="phone" type="phone" required />
                </div>
                <div class="mb-3">
                    <x-forms.input-textarea name="address" />
                </div>
                <div class="mb-3">
                    <x-forms.input-select2 name="marital_status" :options="[ ['single', __('label.single')], ['married', __('label.married')], ['divorced', __('label.divorced')], ['widowed', __('label.widowed')] ]" />
                </div>
                <button type="submit" class="btn btn-primary">{{ __('button.submit') }}</button>
                <button type="reset" class="btn btn-secondary">{{ __('button.reset') }}</button>
            </form>
        </div>
    </div>

@endsection
