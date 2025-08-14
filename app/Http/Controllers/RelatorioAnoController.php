<?php

namespace App\Http\Controllers;

use App\Models\Lancamento;
use App\Models\Secretaria;
use App\Models\UnidadeGestora;
use App\Models\Valor;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class RelatorioAnoController extends Controller
{
    public function indexAnual()
    {
        $mensagem = null;
        $secretarias = Secretaria::orderBy('nome', 'asc')->get();
        $unidades = UnidadeGestora::orderBy('nome', 'asc')->where('status', true)->get();
        $anos = Valor::select('ano')->orderBy('ano', 'desc')->distinct()->get();
        return view('admin.relatorios.anual', compact('secretarias', 'unidades', 'anos', 'mensagem'));
    }

    private function processarFiltroAnual($validated)
    {
        $lancamentos = Lancamento::with(['codigo', 'secretaria.unidadeGestora'])
            ->when(!empty($validated['unidade']), function ($query) use ($validated) {
                $query->whereHas('secretaria', function ($query) use ($validated) {
                    $query->where('unidade_id', $validated['unidade']);
                });
            })
            ->whereYear('created_at', $validated['ano'])
            ->when(!empty($validated['secretaria']), function ($query) use ($validated) {
                $query->where('secretaria_id', $validated['secretaria']);
            })
            ->when(!empty($validated['status']), function ($query) use ($validated) {
                $query->where('status', $validated['status']);
            })->orderBy('processo', 'asc')
            ->get();

        $soma = $lancamentos->sum('valor');
        $total = 'R$ ' . number_format($soma, 2, ',', '.');

        return compact('lancamentos', 'soma', 'total');
    }


    public function filtroAnual(Request $request)
    {
        $validated = $request->validate([
            'ano' => 'required|integer|min:2000|max:' . date('Y'),
            'unidade' => 'nullable|exists:unidades,id',
            'secretaria' => 'nullable|exists:secretarias,id',
            'status' => 'nullable|in:ativo,cancelado,reservado',
        ]);

        $dados = $this->processarFiltroAnual($validated);
        extract($dados);

        $mensagem = $lancamentos->isEmpty()
            ? 'Nenhum lançamento foi encontrado para os filtros aplicados.'
            : null;

        $unidades = UnidadeGestora::orderBy('nome', 'asc')->where('status', true)->get();
        $secretarias = Secretaria::orderBy('nome', 'asc')->get();
        $anos = Valor::select('ano')->orderBy('ano', 'asc')->distinct()->get();

        $request->session()->put('filtro_anual', $validated);

        return view('admin.relatorios.anual', compact('lancamentos', 'unidades', 'secretarias', 'anos', 'mensagem', 'total'));
    }

    public function exportarRelatorioPdf(Request $request)
    {
        $validated = $request->session()->get('filtro_anual');
        if (!$validated) {
            return redirect()->back()->with('error', 'Filtros não definidos.');
        }

        $dados = $this->processarFiltroAnual($validated);
        extract($dados);

        $pdf = Pdf::loadView('admin.relatorios.pdfAnual', compact('lancamentos', 'total', 'validated'))
            ->setPaper('a4', 'landscape');

        $pdf->output();
        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->get_canvas();
        $canvas->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) {
            $font = $fontMetrics->get_font('Arial', 'lighter');
            $size = 8;

            // Texto à esquerda: Relatório e usuário
            $leftText = "Relatório de Lançamentos Anuais | Gerado por: " . (auth()->user()->name);
            $canvas->text(40, 570, $leftText, $font, $size);

            // Texto à direita: Paginação
            $rightText = "Página $pageNumber de $pageCount";
            $textWidth = $fontMetrics->get_text_width($rightText, $font, $size);
            $canvas->text(800 - $textWidth, 570, $rightText, $font, $size);
        });

        return $pdf->download('relatorio_anual.pdf');
    }
}
