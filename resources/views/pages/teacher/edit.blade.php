@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="py-3 mb-0">
            <a class="text-muted fw-light" href="{{ route('teacher.index') }}">{{ __('menu.teacher') }} /</a>
            {{ __('label.edit') }}
        </h4>
        <div>
            <a href="{{ route('teacher.index') }}" class="btn btn-secondary text-white fw-medium">
                {{ __('button.back') }}
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <form action="{{ route('teacher.update', $teacher) }}" method="post" enctype="multipart/form-data">
            <div class="card-header">
                <div class="d-flex align-items-start align-items-sm-center gap-4">
                    <a data-fslightbox href="{{ $teacher->image_url }}">
                        <img src="{{ $teacher->image_url }}"
                             alt="user-avatar" class="d-block rounded" height="100" width="100" id="uploadImage">
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

                        <small class="text-muted mb-0 d-block">{{ __('label.allowed_image_upload') }}</small>
                    </div>
                </div>
            </div>
            <hr class="m-0">
            <div class="card-body">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" value="{{ $teacher->id }}">
                <div class="mb-3">
                    <x-forms.input name="name" required :value="$teacher->name"/>
                </div>
                <div class="mb-3">
                    <x-forms.input name="email" type="email" required :value="$teacher->email"/>
                </div>
                <div class="mb-3">
                    <x-forms.input-select2 name="gender" required :value="$teacher->gender"
                                           :options="[ ['male', __('label.male')], ['female', __('label.female')] ]"/>
                </div>
                <div class="mb-3">
                    <x-forms.input name="phone" type="phone" required :value="$teacher->phone"/>
                </div>
                <div class="mb-3">
                    <x-forms.input-textarea name="address" :value="$teacher->address"/>
                </div>
                <div class="mb-3">
                    <x-forms.input-select2 name="marital_status" :value="$teacher->marital_status"
                                           :options="[ ['single', __('label.single')], ['married', __('label.married')], ['divorced', __('label.divorced')], ['widowed', __('label.widowed')] ]"/>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary me-2">{{ __('button.submit') }}</button>
                    <button type="reset" class="btn btn-outline-secondary">{{ __('button.reset') }}</button>
                </div>
            </div>
        </form>
    </div>

@endsection

@push('script')
    <script>
        document.addEventListener("DOMContentLoaded", (function (e) {

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

        }));
    </script>
@endpush
