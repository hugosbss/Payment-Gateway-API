<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::query()->get();

        return response()->json([
            'message' => 'Usuários encontrados',
            'data' => $users,
        ]);
    }

    public function show(User $user)
    {
        return response()->json([
            'message' => 'Usuário encontrado',
            'data' => $user,
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->validated());

        return response()->json([
            'message' => 'Usuário criado com sucesso',
            'data' => $user,
        ], 201);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();
        $user->update($data);

        return response()->json([
            'message' => 'Usuário atualizado com sucesso',
            'data' => $user,
        ]);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'message' => 'Usuário deletado com sucesso',
        ]);
    }
}
