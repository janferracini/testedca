@extends('adminlte::page')
@section('plugins.CustomScripts', true)
@section('plugins.Customcss', true)
@section('title', 'Valores Anuais')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                {{ $error }}
            @endforeach
        </div>
    @endif
    <div class="container">
        <h1>Valores Anuais</h1>
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="float-right">
            <button class="btn btn-primary" data-toggle="modal" data-target="#modalValor" onclick="abreModal()">
                Adicionar</button>
        </div>
        <div class="clearfix mb-3"></div>
        <table class="table table-hover table-responsive" id="valorTable">
            <thead>
                <th style="width: 15%">Ano</th>
                <th style="width: 75%">Valor</th>
                <th style="width: 10%">Status</th>
                <th style="width: 10%">Ações</th>
            </thead>
            <tbody>
                @foreach ($valores as $valor)
                    @php
                        $status = App\Models\Valor::getStatus();
                        $status = $status[$valor->status];
                    @endphp
                    <tr>
                        <td>{{ $valor->ano }}</td>
                        <td>R$ {{ number_format($valor->valor, 2, ',', '.') }}</td>
                        <td>{{ $status }}</td>
                        <td>
                            <div class="col-12 col-sm-12 col-lg-3 col-md-3 dropdown">
                                <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button"
                                    id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                    aria-expanded="false">
                                    Ações
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <button type="button" class="dropdown-item" data-toggle="modal"
                                        data-target="#modalValor"
                                        onclick="abreModal('{{ $valor->id }}', '{{ $valor->ano }}', '{{ $valor->valor }}', '{{ $valor->status ? 1 : 0 }}')">Editar</button>
                                    <form action="{{ route('valores.updateStatus', $valor->id) }}" method="POST"
                                        onsubmit="return confirm('Deseja realmente {{ $valor->status ? 'inativar' : 'ativar' }} este Valor Anual?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="dropdown-item">{{ $valor->status ? 'Inativar' : 'Ativar' }}</button>
                                    </form>
                                </div>
                            </div>
                        </td>
                @endforeach
            </tbody>
        </table>
    </div>
    <!-- Modal -->
    <form method="post" action="{{ route('valores.store') }}" id="modalValor" autocomplete="on">
        @csrf
        <x-adminlte-modal id="modalCreate" title="Cadastrar/Editar Valor Anual" size="lg" v-centered
            data-backdrop="static">
            <div class="modal-body">
                <div class="row">
                    <div class="col" hidden>
                        <label for="id">Id:</label>
                        <input type="text" id="id" name="id" class="form-control">
                    </div>
                    <div class="form-group col-12 col-md-4">
                        <label for="ano">Ano</label>
                        <input type="text" name="ano" id="ano" class="form-control" required autofocus>
                    </div>
                    <div class="form-group col-12 col-md-4">
                        <label for="valor">Valor R$</label>
                        <input type="text" name="valor" id="valor" class="form-control">
                        </input>
                    </div>
                    <div class="form-group col-12 col-md-4">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control">
                            @foreach (\App\Models\Valor::getStatus() as $key => $value)
                                <option value="{{ $key }}" @if ((isset($valor) && $valor->type == $key) || (!isset($valor) && $key == 0)) selected @endif>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <x-slot name="footerSlot">
                <x-adminlte-button type="button" theme="secondary" icon="fas fa-times" label="Fechar"
                    data-dismiss="modal" />
                <x-adminlte-button type="button" id="btnEnviar" theme="success" icon="fas fa-check" label="Salvar" />
            </x-slot>
        </x-adminlte-modal>
    </form>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            $('#valorTable').DataTable({
                "language": {
                    "url": "/vendor/datatables/i18n/pt-BR.json"
                },
                "paging": true,
                "ordering": true,
                "searching": true,
                "info": true,
                "lengthMenu": [10, 25, 50, 100],
                "columnDefs": [{
                    "orderable": false,
                    "targets": [3]
                }]
            });
        });

        function formatarReal(valor) {
            return valor.replace('.', ',');
        }

        function formatarDecimal(valor) {
            return valor.replace(',', '.');
        }

        $('#modalCreate').on('shown.bs.modal', function() {
            var valor = $('#valor').val();
            if (valor) {
                valor = formatarReal(valor);
                $('#valor').val(valor);
            }
        });

        function abreModal(id, ano, valor, status) {
            $('#id').val(id);
            $('#ano').val(ano);
            $('#valor').val(valor);
            if (id == undefined) {
                $('#status').val(1);
            } else {
                $('#status').val(status);
            }
            $('#modalCreate').modal('show');
        }

        $('#btnEnviar').click(function(e) {
            e.preventDefault();

            var arrayItens = [
                $('#ano'),
                $('#status')
            ];

            var valor = $('#valor').val();
            var regexVirgula = /^\d+,\d{2}$/;
            var regexPonto = /^\d+\.\d{2}$/;

            if (regexVirgula.test(valor)) {
                valor = formatarDecimal(valor);
                $('#valor').val(valor);
            } else if (!regexPonto.test(valor)) {
                alert('Por favor, informe um valor válido no formato 99999,99 ou 99999.99');
                return;
            }
            if (validaFormulario(arrayItens)) {
                $('#modalValor').submit();
            }
        });
    </script>

@endsection
