@extends('layouts.app')

@section('body')
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner py-4">
                <div class="card">
                    <div class="card-body">
                        <div class="app-brand justify-content-center">
                            <a href="{{ route('welcome') }}" class="app-brand-link gap-2">
                                <span class="app-brand-logo demo">
                                    <img src="{{ asset('404_Black.jpg') }}" alt="{{ config('app.name') }}" width="30"
                                         style="border-radius: 150px">
                                </span>
                                <span class="app-brand-text text-body fw-bold fs-3">{{ config('app.name') }}</span>
                            </a>
                        </div>

                        <h4 class="mb-2">{{ __('label.confirm_password') }}</h4>
                        <p class="mb-4">{{ __('label.confirm_password_description') }}</p>

                        <form method="POST" action="{{ route('password.confirm') }}" class="mb-3">
                            @csrf
                            <div class="mb-3">
                                <x-forms.input name="password" :value="null" type="password"/>
                            </div>
                            <button class="btn btn-primary d-grid w-100">{{ __('button.confirm') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-auth.css') }}"/>
@endpush
