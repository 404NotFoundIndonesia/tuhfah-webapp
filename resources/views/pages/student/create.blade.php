@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="py-3 mb-0">
            <a class="text-muted fw-light" href="{{ route('student.index') }}">{{ __('menu.student') }} /</a>
            {{ __('label.new') }}
        </h4>
        <div>
            <a href="{{ route('student.index') }}" class="btn btn-secondary text-white fw-medium">
                {{ __('button.back') }}
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <form action="{{ route('student.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="card-header">
                <div class="d-flex align-items-start align-items-sm-center gap-4">
                    <a data-fslightbox href="{{ asset('404_Black.jpg') }}">
                        <img src="{{ asset('404_Black.jpg') }}"
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
                <div class="mb-3">
                    <x-forms.input name="student_id_number"/>
                </div>
                <div class="mb-3">
                    <x-forms.input name="name" required/>
                </div>
                <div class="mb-3">
                    <x-forms.input name="nickname"/>
                </div>
                <div class="mb-3">
                    <x-forms.input-select2 name="gender" required
                                           :options="[ ['male', __('label.male')], ['female', __('label.female')] ]"/>
                </div>
                <div class="mb-3">
                    <x-forms.input name="birthplace"/>
                </div>
                <div class="mb-3">
                    <x-forms.input name="birthdate" type="date" required />
                </div>
                <div class="mb-3">
                    <x-forms.input-select2 name="student_guardian_id" :options="array_map(fn($guardian) => [$guardian['id'], $guardian['name']], $guardians->toArray())" />
                </div>
                <div class="mb-3">
                    <x-forms.input-select2 name="status" required
                                           :options="[ ['candidate', __('label.candidate')], ['active', __('label.active')], ['graduated', __('label.graduated')], ['expelled', __('label.expelled')], ['on_leave', __('label.on_leave')], ['quit', __('label.quit')] ]"/>
                </div>
                <div class="mb-3">
                    <x-forms.input name="admission_date" type="date" required :value="date('Y-m-d')" />
                </div>
                <div class="mb-3" id="departure-date-wrapper">
                    <x-forms.input name="departure_date" required type="date" />
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

            const statusElement = $('#status');
            const departureDateWrapper = $('#departure-date-wrapper');
            const selectedStatus = statusElement.select2('data')[0].id;

            toggleDepartureDateWrapper(selectedStatus);

            statusElement.on('select2:select', function (e) {
                const data = e.params.data;
                toggleDepartureDateWrapper(data.id);
            });

            function toggleDepartureDateWrapper(status) {
                console.log(status);
                if (['candidate', 'active'].includes(status)) {
                    departureDateWrapper.hide();
                } else {
                    departureDateWrapper.show();
                }
            }
        }));
    </script>
@endpush
