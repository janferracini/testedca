<?php

namespace App\Http\Controllers;

use App\Models\Tipo;

class TipoController extends Controller
{
    public function getTipos()
    {
        $tipos = Tipo::all(); // Isso pega todos os registros da tabela tipos
        return response()->json($tipos); // Retorna os tipos em formato JSON
    }
}
