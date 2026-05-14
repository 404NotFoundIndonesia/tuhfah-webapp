<?php

namespace App\Http\Controllers;

use App\Enum\PaymentStatus;
use App\Enum\Role;
use App\Enum\StudentStatus;
use App\Exports\PaymentExport;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Payment;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class PaymentController extends Controller
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
            $query = Payment::with(['student', 'recordedBy'])->orderByDesc('due_date');

            return DataTables::eloquent($query)
                ->addColumn('student_name', fn ($row) => optional($row->student)->name ?? '-')
                ->addColumn('recorded_by_name', fn ($row) => optional($row->recordedBy)->name ?? '-')
                ->editColumn('due_date', fn ($row) => $row->due_date->format('Y-m-d'))
                ->editColumn('paid_at', fn ($row) => $row->paid_at?->format('Y-m-d H:i') ?? '-')
                ->editColumn('amount', fn ($row) => number_format((float) $row->amount, 0, '.', ','))
                ->editColumn('status', fn ($row) => $row->status->value)
                ->addColumn('action', function ($row) {
                    $markPaid = $row->status !== PaymentStatus::PAID
                        ? '<a class="dropdown-item" href="'.route('payment.mark-paid', $row).'"
                             onclick="event.preventDefault(); document.getElementById(\'mark-paid-'.$row->id.'\').submit();">
                             '.__('button.mark_paid').'
                           </a>
                           <form id="mark-paid-'.$row->id.'" action="'.route('payment.mark-paid', $row).'" method="POST" style="display:none">
                             '.csrf_field().'<input type="hidden" name="_method" value="PATCH">
                           </form>'
                        : '';

                    return '<div class="dropdown">
                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="'.route('payment.show', $row).'">
                                <i class="bx bx-show me-1"></i>'.__('label.detail').'
                            </a>
                            '.$markPaid.'
                            <a class="dropdown-item text-danger" href="'.route('payment.destroy', $row).'"
                               onclick="event.preventDefault(); if(confirm(\''.__('label.are_you_sure').'\')) document.getElementById(\'del-'.$row->id.'\').submit();">
                                <i class="bx bx-trash me-1"></i>'.__('label.delete').'
                            </a>
                            <form id="del-'.$row->id.'" action="'.route('payment.destroy', $row).'" method="POST" style="display:none">
                                '.csrf_field().'<input type="hidden" name="_method" value="DELETE">
                            </form>
                        </div>
                    </div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('pages.payment.index');
    }

    // ── Create / Store ────────────────────────────────────────────────────────

    public function create(): View
    {
        $this->authorizeAdmin();
        $students = Student::where('status', StudentStatus::ACTIVE->value)->orderBy('name')->get();

        return view('pages.payment.create', compact('students'));
    }

    public function store(StorePaymentRequest $request): RedirectResponse
    {
        Payment::create([
            'student_id' => $request->student_id,
            'period' => $request->period,
            'amount' => $request->amount,
            'status' => PaymentStatus::UNPAID->value,
            'due_date' => $request->due_date,
            'paid_at' => null,
            'recorded_by' => auth()->id(),
        ]);

        return redirect()->route('payment.index')
            ->with('success', __('label.payment_created'));
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function show(Payment $payment): View
    {
        $this->authorizeAdmin();
        $payment->load(['student', 'recordedBy']);

        return view('pages.payment.show', compact('payment'));
    }

    // ── Mark Paid ─────────────────────────────────────────────────────────────

    public function markPaid(Payment $payment): RedirectResponse
    {
        $this->authorizeAdmin();

        $payment->update([
            'status' => PaymentStatus::PAID->value,
            'paid_at' => now(),
            'recorded_by' => auth()->id(),
        ]);

        return redirect()->route('payment.index')
            ->with('success', __('label.payment_marked_paid'));
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(Payment $payment): RedirectResponse
    {
        $this->authorizeAdmin();
        $payment->delete();

        return redirect()->route('payment.index')
            ->with('success', __('label.payment_deleted'));
    }

    // ── Guardian View (T4.4) ──────────────────────────────────────────────────

    public function guardianIndex(Request $request): View
    {
        abort_unless(auth()->user()->isRole(Role::STUDENT_GUARDIAN), 403);

        $student = Student::where('student_guardian_id', auth()->id())->firstOrFail();

        $records = $student->payments()
            ->when($request->period, fn ($q) => $q->where('period', $request->period))
            ->orderByDesc('due_date')
            ->paginate(15);

        $periods = $student->payments()->distinct()->orderByDesc('period')->pluck('period');

        return view('pages.payment.guardian', compact('records', 'student', 'periods'));
    }

    // ── Export (T4.7) ─────────────────────────────────────────────────────────

    public function export(Request $request): Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->authorizeAdmin();

        $period = $request->period;
        $format = $request->format ?? 'xlsx';

        if ($format === 'pdf') {
            $payments = Payment::with(['student', 'recordedBy'])
                ->when($period, fn ($q) => $q->where('period', $period))
                ->orderBy('period')->orderBy('student_id')
                ->get();

            $pdf = Pdf::loadView('exports.payment-pdf', compact('payments', 'period'));

            return $pdf->download('payments'.($period ? '-'.$period : '').'.pdf');
        }

        return Excel::download(new PaymentExport($period), 'payments'.($period ? '-'.$period : '').'.xlsx');
    }
}
