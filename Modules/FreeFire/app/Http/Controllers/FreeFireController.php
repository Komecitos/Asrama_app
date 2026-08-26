<?php

namespace Modules\FreeFire\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\FreeFire\Models\FreefireSpinSession;
use Modules\FreeFire\Models\FreefireSpinLog;
use Modules\FreeFire\Models\FreefireWheelSlot;
use Modules\FreeFire\Services\FreefireWheelCalculator;

class FreeFireController extends Controller
{
    public function index()
    {
        return redirect()->route('freefire.calc');
    }

    public function calc()
    {
        $completedSessions = FreefireSpinSession::whereIn('spin_type', ['token_ring', 'token_tower'])
            ->where('status', 'completed')
            ->orderBy('updated_at', 'desc')
            ->limit(15)
            ->get();

        $completedTokenSessions = $completedSessions->where('spin_type', 'token_ring');
        $completedTowerSessions = $completedSessions->where('spin_type', 'token_tower');

        return view('freefire::calc', compact('completedTokenSessions', 'completedTowerSessions'));
    }
    public function session()
    {
        // Auto-complete expired sessions
        FreefireSpinSession::where('status', 'active')
            ->whereNotNull('event_end')
            ->whereDate('event_end', '<', now()->toDateString())
            ->update(['status' => 'completed']);

        $activeSessions = FreefireSpinSession::where('status', 'active')
            ->with(['slots', 'logs'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($session) {
                // Kumpulkan item yang sudah didapat langsung dari log
                $session->obtained_items = $session->logs
                    ->filter(fn($log) => !is_null($log->result) && str_starts_with($log->result, 'Item: '))
                    ->map(fn($log) => ltrim(substr($log->result, 6)))
                    ->values();

                if ($session->spin_type === 'token_ring') {
                    $estimates = FreefireWheelCalculator::buildSessionEstimates($session);

                    $session->token_target = FreefireWheelCalculator::resolveTargetToken($session);
                    $session->expected_token_per_spin = $estimates['expected_token_per_spin'];
                    $session->avg_token_per_spin = $estimates['avg_token_per_spin'];
                    $session->luck_actual = $estimates['luck_actual'];
                    $session->item_estimates = $estimates['items'];
                    $session->next_spin_cost = 0;
                    $session->remaining_faded_cost = 0;
                    $session->remaining_token = $session->token_target !== null
                        ? max(0, $session->token_target - $session->current_token)
                        : 0;

                    $tokenRate = ($session->avg_token_per_spin !== null && $session->avg_token_per_spin > 0)
                        ? $session->avg_token_per_spin
                        : ($session->expected_token_per_spin > 0 ? $session->expected_token_per_spin : 3);

                    $session->est_spins_left = ($session->remaining_token > 0 && $tokenRate > 0)
                        ? (int) ceil($session->remaining_token / $tokenRate)
                        : 0;
                    $session->est_diamond_left = FreefireWheelCalculator::spinsToDiamond($session->est_spins_left);
                } else if ($session->spin_type === 'faded_wheel') {
                    $session->luck_actual = 50;
                    $session->remaining_token = 0;
                    $session->est_diamond_left = 0;
                    $session->est_spins_left = 0;
                    $session->expected_token_per_spin = 0;

                    $session->next_spin_cost = FreefireWheelCalculator::fadedPrice(
                        $session->current_spin,
                        $session->discount_percentage
                    );

                    $session->remaining_faded_cost = FreefireWheelCalculator::remainingFadedCost(
                        $session->current_spin,
                        $session->discount_percentage
                    );
                } else {
                    if ($session->spin_type === 'faded_wheel') {
                        $session->luck_actual = 50;
                        $session->remaining_token = 0;
                        $session->est_diamond_left = 0;
                        $session->est_spins_left = 0;
                        $session->expected_token_per_spin = 0;

                        $session->next_spin_cost = isset($fadedPrices[$session->current_spin])
                            ? round($fadedPrices[$session->current_spin] * (1 - $session->discount_percentage / 100))
                            : 0;

                        $remainingFadedCost = 0;
                        for ($i = $session->current_spin; $i < 8; $i++) {
                            $remainingFadedCost += round($fadedPrices[$i] * (1 - $session->discount_percentage / 100));
                        }
                        $session->remaining_faded_cost = $remainingFadedCost;
                    } else {
                        // Token Tower
                        $towerPity = [20, 35, 50, 80, 100];
                        $currentTokenLevel = $session->current_token; // 0-5
                        $remaining = max(0, 5 - $currentTokenLevel);

                        $remainingPitySpins = 0;
                        for ($i = $currentTokenLevel; $i < 5; $i++) {
                            $remainingPitySpins += $towerPity[$i];
                        }

                        $session->remaining_token = $remaining;
                        $session->est_spins_left = $remainingPitySpins;

                        $fiveSpins = floor($remainingPitySpins / 5);
                        $oneSpins = $remainingPitySpins % 5;
                        $session->est_diamond_left = ($fiveSpins * 79) + ($oneSpins * 19);

                        $session->luck_actual = 0;
                        $session->next_spin_cost = 0;
                        $session->remaining_faded_cost = 0;
                        $session->expected_token_per_spin = 0;
                    }
                }

                return $session;
            });

        $completedSessions = FreefireSpinSession::where('status', 'completed')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        return view('freefire::session', compact('activeSessions', 'completedSessions'));
    }

    public function storeSession(Request $request)
    {
        $request->validate([
            'item_name'           => 'required|string|max:255',
            'spin_type'           => 'required|in:token_ring,faded_wheel,token_tower',
            'token_needed'        => 'nullable|integer|min:1',
            'discount_percentage' => 'nullable|integer|min:0|max:100',
            'event_start'         => 'nullable|date',
            'event_end'           => 'nullable|date|after_or_equal:event_start',
            'slots'               => 'nullable|array',
        ]);

        $session = FreefireSpinSession::create([
            'item_name'           => $request->item_name,
            'spin_type'           => $request->spin_type,
            'token_needed'        => $request->spin_type === 'token_tower' ? 5 : $request->token_needed,
            'luck_percentage'     => $request->tower_luck ?? 0,
            'discount_percentage' => $request->has_discount ? 1 : 0,
            'modal_diamond'       => 0,
            'spent_diamond'       => 0,
            'current_spin'        => 0,
            'current_token'       => 0,
            'status'              => 'active',
            'event_start'         => $request->event_start,
            'event_end'           => $request->event_end,
            'starting_token'      => $request->starting_token ?? 0,
            'ticket_count'        => $request->ticket_count ?? 0,
        ]);

        if ($request->slots) {
            foreach ($request->slots as $slot) {
                $type = $slot['type'] ?? 'token';
                $slotCount = intval($slot['slot_count'] ?? 0);

                if ($type === 'item') {
                    if (empty(trim($slot['item_name'] ?? ''))) continue;
                    $slotCount = max(1, $slotCount);
                } else {
                    if ($slotCount === 0) continue;
                }

                FreefireWheelSlot::create([
                    'session_id'     => $session->id,
                    'type'           => $type,
                    'token_value'    => ($type === 'token') ? ($slot['token_value'] ?? null) : null,
                    'item_name'      => ($type === 'item') ? trim($slot['item_name']) : null,
                    'token_exchange' => ($type === 'item') ? ($slot['token_exchange'] ?? null) : null,
                    'rarity'         => $slot['rarity'] ?? 'epic',
                    'slot_count'     => $slotCount,
                ]);
            }
        }

        return redirect()->route('freefire.session')->with('success', 'Sesi spin baru dibuat!');
    }

    public function updateSession(Request $request, $id)
    {
        $session = FreefireSpinSession::findOrFail($id);

        $request->validate([
            'item_name'      => 'required|string|max:255',
            'spin_type'      => 'required|in:token_ring,faded_wheel,token_tower',
            'token_needed'   => 'nullable|integer|min:1',
            'event_start'    => 'nullable|date',
            'event_end'      => 'nullable|date|after_or_equal:event_start',
            'spent_diamond'  => 'nullable|integer|min:0',
            'current_spin'   => 'nullable|integer|min:0',
            'current_token'  => 'nullable|integer|min:0',
            'starting_token' => 'nullable|integer|min:0',
            'ticket_count'   => 'nullable|integer|min:0',
            'status'         => 'required|in:active,completed',
            'slots'          => 'nullable|array',
        ]);

        $session->update([
            'item_name'           => $request->item_name,
            'spin_type'           => $request->spin_type,
            'token_needed'        => $request->spin_type === 'token_tower' ? 5 : ($request->token_needed ?? $session->token_needed),
            'event_start'         => $request->event_start,
            'event_end'           => $request->event_end,
            'spent_diamond'       => $request->spent_diamond ?? $session->spent_diamond,
            'current_spin'        => $request->current_spin ?? $session->current_spin,
            'current_token'       => $request->current_token ?? $session->current_token,
            'starting_token'      => $request->starting_token ?? $session->starting_token,
            'ticket_count'        => $request->ticket_count ?? $session->ticket_count,
            'discount_percentage' => $request->has_discount ? 1 : 0,
            'luck_percentage'     => $request->tower_luck ?? $session->luck_percentage,
            'status'              => $request->status,
        ]);

        if ($request->has('slots') && is_array($request->slots)) {
            $session->slots()->delete();
            foreach ($request->slots as $slot) {
                $type = $slot['type'] ?? 'token';
                $slotCount = intval($slot['slot_count'] ?? 0);

                if ($type === 'item') {
                    if (empty(trim($slot['item_name'] ?? ''))) continue;
                    $slotCount = max(1, $slotCount);
                } else {
                    if ($slotCount === 0) continue;
                }

                FreefireWheelSlot::create([
                    'session_id'     => $session->id,
                    'type'           => $type,
                    'token_value'    => ($type === 'token') ? ($slot['token_value'] ?? null) : null,
                    'item_name'      => ($type === 'item') ? trim($slot['item_name']) : null,
                    'token_exchange' => ($type === 'item') ? ($slot['token_exchange'] ?? null) : null,
                    'rarity'         => $slot['rarity'] ?? 'epic',
                    'slot_count'     => $slotCount,
                ]);
            }
        }

        return redirect()->route('freefire.session')->with('success', 'Sesi spin berhasil diperbarui!');
    }

    public function addLog(Request $request, $id)
    {
        $session = FreefireSpinSession::findOrFail($id);

        $request->validate([
            'spin_count'         => 'required|integer|min:1',
            'diamond_spent'      => 'required|integer|min:0',
            'token_gained'       => 'nullable|integer|min:0',
            'tower_token_number' => 'nullable|integer|min:1|max:5',
            'got_item_id'        => 'nullable|array',
            'direct_drop'        => 'nullable',
            'direct_item_name'   => 'nullable|string|max:255',
            'auto_complete'      => 'nullable|boolean',
        ]);

        $session->current_spin += $request->spin_count;
        $session->spent_diamond += $request->diamond_spent;

        $resultParts = [];

        if ($session->spin_type === 'token_tower') {
            if ($request->tower_token_number) {
                $session->current_token = max($session->current_token, $request->tower_token_number);
                $resultParts[] = 'Naik ke Token ' . $request->tower_token_number;
            }
        } else {
            $session->current_token += $request->token_gained ?? 0;
            if ($request->token_gained > 0) {
                $resultParts[] = 'Token +' . $request->token_gained;
            }
        }

        // Direct Item Drop (dapat hadiah langsung)
        if ($request->has('direct_drop') || !empty($request->direct_item_name)) {
            $directItemName = !empty($request->direct_item_name) ? trim($request->direct_item_name) : $session->item_name;
            $existing = $session->obtained_items ?? [];
            if (!in_array($directItemName, $existing)) {
                $existing[] = $directItemName;
            }
            $session->obtained_items = array_values($existing);
            $resultParts[] = '🎁 Hadiah Langsung: ' . $directItemName;

            if ($request->filled('auto_complete') && $request->auto_complete == 1) {
                $session->status = 'completed';
            }
        }

        // Item dari slot wheel (Token Ring)
        if ($request->got_item_id && is_array($request->got_item_id)) {
            $obtainedNames = [];
            foreach ($request->got_item_id as $slotId) {
                $slot = FreefireWheelSlot::find($slotId);
                if ($slot && $slot->type === 'item') {
                    $obtainedNames[] = $slot->item_name;
                }
            }

            if (!empty($obtainedNames)) {
                $existing = $session->obtained_items ?? [];
                $session->obtained_items = array_values(array_unique(array_merge($existing, $obtainedNames)));
                $resultParts[] = '🎁 Dapat item: ' . implode(', ', $obtainedNames);
            }
        }

        $session->save();

        FreefireSpinLog::create([
            'session_id'    => $session->id,
            'spin_number'   => $session->current_spin,
            'diamond_spent' => $request->diamond_spent,
            'result'        => !empty($resultParts) ? implode(' · ', $resultParts) : null,
            'token_gained'  => $request->token_gained ?? 0,
        ]);

        if ($session->spin_type === 'token_tower' && $session->current_token >= 5) {
            $session->update(['status' => 'completed']);
            return redirect()->route('freefire.session')->with('success', 'Selamat! Bundle utama berhasil didapat! 🎉');
        }

        if ($session->status === 'completed') {
            return redirect()->route('freefire.session')->with('success', 'Selamat! Target berhasil didapat & sesi selesai! 🎉');
        }

        return redirect()->route('freefire.session')->with('success', 'Spin dicatat!');
    }

    public function completeSession($id)
    {
        $session = FreefireSpinSession::findOrFail($id);
        $session->update(['status' => 'completed']);

        return redirect()->route('freefire.session')->with('success', 'Sesi selesai!');
    }

    public function reopenSession($id)
    {
        $session = FreefireSpinSession::findOrFail($id);
        $session->update(['status' => 'active']);

        return redirect()->route('freefire.session')->with('success', 'Sesi spin diaktifkan kembali!');
    }

    public function destroy($id)
    {
        $session = FreefireSpinSession::findOrFail($id);
        $session->delete();

        return redirect()->route('freefire.session')->with('success', 'Sesi dihapus!');
    }

    public function info()
    {
        return view('freefire::info');
    }
}
