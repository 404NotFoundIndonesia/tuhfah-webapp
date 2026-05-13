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

                        <h4 class="mb-2">{{ __('label.verify_email') }}</h4>
                        <p class="mb-4">{{ __('label.verify_email_description') }}</p>

                        @session('status')
                        <div class="alert alert-success alert-dismissible" role="alert">
                            {{ $value }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @endsession

                        <form method="POST" action="{{ route('verification.send') }}" class="mb-3">
                            @csrf
                            <button class="btn btn-primary d-grid w-100">{{ __('button.resend_verification_email') }}</button>
                        </form>

                        <form method="POST" action="{{ route('logout') }}" class="text-center">
                            @csrf
                            <button type="submit" class="btn btn-link">{{ __('button.logout') }}</button>
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
