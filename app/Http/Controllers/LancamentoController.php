<?php

namespace App\Http\Controllers;

use App\Services\LancamentoService;

use App\Models\Lancamento;
use App\Models\Tipo;
use App\Models\Subtipo;
use App\Models\Codigo;
use App\Models\UnidadeGestora;
use App\Models\Secretaria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class LancamentoController extends Controller
{

    protected $lancamentoService;

    public function __construct(LancamentoService $service)
    {
        $this->lancamentoService = $service;
    }


    public function index(Request $request)
    {
        Log::info('Controller@index - Listando lançamentos com filtros aplicados');
        $query = Lancamento::with('secretaria');
        $query = $this->aplicarFiltros($query, $request);

        // Aplica ordenação e paginação
        $lancamentos = $query->orderBy('id', 'desc')->get();

        // Dados para preencher os filtros
        $unidades = UnidadeGestora::where('status', true)
            ->whereHas('secretarias')
            ->orderBy('nome', 'asc')
            ->get();
        $tipos = Tipo::all();
        $secretarias = Secretaria::all();
        $subtipos = \App\Models\Subtipo::all();
        $codigos = \App\Models\Codigo::all();

        return view('admin.lancamentos.index', compact(
            'lancamentos',
            'tipos',
            'unidades',
            'secretarias',
            'subtipos',
            'codigos'
        ));
    }

    public function show($id)
    {
        Log::info('Controller@show, busca de dados do lançamento: ' . $id);
        try {
            $lancamento = Lancamento::with(['secretaria.unidadeGestora', 'codigo.subtipo.tipo', 'user'])
                ->findOrFail($id);
            $lancamento->valor_formatado = $lancamento->getValorFormatadoAttribute();
            $lancamento->status_formatado = ucfirst($lancamento->getStatus());
            return response()->json($lancamento);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar lançamento: ' . $e->getMessage());
            return response()->json(['error' => 'Lançamento não encontrado ou ocorreu um erro.'], 500);
        }
    }

    private function aplicarFiltros($query, $request)
    {
        Log::info('Controlelr@aplicarFiltros - Aplicando filtros na consulta de lançamentos');
        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filtro por secretaria
        if ($request->filled('secretaria')) {
            $query->where('secretaria_id', $request->input('secretaria'));
        }

        // Filtro por entidade (Unidade Gestora)
        if ($request->filled('unidade')) {
            $query->whereHas('secretaria.unidadeGestora', function ($q) use ($request) {
                $q->where('id', $request->input('unidade'));
            });
        }

        // Filtro por processo
        if ($request->filled('processo')) {
            $query->where('processo', 'like', '%' . $request->input('processo') . '%');
        }

        // Filtro por código
        if ($request->filled('codigo')) {
            $query->whereHas('codigo', function ($q) use ($request) {
                $q->where('codigo', 'like', '%' . $request->input('codigo') . '%');
            });
        }

        return $query;
    }

    public function getSubtiposByTipo($tipo_id)
    {
        Log::info('Controller@getSubtiposByTipo Buscando subtipos para o tipo: ' . $tipo_id);
        $subtipos = Subtipo::where('tipo_id', $tipo_id)->orderBy('nome', 'asc')->get();
        return response()->json($subtipos);
    }

    public function searchCnaes(Request $request)
    {
        Log::info('Controller@searchCnaes buscando CNAEs com query: ' . $request->input('search') . ' e subtipo_id: ' . $request->input('subtipo_id'));
        $query = $request->input('search');
        $subtipo_id = $request->input('subtipo_id');

        $cnaes = Codigo::where(function ($q) use ($query) {
            $q->where('codigo', 'ilike', "%{$query}%")
                ->orWhere('nome', 'ilike', "%{$query}%");
        });

        if ($subtipo_id) {
            $cnaes->where('subtipo_id', $subtipo_id);
        }

        return response()->json($cnaes->limit(15)->get());
    }

    public function store(Request $request)
    {
        Log::info('Controller@store com dados: ' . json_encode($request->all()));
        $request->validate([
            'valor' => 'required|numeric',
            'secretaria_id' => 'required|exists:secretarias,id',
            'codigo_id' => 'required|exists:codigos,id',
            'unidade_id' => 'required|exists:unidades,id',
            'status' => 'required|in:ativo,cancelado,reservado',
        ]);

        try {
            $dados = $request->only([
                'valor',
                'secretaria_id',
                'codigo_id',
                'unidade_id',
                'status',
                'objeto',
                'processo'
            ]);
            $dados['user_id'] = auth()->id();

            $this->lancamentoService->criarLancamento($dados);

            return redirect()->route('lancamentos.index')->with('success', 'Lançamento criado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao cadastrar lançamento: ' . $e->getMessage());
            return redirect()->route('lancamentos.index')->with('error', 'Erro ao cadastrar lançamento.');
        }
    }

    public function edit($id)
    {
        Log::info('entrou em Controller@edit, busca de dados do lançamento: ' . $id);
        $lancamento = Lancamento::with([
            'codigo.subtipo.tipo',
            'secretaria.unidadeGestora'
        ])->findOrFail($id);

        return response()->json([
            'dados' => $lancamento
        ]);
    }

    public function update(Request $request, $id)
    {
        Log::info('entrou em Controller@update, id do lançamento: ' . $id);

        $request->validate([
            'valor' => 'required|numeric',
            'secretaria_id' => 'required|exists:secretarias,id',
            'codigo_id' => 'required|exists:codigos,id',
            'unidade_id' => 'required|exists:unidades,id',
            'status' => 'required|in:ativo,cancelado,reservado',
        ]);

        try {
            $dados = $request->only([
                'valor',
                'secretaria_id',
                'codigo_id',
                'unidade_id',
                'status',
                'objeto',
                'processo'
            ]);
            $dados['user_id'] = auth()->id();

            $this->lancamentoService->atualizarLancamento($dados, $id);

            Log::info('Lançamento atualizado com sucesso: ' . $id);

            return redirect()->route('lancamentos.index')->with('success', 'Lançamento atualizado com sucesso!');
        } catch (QueryException $e) {
            Log::error('Erro ao atualizar lançamento: ' . $e->getMessage());
            return redirect()->route('lancamentos.index')->with('error', 'Erro ao atualizar lançamento. Verifique os dados e tente novamente.');
        }
    }

    public function updateStatus(Request $request, $id)
    {
        Log::info('entrou em Controller@updateStatus, id do lançamento: ' . $id);
        $lancamento = Lancamento::findOrFail($id);

        if ($request->status === 'ativo' && $lancamento->status === 'reservado') {
            $lancamento->status = 'ativo';
            $lancamento->save();

            return redirect()->back()->with('success', 'Status atualizado com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foi possível atualizar o status.');
    }

    public function verificarSaldo(Request $request)
    {
        Log::info('entrou em Controller@verificarSaldo');
        $request->validate([
            'unidade_id' => 'required|exists:unidades,id',
            'codigo_id' => 'required|exists:codigos,id',
        ]);

        try {
            $saldo = $this->lancamentoService->verificarOuCriarSaldo($request->unidade_id, $request->codigo_id);

            return response()->json(['saldo' => $saldo->saldo]);
        } catch (\Exception $e) {
            Log::error('Erro ao verificar ou criar saldo: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao verificar saldo.'], 500);
        }
    }
}
