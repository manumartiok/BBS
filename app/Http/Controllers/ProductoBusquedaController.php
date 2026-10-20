<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use App\Models\Producto;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ProductoBusquedaController extends Controller
{
    public function productos(Request $request)
    {
        $marcas = $this->marcas();
        $productos_ban = Banner::seccion('productos');
        $hayFiltros = $this->hayFiltros($request);
        $productos = collect();

        if ($hayFiltros) {
            $productos = $this->filtrarProductos($request)
                ->orderBy('orden')
                ->paginate(12)
                ->withQueryString();
        }

        return view('content.web.productos', compact('marcas', 'productos_ban', 'productos', 'hayFiltros'));
    }

    public function webProductos(Request $request)
    {
        $usuario = auth('web_usuario')->user();
        $descuentos = collect(range(1, 5))
            ->map(fn ($i) => (float) data_get($usuario, "descuento{$i}", 0))
            ->filter(fn ($d) => $d > 0)
            ->values()
            ->all();

        $marcas = $this->marcas();
        $productos = $this->filtrarProductos($request)
            ->orderBy('orden')
            ->paginate(10)
            ->withQueryString();

        return view('content.web.webproductos', compact('productos', 'descuentos', 'marcas'));
    }

    public function categoria(Request $request, $id)
    {
        $productos_ban = Banner::seccion('productos');
        $marca = Marca::findOrFail($id);
        $marcas = $this->marcas();
        $buscar = trim((string) $request->get('buscar', ''));

        $productos = Producto::where('marca_id', $marca->id)
            ->where('active', true)
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($sub) use ($buscar) {
                    $sub->where('nombre', 'like', "%{$buscar}%")
                        ->orWhere('codigo', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('orden')
            ->get();

        return view('content.web.productos-categoria', compact('productos', 'marca', 'marcas', 'productos_ban', 'buscar'));
    }

    private function marcas()
    {
        return Marca::where('active', true)
            ->orderBy('orden')
            ->get();
    }

    private function hayFiltros(Request $request): bool
    {
        return $request->filled('buscar')
            || $request->filled('codigo')
            || $request->filled('marca');
    }

    private function filtrarProductos(Request $request)
    {
        $query = Producto::with('marca')->where('active', true);

        if ($request->filled('marca')) {
            $query->where('marca_id', $request->integer('marca'));
        }

        if ($request->filled('codigo')) {
            $codigo = trim($request->get('codigo'));
            $query->where('codigo', 'like', "%{$codigo}%");
        }

        if ($request->filled('buscar')) {
            $buscar = trim($request->get('buscar'));
            $query->where(function ($sub) use ($buscar) {
                $sub->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('codigo', 'like', "%{$buscar}%");

                if (Schema::hasColumn('productos', 'modelo')) {
                    $sub->orWhere('modelo', 'like', "%{$buscar}%");
                }
            });
        }

        return $query;
    }
}
