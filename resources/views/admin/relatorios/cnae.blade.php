@extends('adminlte::page')


@section('title', 'Relatório de Lançamentos por CNAE')

@section('content_header')
    <h1>Relatório de Lançamentos por CNAE</h1>
@endsection

@section('content')
    <form method="POST" action="{{ route('filtro.cnae') }}" id='filtro-form' class="mb-4">
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
                <label for="unidade_id">Unidade Gestora</label>
                <select name="unidade_id" id="unidade_id" class="form-control">
                    <option value="">Todas</option>
                    @foreach ($unidades as $unidade)
                        <option value="{{ $unidade->id }}" {{ request('unidade_id') == $unidade->id ? 'selected' : '' }}>
                            {{ $unidade->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="secretaria_id">Secretaria</label>
                <select name="secretaria_id" id="secretaria_id" class="form-control" disabled>
                    <option value="">Selecione a Secretaria</option>
                    <!-- As opções serão carregadas via AJAX -->
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
        <div class="row">
            <div class="col col-md-3">
                <label for="tipo_id">Tipo</label>
                <select name="tipo_id" id="tipo_id" class="form-control" required>
                    <option value="">Selecione o Tipo</option>
                    @foreach ($tipos as $tipo)
                        <option value="{{ $tipo->id }}" {{ request('tipo_id') == $tipo->id ? 'selected' : '' }}>
                            {{ $tipo->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col col-md-3">
                <label for="subtipo_id">Subtipo</label>
                <select name="subtipo_id" id="subtipo_id" class="form-control" required disabled>
                    <option value="">Selecione o Subtipo</option>
                    <!-- As opções serão carregadas via AJAX -->
                </select>
            </div>
            <div class="col col-md-6 ">
                <label for="codigo_id">Código CNAE</label>
                <select name="codigo_id" id="codigo_id" class="form-control select2" style="width: 100%" required disabled>
                    <!-- As opções serão carregadas via AJAX -->
                </select>
            </div>
        </div>
        <div class="mt-3">
            <button type="submit" class="btn btn-primary" id="btn-filtrar">Filtrar</button>
            <button type="button" id="limpar-filtro" class="btn btn-secondary"
                onclick="window.location='{{ route('relatorio.cnae') }}'">
                Limpar
            </button>
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
            <div class="mt-4">
                {{-- <button class="btn btn-sm btn-success" id="exportar-xlsx">Exportar .xlsx</button> --}}
                <form method="GET" action="{{ route('relatorio.exportar.cnae') }}" id="pdf-form">
                    @csrf
                    <input type="hidden" name="ano" id="hidden-ano">
                    <input type="hidden" name="unidade" id="hidden-unidade">
                    <input type="hidden" name="secretaria" id="hidden-secretaria">
                    <input type="hidden" name="status" id="hidden-status">
                    <a href="{{ route('relatorio.exportar.cnae') }}" class="btn btn-sm btn-info"
                        onclick="event.preventDefault(); document.getElementById('pdf-form').submit();">Exportar PDF</a>
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
                    @php
                        $totalPorUnidade = [];
                    @endphp
                    @foreach ($lancamentos as $lancamento)
                        @php
                            $unidadeNome = $lancamento->secretaria->unidadeGestora->nome;
                            $totalPorUnidade[$unidadeNome] = ($totalPorUnidade[$unidadeNome] ?? 0) + $lancamento->valor;
                        @endphp
                        <tr>
                            <td>{{ $lancamento->processo }}</td>
                            <td>{{ $lancamento->created_at->format('d/m/Y') }}</td>
                            <td>{{ $unidadeNome }} - {{ $lancamento->secretaria->nome }}</td>
                            <td>{{ $lancamento->objeto }}</td>
                            <td>{{ $lancamento->codigo->codigo }}</td>
                            <td>{{ $lancamento->codigo->nome }}</td>
                            <td>{{ $lancamento->valor_formatado }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6"><strong>Totais por Unidade Gestora:</strong></td>
                        <td></td>
                    </tr>
                    @foreach ($totalPorUnidade as $unidade => $soma)
                        <tr>
                            <td colspan="6">{{ $unidade }}</td>
                            <td><strong>R$ {{ number_format($soma, 2, ',', '.') }}</strong></td>
                        </tr>
                    @endforeach
                    <tr>
                        <td>Total Geral</td>
                        <td colspan="5"></td>
                        <td><strong>R$ {{ number_format($total, 2, ',', '.') }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>

@endsection
@section('css')
    <!-- Incluindo o CSS do Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.3.2/dist/select2-bootstrap4.min.css"
        rel="stylesheet" />
@stop
@section('js')
    <!-- Incluindo o Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/pt-BR.js"></script>

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
                            input.selectedIndex = 0;
                        }
                    } else {
                        input.value = '';
                    }
                });
                tabelaResultados.style.display = 'none';
            });

            if (tabelaResultados && tabelaResultados.innerHTML.trim() !== '') {
                tabelaResultados.style.display = 'block';
            }
        });
        $(document).ready(function() {
            $('#unidade_id').change(function() {
                var unidadeId = $(this).val();
                if (unidadeId) {
                    $.ajax({
                        url: "{{ route('getSecretarias', '') }}/" + unidadeId,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            $('#secretaria_id').prop('disabled', false);
                            $('#secretaria_id').empty().append(
                                '<option value="">Selecione a Secretaria</option>');
                            $.each(data, function(key, value) {
                                $('#secretaria_id').append('<option value="' + value
                                    .id +
                                    '">' + value.nome + '</option>');
                            });
                            if ((dados) && (dados.secretaria_id)) {
                                $('#secretaria_id').val(dados.secretaria_id).change();
                            }
                        }
                    });
                } else {
                    $('#secretaria_id').prop('disabled', true).empty().append(
                        '<option value="">Selecione a Secretaria</option>');
                }
            });

            $('#secretaria_id').change(function() {
                var secretariaId = $(this).val();
                if (secretariaId) {
                    $('#tipo_id').prop('disabled', false);
                } else {
                    $('#tipo_id').prop('disabled', true);
                }
            });

            $('#tipo_id').change(function() {
                var tipoId = $(this).val();
                if (tipoId) {
                    $('#codigo_id').val(null).trigger('change');
                    $('#codigo_id').empty().append('<option value="">Selecione o Código CNAE</option>')
                        .prop('disabled', true);
                    $.ajax({
                        url: "{{ route('getSubtipos', '') }}/" + tipoId,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            $('#subtipo_id').empty().append(
                                '<option value="">Selecione o Subtipo</option>');
                            $.each(data, function(key, value) {
                                $('#subtipo_id').append('<option value="' + value.id +
                                    '">' + value.nome + '</option>');
                            });
                            $('#subtipo_id').prop('disabled', false);
                            if ((dados) && (dados.codigo.subtipo_id)) {
                                $('#subtipo_id').val(dados.codigo.subtipo_id).change();
                            } else {
                                $('#codigo_id').empty().append(
                                        '<option value="">Selecione o Código CNAE</option>')
                                    .prop('disabled', true);
                            }
                        }
                    });
                } else {
                    $('#subtipo_id').prop('disabled', true).empty().append(
                        '<option value="">Selecione o Subtipo</option>');
                    $('#codigo_id').val(null).trigger('change').prop('disabled', true);

                }
            });

            $('#subtipo_id').change(function() {
                var subtipoId = $(this).val();
                if (subtipoId) {
                    $('#codigo_id').prop('disabled', false);
                    $('#codigo_id').val(dados.codigo_id).trigger('change');
                    setTimeout(function() {
                        $('#codigo_id').trigger('select2:select');
                    }, 200);
                } else {
                    $('#codigo_id').val(null).trigger('change');

                }
            });

            $('#codigo_id').select2({
                width: 'resolve',
                placeholder: "Digite o Código CNAE",
                allowClear: true,
                theme: 'bootstrap4',
                minimumInputLength: 2,
                ajax: {
                    url: "{{ route('searchCnaes') }}",
                    dataType: 'json',
                    delay: 500,
                    data: function(params) {
                        var subtipoId = $('#subtipo_id').val();
                        return {
                            search: params.term,
                            subtipo_id: subtipoId
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    id: item.id,
                                    text: item.codigo + ' - ' + item.nome
                                };
                            })
                        };
                    },
                    cache: true
                }
            });
        });

        $('#btn-filtrar').click(function() {
            e.preventDefault

            var arrayItens = [
                $('#unidade_id'),
                $('#tipo_id'),
                $('#subtipo_id'),
                $('#codigo_id'),
            ]

            function validaFormulario(arrayItens) {
                arrayItens.forEach(function(item) {
                    if (campo.val() === '') {
                        valido = false;
                        campo.css('border', '1px solid red');
                        alert('Por favor, preencha todos os campos!');
                    } else {
                        campo.css('border', '');
                    }
                });
                return valido;
            }
        });
    </script>
@endsection
