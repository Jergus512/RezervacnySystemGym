<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $announcements = Announcement::query()
            ->currentlyActive()
            ->orderByDesc('active_from')
            ->orderByDesc('created_at')
            ->get();

        return view('announcements.index', compact('announcements'));
    }

    public function current(Request $request)
    {
        $announcements = Announcement::query()
            ->currentlyActive()
            ->orderByDesc('active_from')
            ->orderByDesc('created_at')
            ->get(['id', 'title', 'content', 'active_from', 'active_to']);

        return response()->json([
            'server_now' => now()->toIso8601String(),
            'data' => $announcements,
        ]);
    }
}
