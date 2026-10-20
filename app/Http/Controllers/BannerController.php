<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\GestionaArchivos;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Un solo controlador para los banners de todas las secciones.
 * La sección llega por la URL: /admin/banner/nosotros, /admin/banner/contacto, ...
 */
class BannerController extends Controller
{
    use GestionaArchivos;

    public function show(string $seccion)
    {
        return view('content.admin.banner', [
            'banner' => Banner::seccion($seccion),
            'titulo' => Str::headline($seccion),
        ]);
    }

    public function update(Request $request, string $seccion)
    {
        $request->validate([
            'foto' => 'nullable|image|max:2048',
        ]);

        $banner = Banner::seccion($seccion);
        $banner->foto = $this->procesarArchivo($request, 'foto', $banner->foto, 'banners');
        $banner->texto = $request->input('texto');
        $banner->save();

        return redirect()->route('adm.banner', $seccion)
            ->with('success', 'Banner actualizado correctamente.');
    }
}
