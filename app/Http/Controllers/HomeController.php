<?php

namespace App\Http\Controllers;

use App\Models\Home;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    private const TITULOS = ['titulo1', 'titulo2', 'titulo3', 'titulo4', 'titulo5', 'titulo6'];

    public function show()
    {
        $home = Home::first() ?? new Home();

        return view('content.admin.home', compact('home'));
    }

    public function update(Request $request)
    {
        $datos = $request->validate(
            array_fill_keys(self::TITULOS, 'nullable|string|max:255')
        );

        $home = Home::first() ?? new Home();
        $home->fill($datos);
        $home->save();

        return redirect()
            ->route('adm.home')
            ->with('success', 'Página de inicio actualizada correctamente.');
    }
}
