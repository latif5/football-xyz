<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeamStoreRequest;
use App\Http\Requests\TeamUpdateRequest;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $teams = Team::query()
            ->when($request->filled('city'), fn($q) => $q->where('city', $request->string('city')))
            ->orderBy('name')
            ->paginate($request->integer('per_page', 15));
        return response()->json(['status' => 'ok', 'data' => $teams]);
    }

    public function store(TeamStoreRequest $request)
    {
        $data = $request->validated();
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }
        $team = Team::create($data);
        return response()->json(['status' => 'ok', 'data' => $team], 201);
    }

    public function show(Team $team)
    {
        $team->load('players');
        return response()->json(['status' => 'ok', 'data' => $team]);
    }

    public function update(TeamUpdateRequest $request, Team $team)
    {
        $data = $request->validated();
        if ($request->hasFile('logo')) {
            if ($team->logo) {
                Storage::disk('public')->delete($team->logo);
            }
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }
        $team->update($data);
        return response()->json(['status' => 'ok', 'data' => $team]);
    }

    public function destroy(Team $team)
    {
        $team->delete();
        return response()->json(['status' => 'ok']);
    }

    public function restore($team)
    {
        $restored = Team::withTrashed()->findOrFail($team);
        $restored->restore();
        return response()->json(['status' => 'ok', 'data' => $restored]);
    }
}
