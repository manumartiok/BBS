<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManejaCarrito;
use App\Models\Cotizacion;
use App\Models\Presupuesto;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class PresupuestoController extends Controller
{
    use ManejaCarrito;

    private const ENTREGAS = ['mercado_flex' => 'Mercado Flex', 'transporte_terminal' => 'Transporte a la terminal', 'retiro_local' => 'Retiro en local'];
    private const FORMAS_PAGO = ['mercado_pago' => 'Mercado Pago', 'transferencia' => 'Transferencia bancaria', 'efectivo' => 'Efectivo (únicamente en local)'];

    public function index()
    {
        $usuario = auth('web_usuario')->user();
        $presupuestos = Presupuesto::where('usuario_id', $usuario->id)->latest()->paginate(10);

        return view('content.web.webpresupuestos', compact('presupuestos', 'usuario'));
    }

    // Crea el presupuesto con lo que hay en el carrito (el carrito no se vacía)
    public function store(Request $request)
    {
        $request->validate([
            'entrega' => 'required|in:' . implode(',', array_keys(self::ENTREGAS)),
            'forma_pago' => 'required|in:' . implode(',', array_keys(self::FORMAS_PAGO)),
            'mensaje' => 'nullable|string|max:2000',
        ]);

        $usuario = auth('web_usuario')->user();
        $carrito = $this->cargarCarritoPendiente($usuario);
        if (empty($carrito)) {
            return response()->json(['success' => false, 'message' => 'El carrito esta vacio.'], 422);
        }

        if (is_null(Cotizacion::dolar())) {
            return response()->json(['success' => false, 'message' => 'No se pudo calcular los precios en pesos. Intenta nuevamente más tarde.'], 503);
        }

        $armado = $this->armarItemsDelCarrito($carrito, $this->descuentosDelUsuario($usuario));
        if (empty($armado['items'])) {
            return response()->json(['success' => false, 'message' => 'No se encontraron productos validos en el carrito.'], 422);
        }

        try {
            $presupuesto = DB::transaction(function () use ($usuario, $request, $armado) {
                $presupuesto = Presupuesto::create([
                    'usuario_id' => $usuario->id,
                    'entrega' => $request->entrega,
                    'forma_pago' => $request->forma_pago,
                    'mensaje' => $request->mensaje ?? '',
                    'fecha_vencimiento' => now()->addDays(Presupuesto::VIGENCIA_DIAS)->toDateString(),
                    'subtotal' => $armado['totales']['subtotal'],
                    'iva' => $armado['totales']['iva'],
                    'total' => $armado['totales']['total'],
                    // se guarda el dólar de ese momento para mostrarlo en el presupuesto
                    'totales' => $armado['totales'] + ['dolar' => Cotizacion::dolar()],
                ]);

                $presupuesto->items()->createMany($armado['items']);

                return $presupuesto;
            });
        } catch (Throwable $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'No se pudo registrar el presupuesto.'], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Presupuesto realizado con exito.',
            'redirect' => route('web-presupuestos'),
            'id' => $presupuesto->id,
        ]);
    }

    // Se abre en una pestaña nueva
    public function ver(Presupuesto $presupuesto)
    {
        $this->propietario($presupuesto);
        $presupuesto->load('items.producto');

        $totales = $presupuesto->totales ?: [];
        $dolar = $totales['dolar'] ?? Cotizacion::dolar();

        return view('content.presupuestos.ver', compact('presupuesto', 'totales', 'dolar'));
    }

    public function descargar(Presupuesto $presupuesto)
    {
        return $this->pdf($presupuesto)->download($this->nombreArchivo($presupuesto));
    }

    // Reemplaza el carrito por los productos del presupuesto para completar el pedido
    public function pasarAPedido(Presupuesto $presupuesto)
    {
        $usuario = $this->propietario($presupuesto);

        $carrito = [];
        foreach ($presupuesto->items as $item) {
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
        $presupuestos = Presupuesto::with(['usuario', 'items'])
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($query) use ($buscar) {
                    if (ctype_digit($buscar)) {
                        $query->orWhere('id', (int) $buscar);
                    }
                    $query->orWhereHas('usuario', fn ($usuario) => $usuario
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

        return view('content.admin.presupuestos-historico', compact('presupuestos'));
    }

    public function adminShow(Presupuesto $presupuesto)
    {
        $presupuesto->load(['usuario', 'items']);

        return view('content.admin.presupuestos-historico-editor', compact('presupuesto'));
    }

    // Solo se puede cambiar el vencimiento: el estado se calcula a partir de esa fecha
    public function adminUpdate(Request $request)
    {
        $validated = $request->validate([
            'presupuesto_id' => 'required|exists:presupuestos,id',
            'fecha_vencimiento' => 'required|date',
        ]);

        Presupuesto::findOrFail($validated['presupuesto_id'])->update([
            'fecha_vencimiento' => $validated['fecha_vencimiento'],
        ]);

        return redirect()->route('adm.presupuestos-historico-editor', $validated['presupuesto_id'])
            ->with('success', 'Presupuesto actualizado correctamente.');
    }

    public function adminDestroy(Presupuesto $presupuesto)
    {
        $presupuesto->delete();

        return redirect()->route('adm.presupuestos-historico')->with('success', 'Presupuesto eliminado correctamente.');
    }

    private function propietario(Presupuesto $presupuesto)
    {
        $usuario = auth('web_usuario')->user();
        abort_unless($usuario && (int) $presupuesto->usuario_id === (int) $usuario->id, 403);

        return $usuario;
    }

    private function pdf(Presupuesto $presupuesto)
    {
        $this->propietario($presupuesto);
        $presupuesto->load(['usuario', 'items']);

        return Pdf::loadView('content.presupuestos.pdf', [
            'presupuesto' => $presupuesto,
            'entrega' => self::ENTREGAS[$presupuesto->entrega] ?? $presupuesto->entrega,
            'formaPago' => self::FORMAS_PAGO[$presupuesto->forma_pago] ?? $presupuesto->forma_pago,
        ]);
    }

    private function nombreArchivo(Presupuesto $presupuesto): string
    {
        return 'presupuesto-' . str_pad($presupuesto->id, 8, '0', STR_PAD_LEFT) . '.pdf';
    }
}
