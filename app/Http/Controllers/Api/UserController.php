<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function supervisors(): JsonResponse
    {
        return $this->ok(User::query()->where('role', User::ROLE_SUPERVISOR)->latest()->get());
    }

    public function operators(): JsonResponse
    {
        return $this->ok(
            User::query()
                ->where('role', User::ROLE_OPERATOR)
                ->where('supervisor_id', request()->user()->id)
                ->latest()
                ->get()
        );
    }

    public function storeSupervisor(UserRequest $request): JsonResponse
    {
        $user = User::query()->create($request->safe()->merge([
            'role' => User::ROLE_SUPERVISOR,
        ])->all());

        return $this->created($user);
    }

    public function storeOperator(UserRequest $request): JsonResponse
    {
        $user = User::query()->create($request->safe()->merge([
            'role' => User::ROLE_OPERATOR,
            'supervisor_id' => $request->user()->id,
        ])->all());

        return $this->created($user);
    }

    public function show(User $user): JsonResponse
    {
        $this->authorizeUserAccess($user);

        return $this->ok($user);
    }

    public function update(UserRequest $request, User $user): JsonResponse
    {
        $this->authorizeUserAccess($user);

        $data = $request->validated();

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        return $this->ok($user->refresh());
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorizeUserAccess($user);
        $user->delete();

        return response()->json(null, 204);
    }

    private function authorizeUserAccess(User $user): void
    {
        $actor = request()->user();

        abort_if($actor->role === User::ROLE_ADMIN && $user->role !== User::ROLE_SUPERVISOR, 403);
        abort_if(
            $actor->role === User::ROLE_SUPERVISOR
            && ($user->role !== User::ROLE_OPERATOR || $user->supervisor_id !== $actor->id),
            403
        );
    }

    private function ok(mixed $data): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $data]);
    }

    private function created(mixed $data): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $data], 201);
    }
}
