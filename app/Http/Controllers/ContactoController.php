<?php

namespace App\Http\Controllers;

use App\Models\Contacto;
use Illuminate\Http\Request;

class ContactoController extends Controller
{
    /**
     * Mostrar editor de contacto (único registro)
     */
    public function show($contactos_id = null)
    {
        $contacto = Contacto::first() ?? new Contacto();

        return view('content.admin.contacto-editor', [
            'contacto' => $contacto
        ]);
    }

    /**
     * Crear o actualizar información de contacto
     */
    public function update(Request $request)
    {
        $contacto = Contacto::find($request->contactos_id) ?? new Contacto();

        $contacto->ubicacion = $request->ubicacion;
        $contacto->direccion = $request->direccion;

        $contacto->mail = $request->mail;

        $contacto->telefono = $request->telefono;

        $contacto->instagram = $request->instagram;
        $contacto->facebook  = $request->facebook;
        $contacto->youtube   = $request->youtube;
        $contacto->linkedin  = $request->linkedin;

        $contacto->whatssap = $request->whatssap;

        $contacto->save();

        return redirect()->route('adm.contacto', [
            'contactos_id' => $contacto->id
        ])->with('success', 'Información de contacto actualizada correctamente.');
    }
}
