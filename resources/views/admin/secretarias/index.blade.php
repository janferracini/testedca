@extends('adminlte::page')
@section('plugins.CustomScripts', true)
@section('plugins.DataTables', true)
@section('plugins.Customcss', true)
@section('title', 'Secretarias')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                {{ $error }}
            @endforeach
        </div>
    @endif
    <div class="container">
        <h1>Secretarias</h1>
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
            <button class="btn btn-primary" data-toggle="modal" data-target="#modalSecretaria" onclick="abreModal()">
                Adicionar</button>
        </div>
        <div class="clearfix mb-3"></div>
        <table class="table table-over table-responsive" id="secretariaTable">
            <thead>
                <th style="width: 50%">Nome</th>
                <th style="width: 40%">UG</th>
                <th style="width: 10%">Status</th>
                <th style="width: 10%">Ações</th>
            </thead>
            <tbody>
                @foreach ($secretarias as $secretaria)
                    @php
                        $status = App\Models\UnidadeGestora::getStatus();
                        $status = $status[$secretaria->status];
                        $unidadeAtiva = $secretaria->unidadeGestora && $secretaria->unidadeGestora->status == 1;
                    @endphp
                    <tr class="{{ !$unidadeAtiva ? 'table-secondary' : '' }}">
                        <td>{{ $secretaria->nome }}</td>
                        <td>{{ $secretaria->unidadeGestora ? $secretaria->unidadeGestora->nome : 'Não Vinculada' }}</td>
                        <td {{ !$unidadeAtiva ? 'disabled' : '' }}>{{ $status }}</td>
                        <td>
                            <div class="col-12 col-sm-12 col-lg-3 col-md-3 dropdown">
                                <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button"
                                    id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                    aria-expanded="false" {{ !$unidadeAtiva ? 'disabled' : '' }}>
                                    Ações
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <button type="button" class="dropdown-item" data-toggle="modal"
                                        data-target="#modalSecretaria"
                                        onclick="abreModal('{{ $secretaria->id }}', '{{ $secretaria->nome }}', '{{ $secretaria->status ? 1 : 0 }}', '{{ $secretaria->unidade_id }}')">
                                        Editar
                                    </button>
                                    <form action="{{ route('secretaria.updateStatus', $secretaria->id) }}" method="POST"
                                        onsubmit="return confirm('Deseja realmente {{ $secretaria->status ? 'inativar' : 'ativar' }} esta Secretaria?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="dropdown-item">{{ $secretaria->status ? 'Inativar' : 'Ativar' }}</button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <small>Linhas inativas indicam que a Unidade Gestora encontra-se Inativada</small>
        {{ $secretarias->links() }}
    </div>
    <!-- Modal -->
    <form method="post" action="{{ route('secretaria.store') }}" id="modalSecretaria" autocomplete="on">
        @csrf
        <x-adminlte-modal id="modalCreate" title="Cadastrar/Editar Secretaria" size="lg" v-centered
            data-backdrop="static">
            <div class="modal-body">
                <div class="row">
                    <div class="col" hidden>
                        <label for="id">Id:</label>
                        <input type="text" id="id" name="id" class="form-control">
                    </div>
                    <div class="form-group col-12 col-md-12">
                        <label for="nome">Nome</label>
                        <input type="text" name="nome" id="nome" class="form-control" required autofocus>
                    </div>
                    <div class="form-group col-12 col-md-8">
                        <label for="unidade_id">Unidade Gestora</label>
                        <select name="unidade_id" id="unidade_id" class="form-control">
                            @foreach ($unidades as $unidade)
                                <option value="{{ $unidade->id }}">{{ $unidade->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-12 col-md-4">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control">
                            @foreach (\App\Models\Secretaria::getStatus() as $key => $value)
                                <option value="{{ $key }}" @if ((isset($ug) && $ug->type == $key) || (!isset($ug) && $key == 0)) selected @endif>
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
            $('#secretariaTable').DataTable({
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

        function abreModal(id, nome, status, unidade_id) {
            $('#id').val(id);
            $('#nome').val(nome);
            if (id == undefined) {
                $('#status').val(1);
            } else {
                $('#status').val(status);
            }
            $('#unidade_id').val(unidade_id);
            $('#modalCreate').modal('show');
        }

        $('#btnEnviar').click(function(e) {
            e.preventDefault();

            var arrayItens = [
                $('#nome'),
                $('#status'),
                $('#unidade_id')
            ];

            if (validaFormulario(arrayItens)) {
                $('#modalSecretaria').submit();
            }
        });
    </script>
@endsection
