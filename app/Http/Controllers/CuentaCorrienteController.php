<?php

namespace App\Http\Controllers;

use App\Models\Comprobante;
use App\Services\ContabiliumService;
use Illuminate\Http\Request;

class CuentaCorrienteController extends Controller
{
    // Cuenta corriente del cliente logueado (comprobantes sincronizados de Contabilium)
    public function index()
    {
        $clienteId = auth('web_usuario')->user()->contabilium_id;

        // Sin contabilium_id el cliente no está vinculado con Contabilium: no ve comprobantes
        $query = Comprobante::where('cliente_id', $clienteId ?? 0);

        $saldoTotal = (clone $query)->sum('saldo');
        $saldoVencido = (clone $query)->whereDate('fecha_vencimiento', '<', today())->sum('saldo');

        $comprobantes = $query->orderByDesc('fecha_emision')
            ->orderByDesc('contabilium_id')
            ->paginate(20);

        return view('content.web.webcuenta-corriente', compact('comprobantes', 'saldoTotal', 'saldoVencido'));
    }

    // Descarga el PDF del comprobante desde Contabilium
    public function pdf(Comprobante $comprobante, ContabiliumService $contabilium)
    {
        $clienteId = auth('web_usuario')->user()->contabilium_id;

        abort_if(!$clienteId || $comprobante->cliente_id != $clienteId, 404);

        return $this->descargar($comprobante, $contabilium);
    }

    // ADMIN: cuentas corrientes de todos los clientes
    // ?q= busca por razón social, número, tipo, CUIT o email; ?estado=pendientes|vencidos
    public function adminIndex(Request $request)
    {
        $buscar = trim((string) $request->get('q', ''));
        $estado = $request->get('estado');

        $query = Comprobante::query()
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($query) use ($buscar) {
                    $query->where('razon_social', 'like', "%{$buscar}%")
                        ->orWhere('numero', 'like', "%{$buscar}%")
                        ->orWhere('tipo', $buscar)
                        ->orWhereHas('usuario', fn ($usuario) => $usuario
                            ->where('cuit', 'like', "%{$buscar}%")
                            ->orWhere('email', 'like', "%{$buscar}%"));
                });
            })
            ->when($estado === 'pendientes', fn ($query) => $query->where('saldo', '!=', 0))
            ->when($estado === 'vencidos', fn ($query) => $query->where('saldo', '!=', 0)
                ->whereDate('fecha_vencimiento', '<', today()));

        $saldoTotal = (clone $query)->sum('saldo');
        $saldoVencido = (clone $query)->whereDate('fecha_vencimiento', '<', today())->sum('saldo');
        $ultimaSincronizacion = Comprobante::max('updated_at');

        $comprobantes = $query->with('usuario')
            ->orderByDesc('fecha_emision')
            ->orderByDesc('contabilium_id')
            ->paginate(20)
            ->withQueryString();

        return view('content.admin.cuentas-corrientes', compact('comprobantes', 'saldoTotal', 'saldoVencido', 'ultimaSincronizacion'));
    }

    // ADMIN: PDF de cualquier comprobante
    public function adminPdf(Comprobante $comprobante, ContabiliumService $contabilium)
    {
        return $this->descargar($comprobante, $contabilium);
    }

    private function descargar(Comprobante $comprobante, ContabiliumService $contabilium)
    {
        try {
            $pdf = $contabilium->pdfComprobante($comprobante->contabilium_id);
        } catch (\Throwable $e) {
            return back()->with('error', 'No se pudo descargar el comprobante. Intentá de nuevo más tarde.');
        }

        $nombre = trim($comprobante->tipo . ' ' . $comprobante->numero) . '.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $nombre . '"',
        ]);
    }
}
