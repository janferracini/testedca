@extends('adminlte::page')

@section('content')
    <div class="container">
        <h1>Meu Perfil</h1>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <h2 class="mt-3">{{ $user->name }}</h2>
        <form action="{{ route('usuarios.update') }}" method="POST">
            @csrf
            <div class="form-group mt-3">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                    value="{{ $user->email }}">
                @error('email')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-group mt-3">
                <label for="password">Nova Senha (opcional)</label>
                <input type="password" id="password" name="password"
                    class="form-control @error('password') is-invalid @enderror">
                @error('password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            <div class="form-group mt-3">
                <label for="password_confirmation">Confirme a Nova Senha</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary mt-4">Salvar Alterações</button>
        </form>
    </div>
@endsection
