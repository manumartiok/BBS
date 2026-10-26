<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\Marca;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ProductoBusquedaController extends Controller
{
    public function productos(Request $request)
    {
        // Sin filtros, entra con la primera categoría seleccionada.
        if (! $request->hasAny(['buscar', 'codigo', 'marca'])) {
            $primera = Marca::where('active', true)->orderBy('orden')->first();

            if ($primera) {
                return $this->categoria($request, $primera->id);
            }
        }

        $marcas = $this->marcasConProductos();
        $productos = $this->filtrarProductos($request)
            ->orderBy('orden')
            ->paginate(12)
            ->withQueryString();
        $dolar = Cotizacion::dolar();

        return view('content.web.productos', compact('marcas', 'productos', 'dolar'));
    }

    public function webProductos(Request $request)
    {
        $usuario = auth('web_usuario')->user();
        $descuentos = collect(range(1, 5))
            ->map(fn ($i) => (float) data_get($usuario, "descuento{$i}", 0))
            ->filter(fn ($d) => $d > 0)
            ->values()
            ->all();

        $marcas = $this->marcasConProductos();
        $productos = $this->filtrarProductos($request)
            ->orderBy('orden')
            ->paginate(10)
            ->withQueryString();

        $dolar = Cotizacion::dolar();

        return view('content.web.webproductos', compact('productos', 'descuentos', 'marcas', 'dolar'));
    }

    public function categoria(Request $request, $id)
    {
        $marca = Marca::findOrFail($id);
        $marcas = $this->marcasConProductos();
        $buscar = trim((string) $request->get('buscar', ''));

        $productos = Producto::with('marca')->where('marca_id', $marca->id)
            ->where('active', true)
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($sub) use ($buscar) {
                    $sub->where('nombre', 'like', "%{$buscar}%")
                        ->orWhere('codigo', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('orden')
            ->paginate(12)
            ->withQueryString();
        $dolar = Cotizacion::dolar();

        return view('content.web.productos-categoria', compact('productos', 'marca', 'marcas', 'buscar', 'dolar'));
    }

    // Categorías con sus productos activos (solo lo necesario para el desplegable del listado).
    private function marcasConProductos()
    {
        return Marca::where('active', true)
            ->with(['productos' => fn ($q) => $q->select('id', 'marca_id', 'nombre')->where('active', true)->orderBy('orden')])
            ->orderBy('orden')
            ->get();
    }

    private function marcas()
    {
        return Marca::where('active', true)
            ->orderBy('orden')
            ->get();
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
