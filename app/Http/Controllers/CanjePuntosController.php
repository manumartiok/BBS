<?php

namespace App\Http\Controllers;

use App\Models\Canje;
use App\Models\Contacto;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\WebUsuario;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class CanjePuntosController extends Controller
{
    private const ENVIOS = Canje::ENVIOS;

    // Productos que se pueden canjear: activos y con puntos de canje cargados
    private function canjeables()
    {
        return Producto::where('active', true)->where('puntos', '>', 0);
    }

    public function index(Request $request)
    {
        $usuario = auth('web_usuario')->user();

        // Categorías con sus productos canjeables, para el desplegable de la izquierda
        $marcas = Marca::where('active', true)
            ->whereHas('productos', fn ($q) => $q->where('active', true)->where('puntos', '>', 0))
            ->with(['productos' => fn ($q) => $q->select('id', 'marca_id', 'nombre')
                ->where('active', true)->where('puntos', '>', 0)->orderBy('orden')])
            ->orderBy('orden')
            ->get();

        $productos = $this->canjeables()
            ->when($request->filled('marca'), fn ($q) => $q->where('marca_id', $request->integer('marca')))
            ->when($request->filled('producto'), fn ($q) => $q->where('id', $request->integer('producto')))
            ->orderBy('orden')
            ->paginate(9)
            ->withQueryString();

        $puntos = (int) ($usuario->puntos ?? 0);
        $cantidadCarrito = $this->carritoProductos()->count();

        return view('content.web.webcanje', compact('marcas', 'productos', 'puntos', 'cantidadCarrito'));
    }

    // Carrito de canje: elegir la forma de envío y ver lo que se va a canjear
    public function carrito()
    {
        $usuario = auth('web_usuario')->user();
        $productos = $this->carritoProductos();
        $puntos = (int) ($usuario->puntos ?? 0);
        $subtotal = (int) $productos->sum('puntos');
        $contacto = Contacto::first();
        $envioElegido = session('canje_envio', []);

        return view('content.web.webcanje-carrito', compact('productos', 'puntos', 'subtotal', 'contacto', 'envioElegido'));
    }

    public function agregar(Request $request)
    {
        $validated = $request->validate(['producto_id' => 'required|integer']);

        $producto = $this->canjeables()->find($validated['producto_id']);
        if (!$producto) {
            return response()->json(['success' => false, 'message' => 'Producto no disponible para canje.'], 404);
        }

        $ids = array_values(array_unique(array_merge(session('canje_carrito', []), [$producto->id])));
        session(['canje_carrito' => $ids]);

        return response()->json(['success' => true, 'cantidad' => count($ids)]);
    }

    public function quitar(Request $request)
    {
        $validated = $request->validate(['producto_id' => 'required|integer']);

        $ids = array_values(array_diff(session('canje_carrito', []), [(int) $validated['producto_id']]));
        session(['canje_carrito' => $ids]);

        return redirect()->route('web-canje.carrito');
    }

    // Movimiento de puntos: los canjes del usuario
    public function movimientos()
    {
        $usuario = auth('web_usuario')->user();
        $canjes = Canje::with('items')->where('usuario_id', $usuario->id)->latest()->paginate(10);
        $puntos = (int) ($usuario->puntos ?? 0);
        $cantidadCarrito = $this->carritoProductos()->count();

        return view('content.web.webcanje-movimientos', compact('canjes', 'puntos', 'cantidadCarrito'));
    }

    // Detalle del canje en PDF, en una pestaña nueva
    public function ver(Canje $canje)
    {
        abort_unless((int) $canje->usuario_id === (int) auth('web_usuario')->id(), 403);
        $canje->load(['usuario', 'items']);

        return Pdf::loadView('content.canjes.pdf', [
            'canje' => $canje,
            'envio' => self::ENVIOS[$canje->envio] ?? $canje->envio,
        ])->stream('canje-' . str_pad($canje->id, 8, '0', STR_PAD_LEFT) . '.pdf');
    }

    // Paso 1 -> 2: guarda la forma de envío y sigue con los datos de facturación
    public function guardarEnvio(Request $request)
    {
        $validated = $request->validate([
            'envio' => 'required|in:' . implode(',', array_keys(self::ENVIOS)),
            'codigo_postal' => 'nullable|string|max:10',
        ]);

        if (!$this->puedeCanjear()) {
            return redirect()->route('web-canje.carrito');
        }

        session(['canje_envio' => [
            'envio' => $validated['envio'],
            'codigo_postal' => $validated['envio'] === 'mercado_flex' ? ($validated['codigo_postal'] ?? null) : null,
        ]]);

        return redirect()->route('web-canje.detalle');
    }

    // Paso 2: datos de facturación y confirmación
    public function detalle()
    {
        $envioElegido = session('canje_envio');
        if (!$envioElegido || !$this->puedeCanjear()) {
            return redirect()->route('web-canje.carrito');
        }

        $usuario = auth('web_usuario')->user();
        $productos = $this->carritoProductos();
        $subtotal = (int) $productos->sum('puntos');
        $envioTexto = self::ENVIOS[$envioElegido['envio']] ?? $envioElegido['envio'];

        return view('content.web.webcanje-detalle', compact('usuario', 'productos', 'subtotal', 'envioTexto'));
    }

    // Confirma el canje: descuenta los puntos y registra lo canjeado
    public function realizar(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'cuit' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'celular' => 'required|string|max:50',
            'direccion' => 'required|string|max:255',
            'localidad' => 'required|string|max:255',
            'provincia' => 'required|string|max:255',
            'codigo_postal' => 'required|string|max:20',
            'mensaje' => 'nullable|string|max:2000',
            'otra_direccion' => 'nullable|boolean',
            'entrega_direccion' => 'required_if:otra_direccion,1|nullable|string|max:255',
            'entrega_localidad' => 'required_if:otra_direccion,1|nullable|string|max:255',
            'entrega_provincia' => 'required_if:otra_direccion,1|nullable|string|max:255',
            'entrega_codigo_postal' => 'required_if:otra_direccion,1|nullable|string|max:20',
        ]);

        $envioElegido = session('canje_envio');
        if (!$envioElegido || !$this->puedeCanjear()) {
            return redirect()->route('web-canje.carrito');
        }

        $productos = $this->carritoProductos();
        $total = (int) $productos->sum('puntos');

        try {
            DB::transaction(function () use ($request, $envioElegido, $productos, $total) {
                // se bloquea el usuario para no gastar dos veces los mismos puntos
                $usuario = WebUsuario::lockForUpdate()->findOrFail(auth('web_usuario')->id());
                if ((int) $usuario->puntos < $total) {
                    throw new \DomainException('No tenés puntos suficientes para este canje.');
                }

                $canje = Canje::create([
                    'usuario_id' => $usuario->id,
                    'envio' => $envioElegido['envio'],
                    'envio_codigo_postal' => $envioElegido['codigo_postal'] ?? null,
                    'nombre' => $request->nombre,
                    'cuit' => $request->cuit,
                    'email' => $request->email,
                    'celular' => $request->celular,
                    'direccion' => $request->direccion,
                    'localidad' => $request->localidad,
                    'provincia' => $request->provincia,
                    'codigo_postal' => $request->codigo_postal,
                    'mensaje' => $request->mensaje,
                    'entrega_otra' => $request->boolean('otra_direccion') ? [
                        'direccion' => $request->entrega_direccion,
                        'localidad' => $request->entrega_localidad,
                        'provincia' => $request->entrega_provincia,
                        'codigo_postal' => $request->entrega_codigo_postal,
                    ] : null,
                    'puntos' => $total,
                ]);

                foreach ($productos as $producto) {
                    $canje->items()->create([
                        'producto_id' => $producto->id,
                        'codigo' => $producto->codigo,
                        'nombre' => $producto->nombre,
                        'puntos' => (int) $producto->puntos,
                    ]);
                }

                $usuario->decrement('puntos', $total);
            });
        } catch (\DomainException $e) {
            return redirect()->route('web-canje.carrito')->with('error', $e->getMessage());
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'No se pudo registrar el canje. Intentá nuevamente.');
        }

        session()->forget(['canje_carrito', 'canje_envio']);

        return redirect()->route('web-canje')->with('canje_ok', 'Tu canje se realizó con éxito.');
    }

    // Hay productos en el carrito de canje y alcanzan los puntos
    private function puedeCanjear(): bool
    {
        $productos = $this->carritoProductos();

        return $productos->isNotEmpty()
            && (int) $productos->sum('puntos') <= (int) (auth('web_usuario')->user()->puntos ?? 0);
    }

    // Productos del carrito de canje que siguen disponibles
    private function carritoProductos()
    {
        $ids = session('canje_carrito', []);

        return $ids ? $this->canjeables()->whereIn('id', $ids)->orderBy('orden')->get() : collect();
    }
}
