@extends('layout')
@section('plugins.CustomCss', true)

@section('content')
    <div class="login-page">
        <div class="login-box">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="mb-0 text-center">Gestão de Dispensa de Valor</h3>
                </div>
                <div class="card-body login-card-body">
                    <p>Fazer login para iniciar</p>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                <small>{{ $error }}</small>
                            @endforeach
                        </div>
                    @endif
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="form-floating mb-2">
                            <input id="email" type="email" name="email" class="form-control" value=""
                                placeholder="" @error('email') is-invalid @enderror autofocus required />
                            <label for="email" class="form-label">E-mail</label>
                        </div>

                        <div class="form-floating mb-2">
                            <input id="password" type="password" name="password" class="form-control" placeholder=""
                                required />
                            <label for="password" class="form-label">Senha</label>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-grid gap-2 float-right">
                                    <button type="submit" class="btn btn-primary">Entrar</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
