<?php

namespace App\Http\Controllers;

use App\Models\CarritoPublicoItem;
use App\Models\Contacto;
use App\Models\PedidoPublico;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

// Carrito de los usuarios públicos (el de la zona privada es el de PedidoController)
class CarritoPublicoController extends Controller
{
    private const ENVIOS = ['mercado_flex' => 'Mercado Flex', 'transporte_terminal' => 'Transporte a la terminal', 'retiro_local' => 'Retiro en local'];
    private const PAGOS = ['mercado_pago' => 'Mercado Pago', 'transferencia' => 'Transferencia bancaria', 'efectivo' => 'Efectivo (únicamente retiro en local)'];

    private function usuarioId(): int
    {
        return (int) auth('usuario_publico')->id();
    }

    // Contenido del carrito con precios actuales; ignora productos que ya no se venden
    private function estado(): array
    {
        $items = CarritoPublicoItem::with('producto')
            ->where('usuario_publico_id', $this->usuarioId())
            ->orderBy('id')
            ->get()
            ->filter(fn ($item) => $item->producto && $item->producto->active && !is_null($item->producto->precio_pesos))
            ->map(fn ($item) => [
                'producto_id' => $item->producto_id,
                'nombre' => $item->producto->nombre,
                'codigo' => $item->producto->codigo,
                'foto' => $item->producto->foto_url,
                'precio' => (float) $item->producto->precio_pesos,
                'cantidad' => $item->cantidad,
                'subtotal' => (float) $item->producto->precio_pesos * $item->cantidad,
            ])
            ->values();

        return [
            'success' => true,
            'items' => $items,
            'cantidad' => $items->count(),
            'subtotal' => $items->sum('subtotal'),
        ];
    }

    // Paso 2 de la compra: forma de envío
    public function pasoEnvio()
    {
        $carrito = $this->estado();
        if ($carrito['items']->isEmpty()) {
            return redirect()->route('compra.carrito');
        }

        return view('content.web.compra-envio', [
            'items' => $carrito['items'],
            'subtotal' => $carrito['subtotal'],
            'contacto' => Contacto::first(),
            'envioElegido' => session('compra_envio', []),
        ]);
    }

    // Guarda la forma de envío elegida para los pasos que siguen
    public function guardarEnvio(Request $request)
    {
        $validated = $request->validate([
            'envio' => 'required|in:mercado_flex,transporte_terminal,retiro_local',
            'codigo_postal' => 'nullable|string|max:10',
        ]);

        session(['compra_envio' => [
            'envio' => $validated['envio'],
            'codigo_postal' => $validated['envio'] === 'mercado_flex' ? ($validated['codigo_postal'] ?? null) : null,
        ]]);

        return response()->json(['success' => true]);
    }

    // Paso 3 de la compra: forma de pago
    public function pasoPago()
    {
        $carrito = $this->estado();
        $envio = session('compra_envio');
        if ($carrito['items']->isEmpty() || !$envio) {
            return redirect()->route($carrito['items']->isEmpty() ? 'compra.carrito' : 'compra.envio');
        }

        // el efectivo solo vale para retiro en local
        $pago = session('compra_pago');
        if ($pago === 'efectivo' && $envio['envio'] !== 'retiro_local') {
            $pago = null;
        }

        return view('content.web.compra-pago', [
            'items' => $carrito['items'],
            'subtotal' => $carrito['subtotal'],
            'contacto' => Contacto::first(),
            'envioTexto' => self::ENVIOS[$envio['envio']] ?? $envio['envio'],
            'retiroLocal' => $envio['envio'] === 'retiro_local',
            'pagoActual' => $pago ?: 'mercado_pago',
        ]);
    }

    // Guarda la forma de pago elegida para el paso que sigue
    public function guardarPago(Request $request)
    {
        $validated = $request->validate([
            'pago' => 'required|in:mercado_pago,transferencia,efectivo',
        ]);

        if ($validated['pago'] === 'efectivo' && (session('compra_envio.envio') !== 'retiro_local')) {
            return response()->json(['success' => false, 'message' => 'El pago en efectivo es únicamente con retiro en local.'], 422);
        }

        session(['compra_pago' => $validated['pago']]);

        return response()->json(['success' => true]);
    }

