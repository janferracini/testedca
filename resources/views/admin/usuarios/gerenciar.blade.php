@extends('adminlte::page')
@section('plugins.CustomScripts', true)
@section('plugins.DataTables', true)
@section('plugins.Customcss', true)
@section('title', 'Gerenciar Usuários')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                {{ $error }}
            @endforeach
        </div>
    @endif

    <div class="container">
        <h1>Gerenciar Usuários</h1>
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
            <button class="btn btn-primary" data-toggle="modal" data-target="#modalUser" onclick="abreModal()">
                Adicionar</button>
        </div>
        <div class="clearfix mb-3"></div>
        <table class="table table-hover table-responsive" id="userTable">
            <thead>
                <tr>
                    <th style="width: 40%">Nome</th>
                    <th style="width: 30%">E-mail</th>
                    <th style="width: 10%">Tipo</th>
                    <th style="width: 10%">Status</th>
                    <th style="width: 10%">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($usuarios as $usuario)
                    @php
                        $status = App\Models\User::getStatus();
                        $type = App\Models\User::getType();
                        $status = $status[$usuario->status];
                        $type = $type[$usuario->type];
                    @endphp
                    <tr>
                        <td>{{ $usuario->name }}</td>
                        <td>{{ $usuario->email }}</td>
                        <td>{{ $type }}</td>
                        <td>{{ $status }}</td>
                        {{-- dropdonw com ações de editar e inativar --}}
                        <td>
                            <div class="col-12 col-sm-12 col-lg-3 col-md-3 dropdown">
                                <button class="btn btn-outline-primary dropdown-toggle" type="button"
                                    id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                    aria-expanded="false">
                                    Ações
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <button type="button" class="dropdown-item" data-toggle="modal"
                                        data-target="#modalUser"
                                        onclick="abreModal('{{ $usuario->id }}', '{{ $usuario->name }}', '{{ $usuario->email }}', '{{ $usuario->type }}')">Editar</button>
                                    <form action="{{ route('usuarios.updateStatus', $usuario->id) }}" method="POST"
                                        onsubmit="return confirm('Deseja realmente {{ $usuario->status ? 'inativar' : 'ativar' }} este usuário?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="dropdown-item">{{ $usuario->status ? 'Inativar' : 'Ativar' }}</button>
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
    <form method="post" action="{{ route('usuarios.store') }}" id="modalUser" autocomplete="off">
        @csrf
        <x-adminlte-modal id="modalCreate" title="Cadastrar/Editar Usuário" size="lg" v-centered
            data-backdrop="static">
            <div class="modal-body">
                <div class="row">
                    <div class="col" hidden>
                        <label for="id">Id:</label>
                        <input type="text" id="id" name="id" class="form-control">
                    </div>
                    <div class="form-group col-12 col-md-6">
                        <label for="name" class="nome">Nome</label>
                        <input type="text" name="name" id="name" class="form-control" required autofocus>
                    </div>
                    <div class="form-group col-12 col-md-6">
                        <label for="email">E-mail</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>
                    <div class="form-group col-12 col-md-6">
                        <label for="password">Senha</label>
                        <input type="password" name="password" id="password" class="form-control">
                        <p id="error-message" style="color: red; display: none;">A senha deve ter no mínimo 8 caracteres.
                        </p>
                    </div>
                    <div class="form-group col-12 col-md-6">
                        <label for="type">Tipo</label>
                        <select name="type" id="type" class="form-control">
                            @foreach (\App\Models\User::getType() as $key => $value)
                                <option value="{{ $key }}" @if ((isset($usuario) && $usuario->type == $key) || (!isset($usuario) && $key == 0)) selected @endif>
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
            $('#userTable').DataTable({
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
                    "targets": [4]
                }]
            });
        });

        function abreModal(id, name, email, type) {
            $('#id').val(id);
            $('#name').val(name);
            $('#email').val(email);
            $('#type').val(type);
            $('#error-message').hide();
            $('#modalCreate').modal('show');
        };

        $('#btnEnviar').click(function(e) {
            e.preventDefault();

            var id = $('#id').val();
            var arrayItens = [
                $('#name'),
                $('#email'),
                $('#type')
            ];

            if ((id == '') || (id == null)) {
                var passwordField = $('#password');
                arrayItens.push(passwordField);
                if (passwordField.val().length < 8) {
                    $('#error-message').show();
                    return;
                } else {
                    $('#error-message').hide();
                }
            }

            if (validaFormulario(arrayItens)) {
                $('#modalUser').submit();
            }
        });

        @if (session('success'))
            Toast.fire({
                type: 'success',
                title: '{{ session('success') }}'
            });
        @elseif (session('error'))
            Toast.fire({
                type: 'error',
                title: '{{ session('error') }}'
            })
        @endif
    </script>
@endsection
