<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $email = $request->email;
        $password = $request->password;

        if (!Auth::attempt(['email' => $email, 'password' => $password, 'status' => 1])) {
            return redirect()->back()->with('error', ' Usuário ou senha inválidos. Por favor, tente novamente.');
        }

        $usuario = Auth::user();

        //Verica se a senha do usuário é a senha padrão e redireciona para a página de redefinição de senha
        if (password_verify('Mudar@123', $usuario->password)) {
            return redirect()->route('admin.perfil')->with('error', 'Por favor, redefina sua senha.');
        }

        return redirect('/admin/admin');
    }
}
