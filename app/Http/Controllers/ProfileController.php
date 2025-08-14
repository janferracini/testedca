<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class ProfileController extends Controller
{
    public function index()
    {
        $usuarios = User::orderBy('name', 'asc')->paginate(10);
        return view('admin.usuarios.gerenciar', compact('usuarios'));
    }

    public function perfil()
    {
        $user = Auth::user(); // Busca o usuário logado
        return view('admin.usuarios.perfil', compact('user')); // Retorna a view com os dados do usuário
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        // Validação dos campos
        $request->validate([
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8|confirmed',
        ]);

        $user->email = $request->input('email');

        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        try {
            $user->save();
            return redirect()->back()->with('success', 'Perfil atualizado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ocorreu um erro ao atualizar o perfil.');
        }

        return redirect()->back()->with('success', 'Perfil atualizado com sucesso!');
    }

    public function store(Request $request)
    {

        $data = [
            'id' => $request->input('id'),
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'type' => $request->input('type'),
        ];

        if ($request->filled('id')) {
            $request->validate([
                'name' => 'required',
                'email' => 'required|email',
            ]);
        } else {
            $request->validate([
                'name' => 'required',
                'email' => 'required|email',
                'password' => 'required|min:8',
            ]);
        }

        $verify_email = User::where('email', $data['email'])->first();

        if (!empty($verify_email) && $verify_email->id != $data['id']) {
            return redirect()->route('usuarios.index')->with('error', 'Erro ao cadastrar Usuário. E-mail já cadastrado');
        }

        try {
            if ($data['id'] != null) {

                if (!empty($data['password'])) {
                    $data['password'] = bcrypt($data['password']);
                } else {
                    $data['password'] = User::find($data['id'])->password;
                }

                User::find($data['id'])->update($data);
                return redirect()->route('usuarios.index')->with('success', 'Usuário atualizado com sucesso!');
            } else {
                $data['status'] = true;
                User::create($data);
                return redirect()->route('usuarios.index')->with('success', 'Usuário cadastrado com sucesso!');
            }
            return redirect()->route('usuarios.gerenciar')->with('success', 'Usuário cadastrado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao cadastrar ou editar usuário, verifique os dados e caso o erro persista entre em contato com a Divisão de Desenvolvimento.');
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $newStatus = !$user->status;
        $user->status = $newStatus;
        $user->save();

        $message = $newStatus ? 'Usuário ativado com sucesso!' : 'Usuário inativado com sucesso!';

        return redirect()->route('usuarios.index')->with('status', $message);
    }
}
