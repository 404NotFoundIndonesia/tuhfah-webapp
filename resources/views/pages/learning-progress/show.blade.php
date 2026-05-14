@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="py-3 mb-0">
            <a class="text-muted fw-light" href="{{ route('learning-progress.index') }}">{{ __('menu.learning_progress') }} /</a>
            {{ __('label.detail') }}
        </h4>
        <a href="{{ route('learning-progress.index') }}" class="btn btn-secondary text-white fw-medium">
            {{ __('button.back') }}
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">{{ __('field.name') }}</dt>
                <dd class="col-sm-9">{{ optional($progress->student)->name ?? '-' }}</dd>

                <dt class="col-sm-3">{{ __('field.date') }}</dt>
                <dd class="col-sm-9">{{ $progress->date->format('Y-m-d') }}</dd>

                <dt class="col-sm-3">{{ __('field.subject') }}</dt>
                <dd class="col-sm-9">{{ $progress->subject }}</dd>

                <dt class="col-sm-3">{{ __('field.milestone') }}</dt>
                <dd class="col-sm-9">{{ $progress->milestone }}</dd>

                <dt class="col-sm-3">{{ __('field.score') }}</dt>
                <dd class="col-sm-9">{{ $progress->score !== null ? number_format($progress->score, 1) : '-' }}</dd>

                <dt class="col-sm-3">{{ __('field.notes') }}</dt>
                <dd class="col-sm-9">{{ $progress->notes ?? '-' }}</dd>

                <dt class="col-sm-3">{{ __('field.teacher_id') }}</dt>
                <dd class="col-sm-9">{{ optional($progress->teacher)->name ?? '-' }}</dd>
            </dl>
        </div>
    </div>

    @php
        $subjects = \App\Models\LearningProgress::where('student_id', $progress->student_id)
            ->distinct()->pluck('subject');
    @endphp

    @if($progress->student)
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">{{ __('label.progress_chart') }}</h5>
                <select id="chart-subject" class="form-select form-select-sm" style="width:200px">
                    <option value="">{{ __('label.all_subjects') }}</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject }}" @selected($subject === $progress->subject)>{{ $subject }}</option>
                    @endforeach
                </select>
            </div>
            <div class="card-body">
                <div id="progress-chart"></div>
            </div>
        </div>
    @endif
@endsection

@push('style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}"/>
@endpush

@push('script')
    <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
    <script>
        const studentId = {{ $progress->student_id }};
        let chart = null;

        function buildChart(data) {
            const el = document.getElementById('progress-chart');

            if (chart) { chart.destroy(); chart = null; }

            if (!data.length) {
                el.innerHTML = '<p class="text-muted text-center py-4">{{ __('label.no_progress_records') }}</p>';
                return;
            }

            const grouped = {};
            data.forEach(r => {
                if (!grouped[r.subject]) grouped[r.subject] = [];
                grouped[r.subject].push({x: r.date, y: r.score});
            });

            const series = Object.entries(grouped).map(([name, data]) => ({name, data}));

            chart = new ApexCharts(el, {
                series,
                chart: {type: 'line', height: 300, toolbar: {show: false}},
                xaxis: {type: 'datetime'},
                yaxis: {min: 0, max: 100, title: {text: '{{ __('field.score') }}'}},
                markers: {size: 4},
                stroke: {curve: 'smooth', width: 2},
                tooltip: {x: {format: 'yyyy-MM-dd'}},
            });
            chart.render();
        }

        function loadChart(subject) {
            const params = new URLSearchParams({student_id: studentId});
            if (subject) params.append('subject', subject);

            fetch(`{{ route('learning-progress.chart-data') }}?` + params.toString())
                .then(r => r.json())
                .then(buildChart);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const sel = document.getElementById('chart-subject');
            loadChart(sel ? sel.value : '');
            if (sel) sel.addEventListener('change', () => loadChart(sel.value));
        });
    </script>
@endpush
