<?php

namespace App\Http\Controllers;

use App\Enum\PaymentStatus;
use App\Enum\Role;
use App\Exports\HonorariumExport;
use App\Http\Requests\StoreHonorariumRequest;
use App\Models\Honorarium;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class HonorariumController extends Controller
{
    private function authorizeAdmin(): void
    {
        abort_unless(
            auth()->user()->isRole(Role::OWNER)
                || auth()->user()->isRole(Role::HEADMASTER)
                || auth()->user()->isRole(Role::ADMINISTRATOR),
            403
        );
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request): View|JsonResponse
    {
        $this->authorizeAdmin();

        if ($request->ajax()) {
            $query = Honorarium::with(['teacher', 'recordedBy'])->orderByDesc('period');

            return DataTables::eloquent($query)
                ->addColumn('teacher_name', fn ($row) => optional($row->teacher)->name ?? '-')
                ->addColumn('recorded_by_name', fn ($row) => optional($row->recordedBy)->name ?? '-')
                ->editColumn('paid_at', fn ($row) => $row->paid_at?->format('Y-m-d H:i') ?? '-')
                ->editColumn('amount', fn ($row) => number_format((float) $row->amount, 0, '.', ','))
                ->editColumn('status', fn ($row) => $row->status->value)
                ->addColumn('action', function ($row) {
                    $markPaid = $row->status !== PaymentStatus::PAID
                        ? '<a class="dropdown-item" href="'.route('honorarium.mark-paid', $row).'"
                             onclick="event.preventDefault(); document.getElementById(\'mark-paid-'.$row->id.'\').submit();">
                             '.__('button.mark_paid').'
                           </a>
                           <form id="mark-paid-'.$row->id.'" action="'.route('honorarium.mark-paid', $row).'" method="POST" style="display:none">
                             '.csrf_field().'<input type="hidden" name="_method" value="PATCH">
                           </form>'
                        : '';

                    return '<div class="dropdown">
                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="'.route('honorarium.show', $row).'">
                                <i class="bx bx-show me-1"></i>'.__('label.detail').'
                            </a>
                            '.$markPaid.'
                            <a class="dropdown-item text-danger" href="'.route('honorarium.destroy', $row).'"
                               onclick="event.preventDefault(); if(confirm(\''.__('label.are_you_sure').'\')) document.getElementById(\'del-'.$row->id.'\').submit();">
                                <i class="bx bx-trash me-1"></i>'.__('label.delete').'
                            </a>
                            <form id="del-'.$row->id.'" action="'.route('honorarium.destroy', $row).'" method="POST" style="display:none">
                                '.csrf_field().'<input type="hidden" name="_method" value="DELETE">
                            </form>
                        </div>
                    </div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('pages.honorarium.index');
    }

    // ── Create / Store ────────────────────────────────────────────────────────

    public function create(): View
    {
        $this->authorizeAdmin();
        $teachers = User::where('role', Role::TEACHER->value)->orderBy('name')->get();

        return view('pages.honorarium.create', compact('teachers'));
    }

    public function store(StoreHonorariumRequest $request): RedirectResponse
    {
        Honorarium::create([
            'teacher_id' => $request->teacher_id,
            'period' => $request->period,
            'amount' => $request->amount,
            'status' => PaymentStatus::UNPAID->value,
            'paid_at' => null,
            'recorded_by' => auth()->id(),
        ]);

        return redirect()->route('honorarium.index')
            ->with('success', __('label.honorarium_created'));
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function show(Honorarium $honorarium): View
    {
        $this->authorizeAdmin();
        $honorarium->load(['teacher', 'recordedBy']);

        return view('pages.honorarium.show', compact('honorarium'));
    }

    // ── Mark Paid ─────────────────────────────────────────────────────────────

    public function markPaid(Honorarium $honorarium): RedirectResponse
    {
        $this->authorizeAdmin();

        $honorarium->update([
            'status' => PaymentStatus::PAID->value,
            'paid_at' => now(),
            'recorded_by' => auth()->id(),
        ]);

        return redirect()->route('honorarium.index')
            ->with('success', __('label.honorarium_marked_paid'));
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(Honorarium $honorarium): RedirectResponse
    {
        $this->authorizeAdmin();
        $honorarium->delete();

        return redirect()->route('honorarium.index')
            ->with('success', __('label.honorarium_deleted'));
    }

    // ── Export (T4.7) ─────────────────────────────────────────────────────────

    public function export(Request $request): Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->authorizeAdmin();

        $period = $request->period;
        $format = $request->format ?? 'xlsx';

        if ($format === 'pdf') {
            $honorariums = Honorarium::with(['teacher', 'recordedBy'])
                ->when($period, fn ($q) => $q->where('period', $period))
                ->orderBy('period')->orderBy('teacher_id')
                ->get();

            $pdf = Pdf::loadView('exports.honorarium-pdf', compact('honorariums', 'period'));

            return $pdf->download('honorariums'.($period ? '-'.$period : '').'.pdf');
        }

        return Excel::download(new HonorariumExport($period), 'honorariums'.($period ? '-'.$period : '').'.xlsx');
    }
}
