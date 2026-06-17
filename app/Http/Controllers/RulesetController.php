<?php

namespace App\Http\Controllers;

use App\Models\Ruleset;
use App\Services\RawgService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RulesetController extends Controller
{
    public function __construct(protected RawgService $rawg)
    {

    }

    /* ── CREATE ─────────────────────────────────────────────────── */

    public function create()
    {
        return view('rulesets.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'rawg_id'     => ['required', 'integer'],
            'game_name'   => ['required', 'string', 'max:255'],
            'game_image'  => ['nullable', 'url', 'max:500'],
            'title'       => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:1000'],
            'rules'       => ['required', 'array', 'min:1', 'max:50'],
            'rules.*'     => ['required', 'string', 'max:300'],
            'mod_url'     => ['nullable', 'url', 'max:500'],
            'is_public'   => ['required', 'boolean'],
        ]);

        // Filter out any blank rule rows the user may have left empty.
        $data['rules'] = array_values(
            array_filter($data['rules'], fn ($r) => trim($r) !== '')
        );

        if (empty($data['rules'])) {
            return back()->withInput()
                ->withErrors(['rules' => 'Add at least one rule.']);
        }

        $ruleset = Ruleset::create([
            'user_id'     => Auth::id(),
            'rawg_id'     => $data['rawg_id'],
            'game_name'   => $data['game_name'],
            'game_image'  => $data['game_image'] ?? null,
            'title'       => $data['title'],
            'description' => $data['description'],
            'rules'       => $data['rules'],          // cast to JSON in model
            'mod_url'     => $data['mod_url'] ?? null,
            'is_public'   => (bool) $data['is_public'],
        ]);

        return redirect()->route('rulesets.show', $ruleset)
            ->with('success', 'Ruleset created!');
    }

    /* ── READ ───────────────────────────────────────────────────── */

    public function show(Ruleset $ruleset)
    {
        if (! $ruleset->is_public && $ruleset->user_id !== Auth::id()) {
            abort(403);
        }

        return view('rulesets.show', compact('ruleset'));
    }

    /* ── UPDATE ─────────────────────────────────────────────────── */

    public function edit(Ruleset $ruleset)
    {
        $this->authorize('update', $ruleset);
        return view('rulesets.edit', compact('ruleset'));
    }

    public function update(Request $request, Ruleset $ruleset)
    {
        $this->authorize('update', $ruleset);

        // Same validation as store — reuse or extract to a Form Request
        $data = $request->validate([
            'rawg_id'     => ['required', 'integer'],
            'game_name'   => ['required', 'string', 'max:255'],
            'game_image'  => ['nullable', 'url', 'max:500'],
            'title'       => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:1000'],
            'rules'       => ['required', 'array', 'min:1', 'max:50'],
            'rules.*'     => ['required', 'string', 'max:300'],
            'mod_url'     => ['nullable', 'url', 'max:500'],
            'is_public'   => ['required', 'boolean'],
        ]);

        $data['rules'] = array_values(
            array_filter($data['rules'], fn ($r) => trim($r) !== '')
        );

        $ruleset->update($data);

        return redirect()->route('rulesets.show', $ruleset)
            ->with('success', 'Ruleset updated.');
    }

    /* ── DELETE ─────────────────────────────────────────────────── */

    public function destroy(Ruleset $ruleset)
    {
        $this->authorize('delete', $ruleset);
        $ruleset->delete();

        return redirect()->route('profile.index')
            ->with('success', 'Ruleset deleted.');
    }

    /* ── RAWG GAME SEARCH (called by JS) ────────────────────────── */

    public function searchGames(Request $request)
    {
        $request->validate(['q' => ['required', 'string', 'min:2', 'max:100']]);

        $raw = $this->rawg->searchGames($request->q);

        // Normalise to only what the UI needs
        $results = collect($raw['results'] ?? [])
            ->map(fn ($g) => [
                'id'               => $g['id'],
                'name'             => $g['name'],
                'background_image' => $g['background_image'] ?? null,
                'released'         => $g['released']         ?? null,
            ])
            ->values();

        return response()->json(['results' => $results]);
    }
}
