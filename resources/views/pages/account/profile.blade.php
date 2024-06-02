@extends('layouts.dashboard')

@section('content')
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">{{ __('menu.account') }} /</span> {{ __('menu.profile') }}
    </h4>

    <div class="row">
        <div class="col-md-12">
            <ul class="nav nav-pills flex-column flex-md-row mb-3">
                <li class="nav-item">
                    <a class="nav-link active" href="javascript:void(0);">
                        <i class="bx bx-user me-1"></i> {{ __('menu.profile') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('account.password.edit') }}">
                        <i class="bx bx-lock-open-alt me-1"></i> {{ __('menu.change_password') }}
                    </a>
                </li>
            </ul>
            <div class="card mb-4">
                <form id="formAccountSettings" action="{{ route('account.profile.update') }}" method="POST"
                      enctype="multipart/form-data">
                    <h5 class="card-header">{{ __('label.profile_information') }}</h5>
                    <!-- Account -->
                    <div class="card-body">
                        <div class="d-flex align-items-start align-items-sm-center gap-4">
                            <a data-fslightbox href="{{ $user->image_url }}">
                                <img src="{{ $user->image_url }}"
                                     alt="user-avatar" class="d-block rounded" height="100" width="100"
                                     id="uploadImage">
                            </a>
                            <div class="button-wrapper">
                                <label for="upload" class="btn btn-primary me-2 mb-4" tabindex="0">
                                    <span class="d-none d-sm-block">{{ __('button.upload') }}</span>
                                    <i class="bx bx-upload d-block d-sm-none"></i>
                                    <input type="file" id="upload" class="account-file-input" hidden="" name="image"
                                           accept="image/png,image/jpeg">
                                </label>
                                <button type="button" class="btn btn-outline-secondary account-image-reset mb-4">
                                    <i class="bx bx-reset d-block d-sm-none"></i>
                                    <span class="d-none d-sm-block">{{ __('button.reset') }}</span>
                                </button>
                                <span class="error d-block">{{ $errors->first('image') }}</span>
                                <small class="text-muted mb-0 d-block">{{ __('label.allowed_image_upload') }}</small>
                            </div>
                        </div>
                    </div>
                    <hr class="m-0">
                    <div class="card-body">
                        @csrf
                        @method('patch')
                        <div class="mb-3">
                            <x-forms.input name="name" :value="$user->name" required/>
                        </div>
                        <div class="mb-3">
                            <x-forms.input name="email" type="email" :value="$user->email" required/>
                        </div>
                        <div class="mb-3">
                            <x-forms.input-select2 name="gender" required :value="$user->gender"
                                                   :options="[ ['male', __('label.male')], ['female', __('label.female')] ]"/>
                        </div>
                        <div class="mb-3">
                            <x-forms.input name="phone" type="phone" required :value="$user->phone"/>
                        </div>
                        <div class="mb-3">
                            <x-forms.input-textarea name="address" :value="$user->address"/>
                        </div>
                        <div class="mb-3">
                            <x-forms.input-select2 name="marital_status" :value="$user->marital_status"
                                                   :options="[ ['single', __('label.single')], ['married', __('label.married')], ['divorced', __('label.divorced')], ['widowed', __('label.widowed')] ]"/>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary me-2">{{ __('button.submit') }}</button>
                            <button type="reset" class="btn btn-outline-secondary">{{ __('button.reset') }}</button>
                        </div>
                    </div>
                </form>
                <!-- /Account -->
            </div>
            <div class="card">
                <h5 class="card-header">{{ __('label.delete_account') }}</h5>
                <div class="card-body">
                    <div class="mb-3 col-12 mb-0">
                        <div class="alert alert-warning">
                            <h6 class="alert-heading fw-medium mb-1">{{ __('label.are_you_sure_delete_account') }}</h6>
                            <p class="mb-0">{{ __('label.once_your_account_deleted') }}</p>
                        </div>
                    </div>
                    <form id="formAccountDeactivation" method="post" action="{{ route('account.profile.destroy') }}">
                        @csrf
                        @method('delete')
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <x-forms.input-password name="password"/>
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" disabled type="checkbox" name="accountActivation"
                                   id="accountActivation"/>
                            <label class="form-check-label"
                                   for="accountActivation">{{ __('label.im_sure_delete_account') }}</label>
                        </div>
                        <button type="submit" disabled class="btn btn-danger deactivate-account"
                                id="accountActivationButton">{{ __('button.delete_permanently') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        const checkBox = $('#accountActivation');
        const accountActivationButton = $('#accountActivationButton');

        $('#password').on('keyup', function (e) {
            checkBox.attr("disabled", e.target.value.length === 0);
        });

        checkBox.on('change', function (e) {
            accountActivationButton.attr('disabled', !checkBox.prop('checked'));
        })

        const imageElement = document.getElementById("uploadImage");
        const imageInputElement = document.querySelector(".account-file-input");
        const imageResetEl = document.querySelector(".account-image-reset");

        if (imageElement) {
            const originalImage = imageElement.src;

            imageInputElement.onchange = () => {
                imageInputElement.files[0] && (imageElement.src = window.URL.createObjectURL(imageInputElement.files[0]));
                imageElement.closest('a').setAttribute('href', imageElement.src);
                refreshFsLightbox();
            }

            imageResetEl.onclick = () => {
                imageInputElement.value = "";
                imageElement.src = originalImage;
                imageElement.closest('a').setAttribute('href', imageElement.src);
                refreshFsLightbox();
            }
        }
    </script>
@endpush
