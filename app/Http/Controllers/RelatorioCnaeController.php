<?php

namespace App\Http\Controllers;

use App\Models\Lancamento;
use App\Models\Secretaria;
use App\Models\UnidadeGestora;
use App\Models\Valor;
use App\Models\Tipo;
use App\Models\Subtipo;
use App\Models\Codigo;
use App\Models\Saldo;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

use function Laravel\Prompts\error;

class RelatorioCnaeController extends Controller
{
    public function indexCnae()
    {
        $mensagem = null;
        $secretarias = Secretaria::orderBy('nome', 'asc')->get();
        $unidades = UnidadeGestora::orderBy('nome', 'asc')->where('status', true)->get();
        $anos = Valor::select('ano')->orderBy('ano', 'desc')->distinct()->get();
        $tipos = Tipo::all();
        return view('admin.relatorios.cnae', compact('secretarias', 'unidades', 'anos', 'mensagem', 'tipos'));
    }

    public function processarFiltroCnae(array $validated)
    {
        // Inicia a consulta com o modelo Lancamento
        $query = Lancamento::query();

        // Filtrando por ano, se fornecido
        if (!empty($validated['ano'])) {
            $query->whereYear('created_at', $validated['ano']);
        }

        // Filtrando por unidade, se fornecido
        if (!empty($validated['unidade_id'])) {
            $query->whereHas('secretaria', function ($q) use ($validated) {
                $q->where('unidade_id', $validated['unidade_id']);
            });
        }

        // Filtrando por secretaria, se fornecido
        if (!empty($validated['secretaria_id'])) {
            $query->where('secretaria_id', $validated['secretaria_id']);
        }

        // Filtrando por status, se fornecido
        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        // Filtrando por código CNAE, se fornecido
        if (!empty($validated['codigo_id'])) {
            $query->where('codigo_id', $validated['codigo_id']);
        }

        // Adicione outras verificações para tipos e subtipo, se necessário

        // Executa a consulta e obtém os resultados
        $lancamentos = $query->get();
        $total = $lancamentos->sum('valor'); // Calcula o total de valores

        return compact('lancamentos', 'total');
    }



    public function filtroCnae(Request $request)
    {
        $validated = $request->validate([
            'ano' => 'nullable|integer|min:2000|max:' . date('Y'),
            'unidade_id' => 'nullable|exists:unidades,id',
            'secretaria_id' => 'nullable|exists:secretarias,id',
            'status' => 'nullable|in:ativo,cancelado,reservado',
            'tipo_id' => 'nullable|exists:tipos,id',
            'subtipo_id' => 'nullable|exists:subtipos,id',
            'codigo_id' => 'nullable|exists:codigos,id',
        ]);

        $dados = $this->processarFiltroCnae($validated);
        extract($dados);

        $lancamentos = $dados['lancamentos'] ?? collect();
        $total = $dados['total'] ?? 0;

        $nomesRelacionados = $this->obterNomesRelacionados($validated);
        extract($nomesRelacionados);

        $mensagem = $lancamentos->isEmpty()
            ? 'Nenhum lançamento foi encontrado para os filtros aplicados.'
            : null;

        $unidades = UnidadeGestora::orderBy('nome', 'asc')->where('status', true)->get();
        $secretarias = Secretaria::orderBy('nome', 'asc')->get();
        $anos = Lancamento::selectRaw('DISTINCT EXTRACT(YEAR FROM created_at) as ano')
            ->orderBy('ano', 'desc')
            ->get();
        $tipos = Tipo::orderBy('nome', 'asc')->get();

        $request->session()->put('filtro_cnae', $validated);

        return view('admin.relatorios.cnae', compact(
            'lancamentos',
            'unidades',
            'secretarias',
            'anos',
            'tipos',
            'mensagem',
            'total',
            'tipoNome',
            'subtipoNome',
            'codigoCnae',
            'nomeCnae',
        ));
    }
    private function obterNomesRelacionados(array $validated)
    {
        $tipoNome = null;
        $subtipoNome = null;
        $codigoCnae = null;
        $nomeCnae = null;

        if (!empty($validated['tipo_id'])) {
            $tipo = Tipo::find($validated['tipo_id']);
            $tipoNome = $tipo ? $tipo->nome : null;
        }

        if (!empty($validated['subtipo_id'])) {
            $subtipo = Subtipo::find($validated['subtipo_id']);
            $subtipoNome = $subtipo ? $subtipo->nome : null;
        }

        if (!empty($validated['codigo_id'])) {
            $codigo = Codigo::find($validated['codigo_id']);
            $codigoCnae = $codigo ? $codigo->codigo : null;
            $nomeCnae = $codigo ? $codigo->nome : null;
        }

        return compact('tipoNome', 'subtipoNome', 'codigoCnae', 'nomeCnae');
    }

    public function exportarRelatorioPdf(Request $request)
    {
        $validated = $request->session()->get('filtro_cnae');
        if (!$validated) {
            return redirect()->back()->with('error', 'Filtros não definidos.');
        }

        $dados = $this->processarFiltroCnae($validated);
        extract($dados);

        $nomesRelacionados = $this->obterNomesRelacionados($validated);
        extract($nomesRelacionados);

        $pdf = Pdf::loadView('admin.relatorios.pdfCnae', compact(
            'lancamentos',
            'total',
            'validated',
            'tipoNome',
            'subtipoNome',
            'codigoCnae',
            'nomeCnae'
        ))->setPaper('a4', 'landscape');

        $pdf->output();
        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->get_canvas();
        $canvas->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) {
            $font = $fontMetrics->get_font('Arial', 'lighter');
            $size = 8;

            $leftText = "Relatório por CNAE | Gerado por: " . (auth()->user()->name);
            // landscape
            $canvas->text(40, 570, $leftText, $font, $size);
            // portrait
            // $canvas->text(40, 800, $leftText, $font, $size);

            $rightText = "Página $pageNumber de $pageCount";
            $textWidth = $fontMetrics->get_text_width($rightText, $font, $size);
            $canvas->text(800 - $textWidth, 570, $rightText, $font, $size);
        });

        return $pdf->download('relatorio_cnae.pdf');
    }
}
