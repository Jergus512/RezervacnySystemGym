<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PenaltySetting;
use Illuminate\Http\Request;

class PenaltySettingController extends Controller
{
    public function edit()
    {
        $settings = PenaltySetting::getSingleton();
        return view('admin.settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        // Accept either combined minutes (legacy) or hours+minutes from the improved UI
        $data = $request->validate([
            'refund_window_minutes' => ['nullable', 'integer', 'min:0', 'max:525600'], // up to 1 year
            'refund_window_hours' => ['nullable', 'integer', 'min:0', 'max:8760'], // up to 1 year in hours
            'refund_window_minutes_part' => ['nullable', 'integer', 'min:0', 'max:59'],
            // Only allow 'half' and 'none' as penalty policies
            'penalty_policy' => ['required', 'in:half,none'],
        ]);

        // Convert hours+minutes_part into total minutes if provided
        if ($request->filled('refund_window_hours') || $request->filled('refund_window_minutes_part')) {
            $hours = (int) $request->input('refund_window_hours', 0);
            $mins = (int) $request->input('refund_window_minutes_part', 0);
            $total = $hours * 60 + $mins;
            $data['refund_window_minutes'] = $total;
        }

        // Ensure there's a refund_window_minutes set (fallback to 0 if omitted)
        $data['refund_window_minutes'] = (int) ($data['refund_window_minutes'] ?? 0);

        $settings = PenaltySetting::getSingleton();
        $settings->update([
            'refund_window_minutes' => $data['refund_window_minutes'],
            'penalty_policy' => $data['penalty_policy'],
        ]);

        return redirect()->route('admin.settings.edit')->with('status', 'Nastavenia boli aktualizované.');
    }
}
