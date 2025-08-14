@extends('adminlte::page')

@section('title', 'Lançamento Anual')

@section('content_header')
    <h1>Lançamento Anual</h1>
@endsection

@section('content')
    <form method="POST" action="{{ route('filtro.anual') }}" id='filtro-form' class="mb-4">
        @csrf
        <div class="row">
            <div class="col-md-2">
                <label for="ano">Ano</label>
                <select name="ano" id="ano" class="form-control">
                    @foreach ($anos as $ano)
                        <option value="{{ $ano->ano }}" {{ request('ano', date('Y')) == $ano->ano ? 'selected' : '' }}>
                            {{ $ano->ano }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="unidade">Unidade Gestora</label>
                <select name="unidade" id="unidade" class="form-control">
                    <option value="">Todas</option>
                    @foreach ($unidades as $unidade)
                        <option value="{{ $unidade->id }}" {{ request('unidade') == $unidade->id ? 'selected' : '' }}>
                            {{ $unidade->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="secretaria">Secretaria</label>
                <select name="secretaria" id="secretaria" class="form-control">
                    <option value="">Todas</option>
                    @foreach ($secretarias as $secretaria)
                        <option value="{{ $secretaria->id }}"
                            {{ request('secretaria') == $secretaria->id ? 'selected' : '' }}>
                            {{ $secretaria->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="status">Status</label>
                <select name="status" id="status" class="form-control">
                    <option value="">Todos</option>
                    <option value="ativo" {{ request('status') == 'ativo' ? 'selected' : '' }}>Ativo</option>
                    <option value="cancelado" {{ request('status') == 'cancelado' ? 'selected' : '' }}>Cancelado
                    </option>
                    <option value="reservado" {{ request('status') == 'reservado' ? 'selected' : '' }}>Reservado
                    </option>
                </select>
            </div>
        </div>
        <div class="mt-3">
            <button type="submit" class="btn btn-primary" id="btn-filtrar">Filtrar</button>
            <button type="button" id="limpar-filtro" class="btn btn-secondary">Limpar</button>
        </div>
    </form>
    @if (!empty($mensagem))
        <div>
            {{ $mensagem }}
        </div>
    @endif
    <div id="tabela-resultados"
        style="display: {{ empty($lancamentos) || count($lancamentos) === 0 ? 'none' : 'block' }};">
        @if (!empty($lancamentos) && count($lancamentos) > 0)
            <div class="mt-4 mb-2 float-right">
                {{-- <button class="btn btn-sm btn-success" id="exportar-xlsx">Exportar .xlsx</button> --}}
                <form method="GET" action="{{ route('relatorio.exportar.anual') }}" id="pdf-form">
                    @csrf
                    <input type="hidden" name="ano" id="hidden-ano">
                    <input type="hidden" name="unidade" id="hidden-unidade">
                    <input type="hidden" name="secretaria" id="hidden-secretaria">
                    <input type="hidden" name="status" id="hidden-status">
                    <a href="{{ route('relatorio.exportar.anual') }}" class="btn btn-sm btn-outline-danger"
                        onclick="event.preventDefault(); document.getElementById('pdf-form').submit();"><i
                            class="far fa-file-pdf"></i></a>
                    <a href="#" class="btn btn-sm btn-outline-success" id="exportar-xlsx"><i
                            class="far fa-file-excel"></i></a>
                </form>
            </div>
            <table class="table table-sm table-hover mt-3">
                <thead>
                    <tr>
                        <th style="width: 10%">Processo</th>
                        <th style="width: 10%">Data</th>
                        <th style="max-width: 12%">Local</th>
                        <th style="min-width: 20%">Objeto</th>
                        <th style="width: 10%">Código</th>
                        <th>Descrição</th>
                        <th style="width: 12%">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lancamentos as $lancamento)
                        <tr>
                            <td>{{ $lancamento->processo }}</td>
                            <td>{{ $lancamento->created_at->format('d/m/Y') }}</td>
                            <td>{{ $lancamento->secretaria->unidadeGestora->nome }} - {{ $lancamento->secretaria->nome }}
                            </td>
                            <td>{{ $lancamento->objeto }}</td>
                            <td>{{ $lancamento->codigo->codigo }}</td>
                            <td>{{ $lancamento->codigo->nome }}</td>
                            <td>{{ $lancamento->valor_formatado }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6"> </td>
                        <td>
                            <b>{{ $total }}</b>
                        </td>
                    </tr>
                </tfoot>
            </table>

        @endif
    </div>

@endsection
@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const limparBotao = document.getElementById('limpar-filtro');
            const tabelaResultados = document.getElementById('tabela-resultados');
            limparBotao.addEventListener('click', function() {
                const formulario = document.getElementById('filtro-form');
                const anoAtual = new Date().getFullYear();
                formulario.querySelectorAll('select').forEach(input => {
                    if (input.tagName === 'SELECT') {
                        if (input.name === 'ano') {
                            Array.from(input.options).forEach(option => {
                                option.selected = option.value == anoAtual;
                            });
                        } else {
                            // Reseta os outros selects
                            input.selectedIndex = 0;
                        }
                    } else {
                        // Limpa os outros campos
                        input.value = '';
                    }
                });
                tabelaResultados.style.display = 'none'; // Oculta a tabela
            });

            // Exibe a tabela apenas se ela tiver resultados
            if (tabelaResultados && tabelaResultados.innerHTML.trim() !== '') {
                tabelaResultados.style.display = 'block';
            }
        });
    </script>
@endsection
