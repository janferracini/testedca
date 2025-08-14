<?php

namespace App\Http\Controllers;

use App\Models\UnidadeGestora;
use App\Models\Secretaria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UnidadeGestoraController extends Controller
{
    // index
    public function index()
    {
        $unidades = UnidadeGestora::orderBy('nome', 'asc')->paginate(10);
        return view('admin.unidades_gestoras.index', compact('unidades'));
    }

    public function updateStatus(Request $request, $id)
    {
        // Busca a Unidade Gestora pelo ID
        $ug = UnidadeGestora::findOrFail($id);

        // Define o novo status como o oposto do atual
        $newStatus = !$ug->status;
        $ug->status = $newStatus;
        $ug->save();

        // Atualiza o status de todas as secretarias vinculadas à Unidade Gestora
        Secretaria::where('unidade_id', $ug->id)->update(['status' => $newStatus]);

        // Define a mensagem de sucesso
        $message = $newStatus ? 'Unidade Gestora ativada e secretarias vinculadas atualizadas com sucesso!' : 'Unidade Gestora inativada e secretarias vinculadas inativadas com sucesso!';

        // Redireciona para o índice com uma mensagem de sucesso
        return redirect()->route('unidade_gestora.index')->with('success', $message);
    }


    public function store(Request $request)
    {
        $data = [
            'id' => $request->input('id'),
            'nome' => $request->input('nome'),
            'status' => $request->input('status')
        ];

        $request->validate([
            'nome' => 'required',
            'status' => 'required',
        ]);

        $verify_nome = UnidadeGestora::where('nome', $data['nome'])->first();

        if (!empty($verify_nome) && $verify_nome->id != $data['id']) {
            return redirect()->route('unidade_gestora.index')->with('error', 'Erro ao cadastrar Unidade Gestora. Nome já cadastrado');
        }

        try {
            if ($data['id'] != null) {
                UnidadeGestora::find($data['id'])->update($data);
                return redirect()->route('unidade_gestora.index')->with('success', 'Unidade Gestora atualizada com sucesso!');
            } else {
                UnidadeGestora::create($data);
                return redirect()->route('unidade_gestora.index')->with('success', 'Unidade Gestora cadastrada com sucesso!');
            }
        } catch (\Exception $e) {
            Log::error('Erro ao cadastrar Unidade Gestora: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao cadastrar ou editar Unidade Gestora, verifique os dados e caso o erro persista entre em contato com a Divisão de Desenvolvimento.');
        }
    }

    public function secretarias()
    {
        return $this->hasMany(Secretaria::class, 'unidade_id');
    }
}
