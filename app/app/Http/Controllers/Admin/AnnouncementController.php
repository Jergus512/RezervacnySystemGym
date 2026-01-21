<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $now = now();

        // Currently active announcements (visible now)
        $announcements = Announcement::query()
            ->with('creator:id,name')
            ->currentlyActive($now)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('title', 'like', '%'.$q.'%')
                        ->orWhere('content', 'like', '%'.$q.'%');
                });
            })
            ->orderByDesc('active_from')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        // Upcoming announcements: scheduled to become active in the future (active_from > now)
        $upcoming = Announcement::query()
            ->with('creator:id,name')
            ->where('is_active', true)
            ->whereNotNull('active_from')
            ->where('active_from', '>', $now)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('title', 'like', '%'.$q.'%')
                        ->orWhere('content', 'like', '%'.$q.'%');
                });
            })
            ->orderBy('active_from')
            ->orderByDesc('created_at')
            // use a different page query param to avoid colliding with the main paginator
            ->paginate(10, ['*'], 'upcoming_page')
            ->withQueryString();

        return view('admin.announcements.index', compact('announcements', 'upcoming', 'q'));
    }

    public function archive(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $now = now();

        $announcements = Announcement::query()
            ->with('creator:id,name')
            ->where(function ($query) use ($now) {
                // not active by flag OR already ended
                $query->where('is_active', false)
                    ->orWhere(function ($q) use ($now) {
                        $q->whereNotNull('active_to')->where('active_to', '<', $now);
                    });
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('title', 'like', '%'.$q.'%')
                        ->orWhere('content', 'like', '%'.$q.'%');
                });
            })
            ->orderByDesc('active_to')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.announcements.archive', compact('announcements', 'q'));
    }

    public function create()
    {
        return view('admin.announcements.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'active_from' => ['nullable', 'date'],
            'active_to' => ['nullable', 'date', 'after_or_equal:active_from'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        Announcement::create([
            'created_by_user_id' => $request->user()?->id,
            'title' => $validated['title'] ?? null,
            'content' => $validated['content'],
            'active_from' => $validated['active_from'] ?? null,
            'active_to' => $validated['active_to'] ?? null,
            // checkbox keď nepríde (napr. kvôli defaultom), nech to ostane zapnuté
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        return redirect()->route('admin.announcements.index')
            ->with('status', 'Oznam bol vytvorený.');
    }

    public function edit(Announcement $announcement)
    {
        return view('admin.announcements.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'active_from' => ['nullable', 'date'],
            'active_to' => ['nullable', 'date', 'after_or_equal:active_from'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $announcement->update([
            'title' => $validated['title'] ?? null,
            'content' => $validated['content'],
            'active_from' => $validated['active_from'] ?? null,
            'active_to' => $validated['active_to'] ?? null,
            // ak checkbox nepríde, necháme pôvodnú hodnotu
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $announcement->is_active,
        ]);

        return redirect()->route('admin.announcements.index')
            ->with('status', 'Oznam bol upravený.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return redirect()->route('admin.announcements.index')
            ->with('status', 'Oznam bol zmazaný.');
    }
}
