<?php

namespace App\Http\Controllers;

use App\Models\Saldo;
use App\Models\UnidadeGestora;
use App\Models\Codigo;
use Illuminate\Http\Request;

class SaldoController extends Controller
{

    public function index()
    {
        $unidades = UnidadeGestora::all();
        $codigos = Codigo::all();
        return view('admin.saldos.index', compact('unidades', 'codigos'));
    }

    public function showSaldo(Request $request)
    {
        $request->validate([
            'unidade_id' => 'required',
            'codigo_id' => 'required',
        ]);

        // Buscar a entidade e o CNAE selecionados
        $unidade_busca = UnidadeGestora::where('id', $request->unidade_id)->first();
        $codigoCnae = Codigo::where('id', $request->codigo_id)->first();


        if (!$unidade_busca || !$codigoCnae) {
            $message = 'Entidade ou Código CNAE não encontrados.';
            $unidades = UnidadeGestora::all();
            $codigos = Codigo::all();
            return view('admin.saldos.index', compact('unidades', 'codigos', 'message'));
        }

        // Buscar o saldo para a combinação selecionada
        $saldo = Saldo::where('unidades_id', $request->unidade_id)
            ->where('codigos_id', $request->codigo_id)
            ->where('ano', now()->year)
            ->first();

        if (!$saldo) {
            $message = 'Saldo não encontrado.';
            $unidades = UnidadeGestora::all();
            $codigos = Codigo::all();
            return view('admin.saldos.index', compact('unidades', 'codigos', 'message'));
        }

        // Passar os dados para a view
        $unidades = UnidadeGestora::all();
        $codigos = Codigo::all();
        return view('admin.saldos.index', compact('saldo', 'unidade_busca', 'codigoCnae', 'unidades', 'codigos'));
    }

    public function buscarCnae(Request $request)
    {
        $search = $request->input('q');

        // Busca os códigos CNAE filtrados por código ou nome
        $cnaes = Codigo::where('codigo', 'like', "%$search%")
            ->orWhere('nome', 'like', "%$search%")
            ->limit(10)
            ->get();

        $results = $cnaes->map(function ($cnae) {
            return [
                'id' => $cnae->id,
                'text' => "{$cnae->codigo} - {$cnae->nome}",
            ];
        });

        return response()->json($results);
    }
}
