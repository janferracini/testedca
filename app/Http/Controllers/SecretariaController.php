<?php

namespace App\Http\Controllers;

use App\Models\Secretaria;
use App\Models\UnidadeGestora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SecretariaController extends Controller
{

    // index
    public function index()
    {
        $secretarias = Secretaria::with('unidadeGestora')->paginate(10);
        $unidades = UnidadeGestora::where('status', 1)->orderBy('nome', 'asc')->get();

        return view('admin.secretarias.index', compact('secretarias', 'unidades'));
    }

    public function updateStatus(Request $request, $id)
    {
        $secretaria = Secretaria::findOrFail($id);
        $newStatus = !$secretaria->status;
        $secretaria->status = $newStatus;
        $secretaria->save();

        $message = $newStatus ? 'Secretaria ativada com sucesso!' : 'Secretaria inativada com sucesso!';
        return redirect()->route('secretaria.index')->with('success', $message);
    }

    public function store(Request $request)
    {
        $data = [
            'id' => $request->input('id'),
            'nome' => $request->input('nome'),
            'status' => $request->input('status'),
            'unidade_id' => $request->input('unidade_id')
        ];

        $request->validate([
            'nome' => 'required',
            'status' => 'required',
            'unidade_id' => 'required'
        ]);

        $verify_nome_secretaria = Secretaria::where('nome', $data['nome'])
            ->where('unidade_id', $data['unidade_id'])
            ->first();

        if (!empty($verify_nome_secretaria)) {
            return redirect()->route('secretaria.index')
                ->with('error', 'Erro ao cadastrar Secretaria. Nome já cadastrado na Unidade Gestora selecionada');
        }

        try {
            if ($data['id'] != null) {
                Secretaria::find($data['id'])->update($data);
                return redirect()->route('secretaria.index')->with('success', 'Secretaria atualizada com sucesso!');
            } else {
                Secretaria::create($data);
                return redirect()->route('secretaria.index')->with('success', 'Secretaria cadastrada com sucesso!');
            }
        } catch (\Exception $e) {
            Log::error('Erro ao cadastrar Secretaria: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao cadastrar ou editar Secretaria, verifique os dados e caso o erro persista entre em contato com a Divisão de Desenvolvimento.');
        }
    }

    public function getSecretarias($id)
    {
        $secretarias = Secretaria::where('unidade_id', $id)->get();

        return response()->json($secretarias);
    }
}
