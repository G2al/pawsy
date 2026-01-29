<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PetController extends Controller
{
    public function store(Request $request)
    {
        $data = $this->validatePet($request);
        $data['user_id'] = $request->user()->id;

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('pets', 'public');
        }

        $pet = Pet::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Animale creato con successo',
            'pet' => $pet,
        ], 201);
    }

    public function update(Request $request, Pet $pet)
    {
        if ($pet->user_id !== $request->user()->id) {
            abort(404);
        }

        $data = $this->validatePet($request);

        if ($request->hasFile('photo')) {
            if ($pet->photo) {
                Storage::disk('public')->delete($pet->photo);
            }
            $data['photo'] = $request->file('photo')->store('pets', 'public');
        }

        $pet->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Animale aggiornato con successo',
            'pet' => $pet,
        ]);
    }

    public function destroy(Request $request, Pet $pet)
    {
        if ($pet->user_id !== $request->user()->id) {
            abort(404);
        }

        if ($pet->photo) {
            Storage::disk('public')->delete($pet->photo);
        }

        $pet->delete();

        return response()->json([
            'success' => true,
            'message' => 'Animale eliminato con successo',
        ]);
    }

    private function validatePet(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'sex' => ['nullable', Rule::in(['maschio', 'femmina', 'non_specificato'])],
            'breed' => 'nullable|string|max:255',
            'size' => ['nullable', Rule::in(['piccola', 'media', 'grande'])],
            'birth_date' => 'nullable|date',
            'photo' => 'nullable|image|max:2048',
            'notes' => 'nullable|string|max:2000',
        ]);
    }
}
