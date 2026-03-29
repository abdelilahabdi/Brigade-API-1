<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'message' => 'Profil alimentaire recupere avec succes.',
            'profile' => $this->profilePayload($user),
        ]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->forceFill([
            'dietary_tags' => $request->validated('dietary_tags'),
        ])->save();

        $user->refresh();

        return response()->json([
            'message' => 'Profil alimentaire mis a jour avec succes.',
            'profile' => $this->profilePayload($user),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function profilePayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'dietary_tags' => $user->dietary_tags ?? [],
        ];
    }
}
