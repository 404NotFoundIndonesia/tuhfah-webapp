<?php

namespace App\Http\Controllers;

use App\Enum\AnnouncementScope;
use App\Models\Announcement;
use App\Services\DashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\View\View;

class PageController extends Controller
{
    public function welcome(Request $request): View
    {
        $announcements = Announcement::published()
            ->where('scope', AnnouncementScope::PUBLIC->value)
            ->latest('published_at')
            ->take(10)
            ->get();

        return view('welcome', compact('announcements'));
    }

    public function dashboard(Request $request, DashboardService $dashboardService): View
    {
        $announcements = Announcement::published()
            ->latest('published_at')
            ->take(5)
            ->get();

        $stats = $dashboardService->stats($request->user());

        return view('pages.dashboard', compact('announcements', 'stats'));
    }

    public function locale(Request $request): RedirectResponse
    {
        $locale = $request->query('locale');
        if (in_array($locale, array_keys(config('app.available_locales')))) {
            $request->user()->update(['locale' => $locale]);
            session(['locale' => $locale]);
            App::setLocale($locale);
        } else {
            return back()->with('notification', ['icon' => 'error', 'title' => __('menu.locale'), 'message' => __('notification.locale_not_available')]);
        }

        return back()->with('notification', ['icon' => 'success', 'title' => __('menu.locale'), 'message' => __('notification.locale_success')]);
    }
}
