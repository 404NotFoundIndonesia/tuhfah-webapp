@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="py-3 mb-0">
            <a class="text-muted fw-light" href="{{ route('announcement.index') }}">{{ __('menu.announcement') }} /</a>
            {{ __('label.edit') }}
        </h4>
        <a href="{{ route('announcement.index') }}" class="btn btn-secondary text-white fw-medium">
            {{ __('button.back') }}
        </a>
    </div>

    <div class="card mb-4">
        <form action="{{ route('announcement.update', $announcement) }}" method="POST">
            @csrf @method('PUT')
            <div class="card-body">
                <div class="mb-3">
                    <x-forms.input name="title" required :value="$announcement->title"/>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">{{ __('field.scope') }} <span class="text-danger">*</span></label>
                    <select name="scope" class="form-select @error('scope') is-invalid @enderror" required>
                        @foreach($scopes as $scope)
                            <option value="{{ $scope->value }}"
                                @selected(old('scope', $announcement->scope->value) === $scope->value)>
                                {{ __('label.scope_'.$scope->value) }}
                            </option>
                        @endforeach
                    </select>
                    @error('scope')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <x-forms.input name="published_at" type="datetime-local"
                        :value="$announcement->published_at?->format('Y-m-d\TH:i')"/>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">{{ __('field.body') }} <span class="text-danger">*</span></label>
                    <textarea name="body" rows="10"
                        class="form-control @error('body') is-invalid @enderror"
                        required>{{ old('body', $announcement->body) }}</textarea>
                    @error('body')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">{{ __('button.submit') }}</button>
                    @if(!$announcement->published_at)
                        <button type="button" class="btn btn-success"
                            onclick="document.getElementById('publish-form').submit()">
                            {{ __('label.publish_now') }}
                        </button>
                    @endif
                    <button type="reset" class="btn btn-outline-secondary">{{ __('button.reset') }}</button>
                </div>
            </div>
        </form>
    </div>
@endsection

@if(!$announcement->published_at)
    <form id="publish-form" action="{{ route('announcement.publish', $announcement) }}" method="POST" style="display:none">
        @csrf @method('PATCH')
    </form>
@endif
