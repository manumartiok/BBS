<?php

namespace App\Http\Controllers;

use App\Models\UsuarioPublico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccesoPublicoController extends Controller
{
    private function guard()
    {
        return Auth::guard('usuario_publico');
    }

    public function show()
    {
        if ($this->guard()->check()) {
            return redirect()->route('home');
        }

        return view('content.web.acceso');
    }

    public function registro(Request $request)
    {
        $datos = $request->validateWithBag('registro', [
            'nombre' => 'required|string|max:255',
            'cuit' => 'required|string|max:50',
            'email' => 'required|email|max:255|unique:usuarios_publicos,email',
            'celular' => 'required|string|max:50',
            'direccion' => 'required|string|max:255',
            'localidad' => 'required|string|max:255',
            'provincia' => 'required|string|max:255',
            'codigo_postal' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.unique' => 'Ya existe una cuenta con ese email.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $usuario = UsuarioPublico::create($datos);

        $this->guard()->login($usuario);
        $request->session()->regenerate();

        return redirect()->route('home')->with('acceso_ok', 'Tu cuenta fue creada. ¡Bienvenido/a!');
    }

    public function login(Request $request)
    {
        $datos = $request->validateWithBag('login', [
            'login' => 'required|string|max:255',
            'password' => 'required|string',
        ]);

        // se entra con el email o con el nombre del registro
        if (str_contains($datos['login'], '@')) {
            $usuario = UsuarioPublico::where('email', $datos['login'])->first();
            $usuario = $usuario && Hash::check($datos['password'], $usuario->password) ? $usuario : null;
        } else {
            // el nombre puede repetirse: entra la cuenta con ese nombre cuya contraseña coincide
            $usuario = UsuarioPublico::where('nombre', $datos['login'])->get()
                ->first(fn ($candidato) => Hash::check($datos['password'], $candidato->password));
        }

        if (!$usuario) {
            return back()
                ->withErrors(['login' => 'Nombre, email o contraseña incorrectos.'], 'login')
                ->onlyInput('login');
        }

        $this->guard()->login($usuario);

        $request->session()->regenerate();

        return redirect()->route('home');
    }

    // Solo cierra la sesión pública: si hay un cliente en la zona privada, sigue conectado
    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('acceso_ok', 'Cerraste sesión correctamente.');
    }
}
