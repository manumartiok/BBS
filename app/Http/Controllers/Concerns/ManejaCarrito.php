<?php

namespace App\Http\Controllers\Concerns;

use App\Models\CarritoPendiente;
use App\Models\Producto;

/**
 * Carrito de la zona privada: se guarda en sesión y se respalda en carritos_pendientes.
 * Lo usan los pedidos y los presupuestos, que se arman con la misma lógica de precios.
 */
trait ManejaCarrito
{
    private function descuentosDelUsuario($usuario): array
    {
        return collect(range(1, 5))
            ->map(fn ($i) => (float) data_get($usuario, "descuento{$i}", 0))
            ->filter(fn ($d) => $d > 0)
            ->values()
            ->all();
    }

    private function aplicarDescuentos(float $precio, array $descuentos): float
    {
        foreach ($descuentos as $descuento) {
            $precio *= 1 - ($descuento / 100);
        }

        return $precio;
    }

    private function cargarCarritoPendiente($usuario): array
    {
        if (!$usuario) {
            return session('carrito', []);
        }

        $carrito = session('carrito', []);
        if (!empty($carrito)) {
            $this->guardarCarritoPendiente($usuario, $carrito);
            return $carrito;
        }

        $pendiente = CarritoPendiente::where('usuario_id', $usuario->id)
            ->whereNull('cliente_id')
            ->first();
        $carrito = $pendiente?->items ?: [];

        if (!empty($carrito)) {
            session(['carrito' => $carrito]);
        }

        return $carrito;
    }

    private function guardarCarritoPendiente($usuario, array $carrito): void
    {
        if (!$usuario) {
            return;
        }

        $query = CarritoPendiente::where('usuario_id', $usuario->id)->whereNull('cliente_id');
        if (empty($carrito)) {
            $query->delete();
            return;
        }

        CarritoPendiente::updateOrCreate(
            ['usuario_id' => $usuario->id, 'cliente_id' => null],
            ['items' => $carrito]
        );
    }

    /**
     * Items y totales del carrito con los precios actuales y los descuentos del cliente.
     * Si no queda ningún producto válido, 'items' viene vacío. 'puntos' son los que se ganan con esa compra.
     */
    private function armarItemsDelCarrito(array $carrito, array $descuentos): array
    {
        $items = [];
        $subtotalSinDescuento = 0;
        $subtotalConDescuento = 0;
        $puntos = 0;

        foreach ($carrito as $productoId => $item) {
            $producto = Producto::where('active', true)->find($productoId);
            if (!$producto) {
                continue;
            }

            $cantidad = (int) ($item['cantidad'] ?? 0);
            if ($cantidad < 1) {
                continue;
            }

            $precioBase = (float) $producto->precio_pesos;
            $precioConDescuento = $this->aplicarDescuentos($precioBase, $descuentos);
            $subtotalItem = $precioConDescuento * $cantidad;
            $subtotalSinDescuento += $precioBase * $cantidad;
            $subtotalConDescuento += $subtotalItem;
            $puntos += (int) $producto->puntos_compra * $cantidad;

            $items[] = [
                'producto_id' => $producto->id,
                'codigo' => $producto->codigo ?? '-',
                'nombre' => $producto->nombre ?? '-',
                'precio_base' => $precioBase,
                'precio_con_descuento' => $precioConDescuento,
                'cantidad' => $cantidad,
                'subtotal' => $subtotalItem,
            ];
        }

        // La forma de pago ya no lleva descuento
        $iva = $subtotalConDescuento * 0.21;

        return [
            'items' => $items,
            // puntos que suma el cliente al comprar: "puntos por compra" de cada producto x cantidad
            'puntos' => $puntos,
            'totales' => [
                'subtotal_sin_descuento' => $subtotalSinDescuento,
                'descuento_items' => $subtotalSinDescuento - $subtotalConDescuento,
                'descuento_forma_pago_porcentaje' => 0,
                'descuento_forma_pago' => 0,
                'subtotal' => $subtotalConDescuento,
                'iva' => $iva,
                'total' => $subtotalConDescuento + $iva,
            ],
        ];
    }
}
