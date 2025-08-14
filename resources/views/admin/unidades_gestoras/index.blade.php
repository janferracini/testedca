@extends('adminlte::page')
@section('plugins.CustomScripts', true)
@section('plugins.Customcss', true)
@section('title', 'Unidades Gestoras')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                {{ $error }}
            @endforeach
        </div>
    @endif

    <div class="container">
        <h1>Unidades Gestoras</h1>
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
            <button class="btn btn-primary" data-toggle="modal" data-target="#modalUg" onclick="abreModal()">
                Adicionar</button>
        </div>
        <div class="clearfix mb-3"></div>
        <table class="table table-hover table-responsive" id="ugTable">
            <thead>
                <th style="width: 100%">Nome</th>
                <th style="width: 10%">Status</th>
                <th style="width: 10%">Ações</th>
            </thead>
            <tbody>
                @foreach ($unidades as $ug)
                    @php
                        $status = App\Models\UnidadeGestora::getStatus();
                        $status = $status[$ug->status];
                    @endphp
                    <tr>
                        <td>{{ $ug->nome }}</td>
                        <td>{{ $status }}</td>
                        <td>
                            <div class="col-12 col-sm-12 col-lg-3 col-md-3 dropdown">
                                <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button"
                                    id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                    aria-expanded="false">
                                    Ações
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <button type="button" class="dropdown-item" data-toggle="modal" data-target="#modalUg"
                                        onclick="abreModal('{{ $ug->id }}', '{{ $ug->nome }}', '{{ $ug->status ? 1 : 0 }}')">Editar</button>
                                    <form action="{{ route('ug.updateStatus', $ug->id) }}" method="POST"
                                        onsubmit="return confirm('Deseja realmente {{ $ug->status ? 'inativar' : 'ativar' }} esta Unidade Gestora?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="dropdown-item">{{ $ug->status ? 'Inativar' : 'Ativar' }}</button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <!-- Modal -->
    <form method="post" action="{{ route('unidade_gestora.store') }}" id="modalUg" autocomplete="on">
        @csrf
        <x-adminlte-modal id="modalCreate" title="Cadastrar/Editar Unidade Gestora" size="lg" v-centered
            data-backdrop="static">
            <div class="modal-body">
                <div class="row">
                    <div class="col" hidden>
                        <label for="id">Id:</label>
                        <input type="text" id="id" name="id" class="form-control">
                    </div>
                    <div class="form-group col-12 col-md-8">
                        <label for="nome">Nome</label>
                        <input type="text" name="nome" id="nome" class="form-control" required autofocus>
                    </div>
                    <div class="form-group col-12 col-md-4">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control">
                            @foreach (\App\Models\UnidadeGestora::getStatus() as $key => $value)
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
            $('#ugTable').DataTable({
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
                    "targets": [2]
                }]
            });
        });

        function abreModal(id, nome, status) {
            $('#id').val(id);
            $('#nome').val(nome);
            if (id == undefined) {
                $('#status').val(1);
            } else {
                $('#status').val(status);
            }
            $('#modalCreate').modal('show');
        };

        function validaFormulario(arrayItens) {
            var isValid = true;

            arrayItens.forEach(function(item) {
                if (item.val() === '' || item.val() === null || item.val() == undefined) {
                    item.addClass('is-invalid');
                    isValid = false;
                } else {
                    item.removeClass('is-invalid');
                }
            });

            return isValid;
        }

        $('#btnEnviar').click(function(e) {
            e.preventDefault();

            var arrayItens = [
                $('#nome'),
                $('#status')
            ];

            if (validaFormulario(arrayItens)) {
                $('#modalUg').submit();
            }
        });
    </script>
@endsection
