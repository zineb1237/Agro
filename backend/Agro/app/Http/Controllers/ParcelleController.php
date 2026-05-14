<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Parcelle;
class ParcelleController extends Controller
{
    public function index()
    {
        return response()->json(Parcelle::where('proprietaire_id', auth()->id())->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string',
            'culture' => 'required|string',
            'superficie' => 'required|numeric',
            'stade' => 'nullable|string',
            'datePlantation' => 'nullable|date',
            'typeSol' => 'nullable|string',
            'localisation' => 'nullable|string',
            'statut' => 'nullable|string'
        ]);

        $parcelle = Parcelle::create([
            'proprietaire_id' => auth()->id(),
            'nom' => $request->nom,
            'culture' => $request->culture,
            'superficie' => $request->superficie,
            'stade' => $request->stade,
            'datePlantation' => $request->datePlantation,
            'typeSol' => $request->typeSol,
            'localisation' => $request->localisation,
            'statut' => $request->statut ?? 'actif'
        ]);

        return response()->json($parcelle, 201);
    }

    public function show($id)
    {
        $parcelle = Parcelle::where('proprietaire_id', auth()->id())->findOrFail($id);
        return response()->json($parcelle);
    }

    public function update(Request $request, $id)
    {
        $parcelle = Parcelle::where('proprietaire_id', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'nom' => 'sometimes|string',
            'culture' => 'sometimes|string',
            'superficie' => 'sometimes|numeric',
            'stade' => 'nullable|string',
            'datePlantation' => 'nullable|date',
            'typeSol' => 'nullable|string',
            'localisation' => 'nullable|string',
            'statut' => 'nullable|string'
        ]);

        $parcelle->update($request->all());

        return response()->json($parcelle);
    }

    public function destroy($id)
    {
        $parcelle = Parcelle::where('proprietaire_id', auth()->id())->findOrFail($id);
        $parcelle->delete();

        return response()->json(null, 204);
    }
}

