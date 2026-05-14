<?php

namespace App\Http\Controllers;

use App\Enum\ItemCondition;
use App\Enum\Role;
use App\Http\Requests\LogInventoryRequest;
use App\Http\Requests\StoreInventoryRequest;
use App\Http\Requests\UpdateInventoryRequest;
use App\Models\Inventory;
use App\Models\InventoryLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class InventoryController extends Controller
{
    private function authorizeView(): void
    {
        abort_unless(
            auth()->user()->isRole(Role::OWNER)
                || auth()->user()->isRole(Role::HEADMASTER)
                || auth()->user()->isRole(Role::ADMINISTRATOR),
            403
        );
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()->isRole(Role::ADMINISTRATOR), 403);
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request): View|JsonResponse
    {
        $this->authorizeView();

        if ($request->ajax()) {
            return DataTables::eloquent(Inventory::query())
                ->editColumn('condition', fn ($row) => __('label.'.$row->condition->value))
                ->editColumn('acquisition_date', fn ($row) => $row->acquisition_date->format('Y-m-d'))
                ->filterColumn('condition', fn ($query, $keyword) => $query->where('condition', $keyword))
                ->addColumn('action', function ($row) {
                    $isAdmin = auth()->user()->isRole(Role::ADMINISTRATOR);
                    $actions = '<a class="dropdown-item" href="'.route('inventory.show', $row).'">
                                    <i class="bx bx-show me-1"></i>'.__('label.detail').'
                                </a>';

                    if ($isAdmin) {
                        $actions .= '<a class="dropdown-item" href="'.route('inventory.edit', $row).'">
                                        <i class="bx bx-edit-alt me-1"></i>'.__('label.edit').'
                                    </a>
                                    <form action="'.route('inventory.destroy', $row).'" method="post" onsubmit="confirmSubmit(event, this)">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="'.csrf_token().'" />
                                        <button class="dropdown-item text-danger" type="submit"><i class="bx bx-trash me-1"></i>'.__('label.delete').'</button>
                                    </form>';
                    }

                    return '<div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu">'.$actions.'</div>
                            </div>';
                })
                ->rawColumns(['action'])
                ->toJson();
        }

        $conditions = ItemCondition::cases();

        return view('pages.inventory.index', compact('conditions'));
    }

    // ── Create / Store ────────────────────────────────────────────────────────

    public function create(): View
    {
        $this->authorizeAdmin();
        $conditions = ItemCondition::cases();

        return view('pages.inventory.create', compact('conditions'));
    }

    public function store(StoreInventoryRequest $request): RedirectResponse
    {
        Inventory::create($request->validated());

        return redirect()->route('inventory.index')
            ->with('success', __('label.inventory_created'));
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function show(Inventory $inventory): View
    {
        $this->authorizeView();
        $inventory->load('logs.recordedBy');

        return view('pages.inventory.show', compact('inventory'));
    }

    // ── Edit / Update ─────────────────────────────────────────────────────────

    public function edit(Inventory $inventory): View
    {
        $this->authorizeAdmin();
        $conditions = ItemCondition::cases();

        return view('pages.inventory.edit', compact('inventory', 'conditions'));
    }

    public function update(UpdateInventoryRequest $request, Inventory $inventory): RedirectResponse
    {
        $inventory->update($request->validated());

        return redirect()->route('inventory.index')
            ->with('success', __('label.inventory_updated'));
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(Inventory $inventory): RedirectResponse
    {
        $this->authorizeAdmin();
        $inventory->delete();

        return redirect()->route('inventory.index')
            ->with('success', __('label.inventory_deleted'));
    }

    // ── Log Usage / Disposal (T7.3) ───────────────────────────────────────────

    public function log(LogInventoryRequest $request, Inventory $inventory): RedirectResponse
    {
        InventoryLog::create([
            'inventory_id' => $inventory->id,
            'type' => $request->type,
            'quantity_changed' => $request->quantity_changed,
            'reason' => $request->reason,
            'recorded_by' => auth()->id(),
        ]);

        $inventory->decrement('quantity', $request->quantity_changed);

        return redirect()->route('inventory.show', $inventory)
            ->with('success', __('label.inventory_log_created'));
    }
}
