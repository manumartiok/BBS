<?php

namespace App\Http\Controllers;

use App\Models\HomeBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeBannerController extends Controller
{
     public function index()
    {
        $home_banners = HomeBanner::orderBy('orden')->get();
        return view('content.admin.home-banner', ['home_banner' => $home_banners]);
    }

    public function create()
    {
        return view('content.admin.home-banner-creador');
    }

    public function store(Request $request)
    {
        $request->validate([
            'foto' => 'nullable|file|mimetypes:image/jpeg,image/png,image/jpg,image/gif,image/webp,video/mp4,video/mpeg,video/quicktime,video/webm|max:51200', // 50 MB
        ]);

        $home_banners = new HomeBanner();
        $home_banners->orden = $request->orden;
        // $home_banners->titulo = $request->titulo;
        // $home_banners->texto = $request->texto;

        // Manejo de foto o video
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $mimeType = $file->getMimeType();
            
            // Determinar si es imagen o video
            if (strpos($mimeType, 'image') !== false) {
                $home_banners->tipo_medio = 'imagen';
            } elseif (strpos($mimeType, 'video') !== false) {
                $home_banners->tipo_medio = 'video';
            }

            $name = time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('banners', $name, 'public');
            $home_banners->foto = Storage::url($path);
        }

        $home_banners->save();
        return redirect()->route('adm.home-ban')->with('success', 'Acción realizada correctamente.');
    }

    public function show($home_banners_id)
    {
        $home_banners = HomeBanner::find($home_banners_id);
        return view('content.admin.home-banner-editor', ['home_banner' => $home_banners]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'foto' => 'nullable|file|mimetypes:image/jpeg,image/png,image/jpg,image/gif,image/webp,video/mp4,video/mpeg,video/quicktime,video/webm|max:51200', // 50 MB
        ]);

        $home_banners = HomeBanner::find($request->home_banners_id) ?? new HomeBanner();
        $home_banners->orden = $request->orden;
        // $home_banners->titulo = $request->titulo;
        // $home_banners->texto = $request->texto;

        // Manejo de foto o video
        if ($request->hasFile('foto')) {
            // Eliminar archivo anterior
            if (!empty($home_banners->foto)) {
                $oldPath = str_replace('/storage/', '', $home_banners->foto);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $file = $request->file('foto');
            $mimeType = $file->getMimeType();
            
            // Determinar si es imagen o video
            if (strpos($mimeType, 'image') !== false) {
                $home_banners->tipo_medio = 'imagen';
            } elseif (strpos($mimeType, 'video') !== false) {
                $home_banners->tipo_medio = 'video';
            }

            $name = time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('banners', $name, 'public');
            $home_banners->foto = Storage::url($path);
        }

        $home_banners->save();
        return redirect()->route('adm.home-ban', ['home_banner' => $home_banners->id])->with('success', 'Acción realizada correctamente.');
    }

    public function destroy($home_banners_id)
    {
        $home_banners = HomeBanner::find($home_banners_id);

        if ($home_banners) {
            if (!empty($home_banners->foto)) {
                $imagePath = str_replace('/storage/', '', $home_banners->foto);
                if (Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }
            }
            $home_banners->delete();
        }

        return redirect()->route('adm.home-ban')->with('success', 'Acción realizada correctamente.');
    }

    public function switch($home_banners_id)
    {
        $home_banners = HomeBanner::find($home_banners_id);
        $home_banners->active = !$home_banners->active;
        $home_banners->save();
        return redirect()->route('adm.home-ban');
    }

    public function reordenar(Request $request)
    {
        foreach ($request->orden as $home_banners) {
            \App\Models\HomeBanner::where('id', $home_banners['id'])->update(['orden' => $home_banners['orden']]);
        }

        return response()->json(['success' => true])->with('success', 'Acción realizada correctamente.');
    }
}
