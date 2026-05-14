<?php

namespace App\Http\Controllers;

use App\Enum\AnnouncementScope;
use App\Enum\Role;
use App\Http\Requests\StoreAnnouncementRequest;
use App\Http\Requests\UpdateAnnouncementRequest;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
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

    public function index(Request $request): View
    {
        $isAdmin = auth()->user()->isRole(Role::OWNER)
            || auth()->user()->isRole(Role::HEADMASTER)
            || auth()->user()->isRole(Role::ADMINISTRATOR);

        $announcements = Announcement::with('author')
            ->when(! $isAdmin, fn ($q) => $q->published())
            ->latest('published_at')
            ->paginate(15);

        return view('pages.announcement.index', compact('announcements', 'isAdmin'));
    }

    // ── Create / Store ────────────────────────────────────────────────────────

    public function create(): View
    {
        $this->authorizeAdmin();
        $scopes = AnnouncementScope::cases();

        return view('pages.announcement.create', compact('scopes'));
    }

    public function store(StoreAnnouncementRequest $request): RedirectResponse
    {
        Announcement::create([
            'title' => $request->title,
            'body' => $request->body,
            'scope' => $request->scope,
            'published_at' => $request->published_at,
            'author_id' => auth()->id(),
        ]);

        return redirect()->route('announcement.index')
            ->with('success', __('label.announcement_created'));
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function show(Announcement $announcement): View
    {
        // Guests and non-admin users can only see published public announcements
        if (! auth()->check()) {
            abort_unless(
                $announcement->scope === AnnouncementScope::PUBLIC
                    && $announcement->published_at !== null
                    && $announcement->published_at->isPast(),
                403
            );
        } elseif (! (auth()->user()->isRole(Role::OWNER)
            || auth()->user()->isRole(Role::HEADMASTER)
            || auth()->user()->isRole(Role::ADMINISTRATOR))) {
            abort_unless($announcement->published_at !== null && $announcement->published_at->isPast(), 403);
        }

        $announcement->load('author');

        return view('pages.announcement.show', compact('announcement'));
    }

    // ── Edit / Update ─────────────────────────────────────────────────────────

    public function edit(Announcement $announcement): View
    {
        $this->authorizeAdmin();
        $scopes = AnnouncementScope::cases();

        return view('pages.announcement.edit', compact('announcement', 'scopes'));
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $announcement->update([
            'title' => $request->title,
            'body' => $request->body,
            'scope' => $request->scope,
            'published_at' => $request->published_at,
        ]);

        return redirect()->route('announcement.index')
            ->with('success', __('label.announcement_updated'));
    }

    // ── Publish ───────────────────────────────────────────────────────────────

    public function publish(Announcement $announcement): RedirectResponse
    {
        $this->authorizeAdmin();

        if ($announcement->published_at === null) {
            $announcement->update(['published_at' => now()]);
        }

        return redirect()->route('announcement.show', $announcement)
            ->with('success', __('label.announcement_published'));
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $this->authorizeAdmin();
        $announcement->delete();

        return redirect()->route('announcement.index')
            ->with('success', __('label.announcement_deleted'));
    }
}