    // Paso 4 de la compra: datos de facturación y confirmación
    public function pasoFinal()
    {
        $carrito = $this->estado();
        $envio = session('compra_envio');
        $pago = session('compra_pago');

        if ($carrito['items']->isEmpty()) {
            return redirect()->route('compra.carrito');
        }
        if (!$envio) {
            return redirect()->route('compra.envio');
        }
        if (!$pago || !$this->pagoValido($pago, $envio['envio'])) {
            return redirect()->route('compra.pago');
        }

        return view('content.web.compra-final', [
            'items' => $carrito['items'],
            'subtotal' => $carrito['subtotal'],
            'usuario' => auth('usuario_publico')->user(),
            'envioTexto' => self::ENVIOS[$envio['envio']] ?? $envio['envio'],
            'pagoTexto' => self::PAGOS[$pago] ?? $pago,
        ]);
    }

    // Confirma el pedido: lo registra con el carrito actual y vacía el carrito
    public function realizarPedido(Request $request)
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

        $carrito = $this->estado();
        $envio = session('compra_envio');
        $pago = session('compra_pago');

        if ($carrito['items']->isEmpty()) {
            return redirect()->route('compra.carrito');
        }
        if (!$envio) {
            return redirect()->route('compra.envio');
        }
        if (!$pago || !$this->pagoValido($pago, $envio['envio'])) {
            return redirect()->route('compra.pago');
        }

        try {
            DB::transaction(function () use ($request, $carrito, $envio, $pago) {
                $pedido = PedidoPublico::create([
                    'usuario_publico_id' => $this->usuarioId(),
                    'envio' => $envio['envio'],
                    'envio_codigo_postal' => $envio['codigo_postal'] ?? null,
                    'forma_pago' => $pago,
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
                    'total' => $carrito['subtotal'],
                ]);

                foreach ($carrito['items'] as $item) {
                    $pedido->items()->create([
                        'producto_id' => $item['producto_id'],
                        'codigo' => $item['codigo'],
                        'nombre' => $item['nombre'],
                        'cantidad' => $item['cantidad'],
                        'precio' => $item['precio'],
                        'subtotal' => $item['subtotal'],
                    ]);
                }

                CarritoPublicoItem::where('usuario_publico_id', $this->usuarioId())->delete();
            });
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'No se pudo registrar el pedido. Intentá nuevamente.');
        }

        session()->forget(['compra_envio', 'compra_pago']);

        return redirect()->route('home')->with('compra_ok', 'Tu pedido se realizó con éxito.');
    }

    // El efectivo solo vale para retiro en local
    private function pagoValido(string $pago, string $envio): bool
    {
        return isset(self::PAGOS[$pago]) && ($pago !== 'efectivo' || $envio === 'retiro_local');
    }

    public function index()
    {
        return response()->json($this->estado());
    }

    public function agregar(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|integer',
            'cantidad' => 'required|integer|min:1|max:9999',
        ]);

        $producto = Producto::where('active', true)->find($validated['producto_id']);
        if (!$producto) {
            return response()->json(['success' => false, 'message' => 'Producto no encontrado.'], 404);
        }
        if (is_null($producto->precio_pesos)) {
            return response()->json(['success' => false, 'message' => 'Precio no disponible por el momento.'], 503);
        }

        $item = CarritoPublicoItem::firstOrNew([
            'usuario_publico_id' => $this->usuarioId(),
            'producto_id' => $producto->id,
        ]);
        $item->cantidad = min(9999, ($item->exists ? $item->cantidad : 0) + $validated['cantidad']);
        $item->save();

        return response()->json($this->estado());
    }

    public function actualizar(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|integer',
            'cantidad' => 'required|integer|min:1|max:9999',
        ]);

        $actualizados = CarritoPublicoItem::where('usuario_publico_id', $this->usuarioId())
            ->where('producto_id', $validated['producto_id'])
            ->update(['cantidad' => $validated['cantidad']]);

        if (!$actualizados) {
            return response()->json(['success' => false, 'message' => 'El producto no está en el carrito.'], 404);
        }

        return response()->json($this->estado());
    }

    public function eliminar(Request $request)
    {
        $validated = $request->validate(['producto_id' => 'required|integer']);

        CarritoPublicoItem::where('usuario_publico_id', $this->usuarioId())
            ->where('producto_id', $validated['producto_id'])
            ->delete();

        return response()->json($this->estado());
    }
}
