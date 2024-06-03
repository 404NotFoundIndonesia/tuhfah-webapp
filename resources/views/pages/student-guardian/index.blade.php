@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="py-3 mb-0">{{ __('menu.student_guardian') }}</h4>
        <div>
            <a href="{{ route('student-guardian.create') }}" class="btn btn-primary text-white fw-medium">
                + {{ __('menu.student_guardian') }}
            </a>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive pb-3 text-nowrap">
            <table class="table">
                <thead>
                <tr>
                    <th></th>
                    <th>{{ __('field.name') }}</th>
                    <th>{{ __('field.gender') }}</th>
                    <th>{{ __('field.email') }}</th>
                    <th>{{ __('field.phone') }}</th>
                    <th width="15px"></th>
                </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables/datatables.min.css') }}"/>
@endpush

@push('script')
    <script src="{{ asset('assets/vendor/libs/datatables/datatables.min.js') }}"></script>
    <script>
        const locale = document.querySelector('html').getAttribute('lang');

        const datatableObject = $('table.table').DataTable({
            processing: true,
            serverSide: true,
            ajax: window.location.href,
            columns: [
                {data: 'image', name: 'image', orderable: false, searchable: false},
                {data: 'name', name: 'name'},
                {data: 'gender', name: 'gender', orderable: false, searchable: false},
                {data: 'email', name: 'email'},
                {data: 'phone', name: 'phone'},
                {data: 'action', orderable: false, searchable: false},
            ],
            order: [[1, "asc"]],
            dom: '<"row px-4 my-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row px-4 my-3"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6 d-flex justify-content-end"p>>',
            language: {
                url: `/assets/vendor/libs/datatables/language/${locale}.json`
            }
        });

        datatableObject.on('draw', () => {
            refreshFsLightbox();
        });
    </script>
@endpush
