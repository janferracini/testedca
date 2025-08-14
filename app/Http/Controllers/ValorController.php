<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Valor;
use Illuminate\Support\Facades\Log;

class ValorController extends Controller
{
    public function index()
    {
        $valores = Valor::orderBy('ano', 'desc')->paginate(10);
        return view('admin.valores-anuais.index', compact('valores'));
    }

    public function updateStatus(Request $request, $id)
    {
        $valor = Valor::findOrFail($id);

        $newStatus = !$valor->status;
        $valor->status = $newStatus;
        $valor->save();

        $message = $newStatus ? 'Valor Anual ativado com sucesso!' : 'Valor Anual inativado com sucesso!';

        return redirect()->route('valores.index')->with('success', $message);
    }

    public function store(Request $request)
    {
        $data = [
            'id' => $request->input('id'),
            'ano' => $request->input('ano'),
            'valor' => str_replace(',', '.', $request->input('valor')),
            'status' => $request->input('status')
        ];

        $request->validate([
            'ano' => 'required',
            'valor' => 'required|regex:/^\d+.\d{2}$/',
            'status' => 'required',
        ]);

        $verify_ano = Valor::where('ano', $data['ano'])->first();

        if (!empty($verify_ano) && $verify_ano->id != $data['id']) {
            return redirect()->route('valores.index')->with('error', 'Erro ao cadastrar Valor Anual. Ano já cadastrado');
        }

        try {
            if (!empty($data['id'])) {
                $valor = Valor::find($data['id']);
                if ($valor) {
                    $valor->update($data);
                    return redirect()->route('valores.index')->with('success', 'Valor Anual atualizado com sucesso!');
                } else {
                    return redirect()->route('valores.index')->with('error', 'Valor não encontrado.');
                }
            } else {
                Valor::create($data);
                return redirect()->route('valores.index')->with('success', 'Valor Anual cadastrado com sucesso!');
            }
        } catch (\Exception $e) {
            Log::error('Erro ao cadastrar Valor Anual: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao cadastrar ou editar Valor Anual, verifique os dados e caso o erro persista entre em contato com a Divisão de Desenvolvimento.');
        }
    }
}
