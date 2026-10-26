<?php
namespace App\Http\Controllers;

use App\Models\WebUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class WebUsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = WebUsuario::query();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($sub) use ($q) {
                $sub->where('usuario',       'like', "%{$q}%")
                    ->orWhere('email',        'like', "%{$q}%")
                    ->orWhere('razon_social', 'like', "%{$q}%")
                    ->orWhere('localidad',    'like', "%{$q}%")
                    ->orWhere('provincia',    'like', "%{$q}%")
                    ->orWhere('cuit',         'like', "%{$q}%")
                    ->orWhere('codigo_postal','like', "%{$q}%");
            });
        }

        $usuarios = $query->orderBy('id', 'desc')->paginate(50)->withQueryString();

        return view('content.admin.web-usuarios', ['usuarios' => $usuarios]);
    }

    /**
     * Display the specified resource.
     */
    public function show($usuarios_id)
    {
        $usuario = WebUsuario::find($usuarios_id);
        
        if (!$usuario) {
            return redirect()->route('adm.web-usuarios')->with('error', 'Usuario no encontrado.');
        }
        
        return view('content.admin.web-usuarios-editor', [
            'usuario' => $usuario,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
       public function update(Request $request)
{
    $request->validate([
        'usuarios_id'   => 'required|exists:web_usuarios,id',
        'usuario'       => 'nullable|string|max:255',
        'email'         => 'nullable|email|max:255',
        'rol'           => 'nullable|in:' . implode(',', array_keys(WebUsuario::ROLES)),
        'puntos'        => 'nullable|integer|min:0',
        'razon_social'  => 'nullable|string|max:255',
        'cuit'          => 'nullable|string|max:255',
        'telefono'      => 'nullable|string|max:255',
        'domicilio'     => 'nullable|string|max:255',
        'codigo_postal' => 'nullable|string|max:20',
        'localidad'     => 'nullable|string|max:255',
        'provincia'     => 'nullable|string|max:255',
        'pais'          => 'nullable|string|max:255',
        'descuento1'    => 'nullable|numeric|min:0|max:100',
        'descuento2'    => 'nullable|numeric|min:0|max:100',
        'descuento3'    => 'nullable|numeric|min:0|max:100',
        'descuento4'    => 'nullable|numeric|min:0|max:100',
        'descuento5'    => 'nullable|numeric|min:0|max:100',
        'contraseña'    => 'nullable|min:8|confirmed',
    ]);

    $usuario = WebUsuario::find($request->usuarios_id);

    if (!$usuario) {
        return redirect()->back()->with('error', 'Usuario no encontrado.');
    }

    // Guardar contraseña en texto plano ANTES de hashear, para el mail
    // Si no se envió nueva contraseña, NO tenemos la actual en texto plano
    // así que pasamos null y el mail no la mostrará
    $contraseñaParaMail = null;

    if ($request->filled('usuario')) $usuario->usuario = $request->usuario;
    if ($request->filled('email'))   $usuario->email   = $request->email;

    $usuario->rol    = $request->rol ?: null;
    $usuario->puntos = (int) $request->puntos;

    $usuario->razon_social  = $request->razon_social;
    $usuario->cuit          = $request->cuit;
    $usuario->telefono      = $request->telefono;
    $usuario->domicilio     = $request->domicilio;
    $usuario->codigo_postal = $request->codigo_postal;
    $usuario->localidad     = $request->localidad;
    $usuario->provincia     = $request->provincia;
    // $usuario->pais          = $request->pais;

    $usuario->descuento1 = $request->descuento1;
    $usuario->descuento2 = $request->descuento2;
    $usuario->descuento3 = $request->descuento3;
    $usuario->descuento4 = $request->descuento4;
    $usuario->descuento5 = $request->descuento5;

    // Manejar activo/inactivo desde el checkbox del form
    $eraActivo = $usuario->active;

    if ($request->filled('contraseña')) {
        $contraseñaParaMail  = $request->contraseña;
        $usuario->contraseña = Hash::make($contraseñaParaMail);
    }

    $usuario->save();

    return redirect()->route('adm.web-usuarios')->with('success', 'Usuario actualizado correctamente.');
}
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($usuarios_id)
    {
        $usuario = WebUsuario::find($usuarios_id);
        
        if (!$usuario) {
            return redirect()->route('adm.web-usuarios')->with('error', 'Usuario no encontrado.');
        }
        
        $usuario->delete();
        return redirect()->route('adm.web-usuarios')->with('success', 'Usuario eliminado correctamente.');
    }
      
    /**
     * Toggle user active status.
     */
    public function switch($usuarios_id)
    {
        $usuario = WebUsuario::find($usuarios_id);

        if (!$usuario) {
            return redirect()->route('adm.web-usuarios')->with('error', 'Usuario no encontrado.');
        }

        $usuario->active = !$usuario->active;
        $usuario->save();

        $status = $usuario->active ? 'activado' : 'desactivado';
        return redirect()->route('adm.web-usuarios')->with('success', "Usuario {$status} correctamente.");
    }
    
    public function perfil()
    {
        $authUser = auth('web_usuario')->user();
        $usuario  = WebUsuario::find($authUser->id);
        return view('content.web.web-perfil', ['usuario' => $usuario]);
    }

    public function perfilUpdate(Request $request)
    {
        $authUser = auth('web_usuario')->user();
        $usuario  = WebUsuario::find($authUser->id);
     
        $request->validate([
            'email'         => 'nullable|email|max:255|unique:web_usuarios,email,' . $usuario->id,
            'telefono'      => 'nullable|string|max:255',
            'domicilio'     => 'nullable|string|max:255',
            'codigo_postal' => 'nullable|string|max:20',
            'localidad'     => 'nullable|string|max:255',
            'provincia'     => 'nullable|string|max:255',
            'pais'          => 'nullable|string|max:255',
            'contraseña'    => 'nullable|min:8|confirmed',
        ], [
            'email.email'           => 'El email no tiene un formato válido',
            'email.unique'          => 'Ese email ya está en uso por otra cuenta',
            'contraseña.min'        => 'La contraseña debe tener al menos 8 caracteres',
            'contraseña.confirmed'  => 'Las contraseñas no coinciden',
        ]);
     
        if ($request->filled('email')) {
            $usuario->email = $request->email;
        }
     
        $usuario->telefono      = $request->telefono;
        $usuario->domicilio     = $request->domicilio;
        $usuario->codigo_postal = $request->codigo_postal;
        $usuario->localidad     = $request->localidad;
        $usuario->provincia     = $request->provincia;
        $usuario->pais          = $request->pais;
     
        if ($request->filled('contraseña')) {
            $usuario->contraseña = \Illuminate\Support\Facades\Hash::make($request->contraseña);
        }
     
        $usuario->save();
     
        return redirect()->route('web-perfil')->with('success', 'Perfil actualizado correctamente.');
    }

    
}