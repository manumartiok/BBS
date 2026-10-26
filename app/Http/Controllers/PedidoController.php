<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManejaCarrito;
use App\Mail\PedidoConfirmacionMail;
use App\Models\CarritoPendiente;
use App\Models\Cotizacion;
use App\Models\Pedido;
use App\Models\Producto;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PedidoController extends Controller
{
    use ManejaCarrito;

    public function index()
    {
        $usuario = auth('web_usuario')->user();
        $carrito = $this->cargarCarritoPendiente($usuario);
        $descuentos = $this->descuentosDelUsuario($usuario);
        $productosCarrito = [];

        foreach ($carrito as $productoId => $item) {
            $producto = Producto::where('active', true)->find($productoId);
            if (!$producto) {
                continue;
            }

            $precioBase = (float) $producto->precio_pesos;
            $productosCarrito[] = [
                'producto' => $producto,
                'cantidad' => (int) ($item['cantidad'] ?? 1),
                'precio_base' => $precioBase,
                'precio_con_descuento' => $this->aplicarDescuentos($precioBase, $descuentos),
            ];
        }

        $dolar = Cotizacion::dolar();

        return view('content.web.webcarrito', compact('productosCarrito', 'usuario', 'descuentos', 'dolar'));
    }

    public function agregar(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|integer',
            'cantidad' => 'required|integer|min:1',
        ]);

        $producto = Producto::where('active', true)->find($validated['producto_id']);
        if (!$producto) {
            return response()->json(['success' => false, 'message' => 'Producto no encontrado'], 404);
        }

        if (is_null($producto->precio_pesos)) {
            return response()->json(['success' => false, 'message' => 'Precio no disponible por el momento.'], 503);
        }

        $usuario = auth('web_usuario')->user();
        $carrito = $this->cargarCarritoPendiente($usuario);
        $cantidad = (int) $validated['cantidad'];
        $precioBase = (float) $producto->precio_pesos;

        if (isset($carrito[$producto->id])) {
            $carrito[$producto->id]['cantidad'] += $cantidad;
        } else {
            $carrito[$producto->id] = [
                'cantidad' => $cantidad,
                'precio_base' => $precioBase,
                'precio_con_descuento' => $this->aplicarDescuentos($precioBase, $this->descuentosDelUsuario($usuario)),
            ];
        }

        session(['carrito' => $carrito]);
        $this->guardarCarritoPendiente($usuario, $carrito);

        return response()->json(['success' => true, 'carrito' => $carrito]);
    }

    public function actualizar(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|integer',
            'cantidad' => 'required|integer|min:0',
        ]);

        $usuario = auth('web_usuario')->user();
        $carrito = $this->cargarCarritoPendiente($usuario);
        $productoId = (int) $validated['producto_id'];
        $cantidad = (int) $validated['cantidad'];

        if (!isset($carrito[$productoId])) {
            return response()->json(['success' => false, 'message' => 'Producto no en carrito'], 404);
        }

        if ($cantidad === 0) {
            unset($carrito[$productoId]);
        } else {
            $carrito[$productoId]['cantidad'] = $cantidad;
        }

        session(['carrito' => $carrito]);
        $this->guardarCarritoPendiente($usuario, $carrito);

        return response()->json(['success' => true, 'carrito' => $carrito]);
    }

    public function eliminar(Request $request)
    {
        $validated = $request->validate(['producto_id' => 'required|integer']);
        $usuario = auth('web_usuario')->user();
        $carrito = $this->cargarCarritoPendiente($usuario);
        unset($carrito[(int) $validated['producto_id']]);

        session(['carrito' => $carrito]);
        $this->guardarCarritoPendiente($usuario, $carrito);

        return response()->json(['success' => true, 'carrito' => $carrito]);
    }

    public function vaciar()
    {
        $usuario = auth('web_usuario')->user();
        session()->forget('carrito');
        $this->guardarCarritoPendiente($usuario, []);

        return response()->json(['success' => true]);
    }

    public function completar(Request $request)
    {
        $request->validate([
            'entrega' => 'required|in:mercado_flex,transporte_terminal,retiro_local',
            'forma_pago' => 'required|in:mercado_pago,transferencia,efectivo',
            'mensaje' => 'nullable|string|max:2000',
            'archivo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $usuario = auth('web_usuario')->user();
        if (!$usuario) {
            return response()->json(['success' => false, 'message' => 'Usuario no autenticado.'], 401);
        }

        if (empty($usuario->email)) {
            return response()->json(['success' => false, 'message' => 'Tu usuario no tiene un email configurado.'], 422);
        }

        $carrito = $this->cargarCarritoPendiente($usuario);
        if (empty($carrito)) {
            return response()->json(['success' => false, 'message' => 'El carrito esta vacio.'], 422);
        }

        if (is_null(Cotizacion::dolar())) {
            return response()->json(['success' => false, 'message' => 'No se pudo calcular los precios en pesos. Intenta nuevamente más tarde.'], 503);
        }

        $armado = $this->armarItemsDelCarrito($carrito, $this->descuentosDelUsuario($usuario));
        $items = $armado['items'];
        $totales = $armado['totales'];
        $puntos = $armado['puntos'];

        if (empty($items)) {
            return response()->json(['success' => false, 'message' => 'No se encontraron productos validos en el carrito.'], 422);
        }

        $pedido = [
            'cliente' => [
                'usuario' => $usuario->usuario ?? '-',
                'email' => $usuario->email,
                'empresa' => $usuario->empresa ?? '-',
                'cuit' => $usuario->cuit ?? '-',
                'telefono' => $usuario->telefono ?? '-',
            ],
            'entrega' => $request->entrega,
            'entrega_label' => ['mercado_flex' => 'Mercado Flex', 'transporte_terminal' => 'Transporte a la terminal', 'retiro_local' => 'Retiro en local'][$request->entrega],
            'forma_pago' => $request->forma_pago,
            'forma_pago_label' => ['mercado_pago' => 'Mercado Pago', 'transferencia' => 'Transferencia bancaria', 'efectivo' => 'Efectivo (únicamente en local)'][$request->forma_pago],
            'mensaje' => $request->mensaje ?? '',
            'items' => $items,
            'totales' => $totales,
        ];

        try {
            DB::transaction(function () use ($usuario, $request, $totales, $items, $carrito, $puntos) {
                $pedidoModel = Pedido::create([
                    'usuario_id' => $usuario->id,
                    'cliente_id' => $usuario->id,
                    'estado' => 'pendiente',
                    'entrega' => $request->entrega,
                    'forma_pago' => $request->forma_pago,
                    'mensaje' => $request->mensaje ?? '',
                    'subtotal' => $totales['subtotal'],
                    'iva' => $totales['iva'],
                    'total' => $totales['total'],
                    'puntos_ganados' => $puntos,
                    'totales' => $totales,
                ]);

                foreach ($items as $item) {
                    $pedidoModel->items()->create([
                        'producto_id' => $item['producto_id'],
                        'codigo' => $item['codigo'],
                        'nombre' => $item['nombre'],
                        'cantidad' => $item['cantidad'],
                        'precio_base' => $item['precio_base'],
                        'precio_con_descuento' => $item['precio_con_descuento'],
                        'subtotal' => $item['subtotal'],
                        'carrito_item' => $carrito[$item['producto_id']] ?? null,
                    ]);
                }

                // el cliente suma los puntos por compra de lo que lleva
                if ($puntos > 0) {
                    $usuario->increment('puntos', $puntos);
                }
            });
        } catch (Throwable $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'No se pudo registrar el pedido.'], 500);
        }

        session()->forget('carrito');
        $this->guardarCarritoPendiente($usuario, []);

        $ownerEmail = config('mail.orders_recipient') ?: config('mail.from.address');
        $erroresCorreo = [];
        try {
            Mail::to($usuario->email)->send(new PedidoConfirmacionMail($pedido, 'cliente', $request->file('archivo')));
        } catch (Throwable $e) {
            report($e);
            $erroresCorreo[] = 'cliente';
        }
        try {
            Mail::to($ownerEmail)->send(new PedidoConfirmacionMail($pedido, 'duenos', $request->file('archivo')));
        } catch (Throwable $e) {
            report($e);
            $erroresCorreo[] = 'administracion';
        }

        return response()->json([
            'success' => true,
            'puntos_ganados' => $puntos,
            'puntos_total' => (int) $usuario->fresh()->puntos,
            'message' => empty($erroresCorreo)
                ? 'Pedido realizado con exito.'
                : 'Pedido realizado, pero no se pudo enviar uno de los emails.',
        ]);
    }

    public function historico()
    {
        $usuario = auth('web_usuario')->user();
        $pedidos = Pedido::with('items')->where('cliente_id', $usuario->id)->latest()->paginate(10);
        return view('content.web.webpedidos', compact('pedidos', 'usuario'));
    }

    public function pdf(Pedido $pedido)
    {
        $usuario = auth('web_usuario')->user();
        abort_unless($usuario && (int) $pedido->cliente_id === (int) $usuario->id, 403);
        $pedido->load(['usuario', 'cliente', 'items']);
        return Pdf::loadView('content.pedidos.pdf', compact('pedido'))
            ->stream('pedido-' . str_pad($pedido->id, 8, '0', STR_PAD_LEFT) . '.pdf');
    }

    public function recomprar(Pedido $pedido)
    {
        $usuario = auth('web_usuario')->user();
        abort_unless($usuario && (int) $pedido->cliente_id === (int) $usuario->id, 403);

        $carrito = [];
        foreach ($pedido->items as $item) {
            if ($item->producto_id && $item->cantidad > 0) {
                $carrito[$item->producto_id] = [
                    'cantidad' => (int) $item->cantidad,
                    'precio_base' => $item->precio_base,
                    'precio_con_descuento' => $item->precio_con_descuento,
                ];
            }
        }

        session(['carrito' => $carrito]);
        $this->guardarCarritoPendiente($usuario, $carrito);
        return redirect()->route('web-carrito');
    }

    public function adminIndex(Request $request)
    {
        $buscar = trim((string) $request->get('q', ''));
        $pedidos = Pedido::with(['cliente', 'usuario', 'items'])
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($query) use ($buscar) {
                    if (ctype_digit($buscar)) {
                        $query->orWhere('id', (int) $buscar);
                    }
                    $query->orWhereHas('cliente', fn ($cliente) => $cliente
                        ->where('usuario', 'like', "%{$buscar}%")
                        ->orWhere('email', 'like', "%{$buscar}%")
                        ->orWhere('empresa', 'like', "%{$buscar}%")
                        ->orWhere('cuit', 'like', "%{$buscar}%"))
                        ->orWhereHas('items', fn ($items) => $items
                            ->where('codigo', 'like', "%{$buscar}%")
                            ->orWhere('nombre', 'like', "%{$buscar}%"));
                });
            })
            ->latest()->paginate(15)->withQueryString();

        return view('content.admin.historico', compact('pedidos'));
    }

    public function adminShow(Pedido $pedido)
    {
        $pedido->load(['cliente', 'usuario', 'items']);
        return view('content.admin.historico-editor', compact('pedido'));
    }

    public function adminUpdate(Request $request)
    {
        $validated = $request->validate([
            'pedido_id' => 'required|exists:pedidos,id',
            'fecha_entrega' => 'nullable|date',
            'guia_transporte' => 'nullable|string|max:100',
            'estado' => 'required|in:' . implode(',', array_keys(Pedido::ESTADOS)),
        ]);

        Pedido::findOrFail($validated['pedido_id'])->update([
            'fecha_entrega' => $validated['fecha_entrega'] ?? null,
            'guia_transporte' => $validated['guia_transporte'] ?? null,
            'estado' => $validated['estado'],
        ]);

        return redirect()->route('adm.pedidos-historico-editor', $validated['pedido_id'])
            ->with('success', 'Pedido actualizado correctamente.');
    }

    public function adminDestroy(Pedido $pedido)
    {
        $pedido->delete();
        return redirect()->route('adm.pedidos-historico')->with('success', 'Pedido eliminado correctamente.');
    }

    public function adminSwitch(Pedido $pedido)
    {
        // Avanza al siguiente estado y, tras el último, vuelve al primero
        $estados = array_keys(Pedido::ESTADOS);
        $actual = array_search($pedido->estado, $estados, true);
        $siguiente = $estados[$actual === false ? 0 : ($actual + 1) % count($estados)];
        $pedido->update(['estado' => $siguiente]);
        return back()->with('success', 'Estado actualizado correctamente.');
    }
}
