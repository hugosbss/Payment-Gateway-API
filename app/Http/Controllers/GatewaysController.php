<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateGatewayPriorityRequest;
use App\Models\Gateway;

class GatewaysController extends Controller
{
    public function activate(Gateway $gateway)
    {
        $gateway->update(['is_active' => true]);

        return response()->json([
            'message' => 'Gateway ativado com sucesso',
            'data' => $gateway,
        ]);
    }

    public function deactivate(Gateway $gateway)
    {
        $gateway->update(['is_active' => false]);

        return response()->json([
            'message' => 'Gateway desativado com sucesso',
            'data' => $gateway,
        ]);
    }

    public function priority(UpdateGatewayPriorityRequest $request, Gateway $gateway)
    {
        $gateway->update($request->validated());

        return response()->json([
            'message' => 'Prioridade do gateway atualizada com sucesso',
            'data' => $gateway,
        ]);
    }
}
