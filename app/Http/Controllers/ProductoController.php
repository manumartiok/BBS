<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\GestionaArchivos;
use App\Models\Producto;
use Illuminate\Http\Request;
use App\Models\Marca;
use Illuminate\Support\Facades\Storage;
use App\Models\ProductoFoto;

class ProductoController extends Controller
{
    use GestionaArchivos;

    // Subcampos de la lista "caracteristicas" (se guarda como json)
    private const CARACTERISTICAS = ['nombre' => 'string', 'valor' => 'string'];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Eager load marca to avoid N+1 and allow searching by marca
        $query = Producto::with('marca')->orderBy('orden');

        if ($request->filled('q')) {
            $term = $request->get('q');

            // Buscar por nombre, código o por texto de la marca
            $query->where(function($sub) use ($term) {
                $sub->where('nombre', 'like', "%{$term}%")
                    ->orWhere('codigo', 'like', "%{$term}%")
                    ->orWhereHas('marca', function($m) use ($term) {
                        $m->where('nombre', 'like', "%{$term}%");
                    });
            });
        }

        $productos = $query->paginate(25)->withQueryString();

        return view('content.admin.producto', [
            'productos' => $productos
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $marcas = Marca::orderBy('orden')->get();

        return view('content.admin.producto-creador', [
            'producto' => new Producto(),
            'marcas' => $marcas
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validar($request);

        $producto = new Producto();

        $this->completar($producto, $request);

        $producto->save();
        return redirect()->route('adm.producto-editor', $producto->id)
            ->with('success', 'Producto creado correctamente. Ahora puedes agregar fotos.');
    }

    /**
     * Display the specified resource.
     */
    public function show($producto_id)
    {
        $producto = Producto::findOrFail($producto_id);
        $marcas = Marca::orderBy('orden')->get();

        return view('content.admin.producto-editor', [
            'producto' => $producto,
            'marcas' => $marcas
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Producto $producto)
    {
        // Not used - using show() to load editor with id
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $this->validar($request);

        $producto = Producto::findOrFail($request->producto_id);

        $this->completar($producto, $request);

        $producto->save();

        return redirect()->route('adm.producto')
            ->with('success', 'Producto actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($producto_id)
    {
        $producto = Producto::find($producto_id);

        if ($producto) {
            $this->borrarArchivosDe($producto, ['foto']);

            $producto->delete();
        }

        return redirect()->route('adm.producto')
            ->with('success', 'Producto eliminado correctamente.');
    }

    /**
     * Activar / desactivar.
     */
    public function switch($producto_id)
    {
        $producto = Producto::findOrFail($producto_id);

        $producto->active = !$producto->active;

        $producto->save();

        return redirect()->route('adm.producto')
            ->with('success', 'Estado actualizado correctamente.');
    }

    /**
     * Alternar destacado.
     */
    public function destacado($producto_id)
    {
        $producto = Producto::findOrFail($producto_id);

        $producto->destacado = !$producto->destacado;

        $producto->save();

        return redirect()->route('adm.producto')
            ->with('success', 'Destacado actualizado correctamente.');
    }

    /**
     * Alternar oferta.
     */
    public function oferta($producto_id)
    {
        $producto = Producto::findOrFail($producto_id);

        $producto->oferta = !$producto->oferta;

        $producto->save();

        return redirect()->route('adm.producto')
            ->with('success', 'Oferta actualizada correctamente.');
    }

    /**
     * Alternar más vendido.
     */
    public function masVendido($producto_id)
    {
        $producto = Producto::findOrFail($producto_id);

        $producto->mas_vendido = !$producto->mas_vendido;

        $producto->save();

        return redirect()->route('adm.producto')
            ->with('success', 'Más vendido actualizado correctamente.');
    }

    /**
     * Alternar descubre más.
     */
    public function descubreMas($producto_id)
    {
        $producto = Producto::findOrFail($producto_id);

        $producto->descubre_mas = !$producto->descubre_mas;

        $producto->save();

        return redirect()->route('adm.producto')
            ->with('success', 'Descubre más actualizado correctamente.');
    }

    /**
     * Reordenar.
     */
    public function reordenar(Request $request)
    {
        foreach ($request->orden as $item) {

            Producto::where('id', $item['id'])
                ->update([
                    'orden' => $item['orden']
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Orden actualizado correctamente'
        ]);
    }

    /**
     * Importar productos desde CSV/Excel.
     * Mapea columnas: nombre, codigo, stock_fisico, stock_transito, descripcion, marca, precio, puntos, puntos_compra
     * Si ya existe un producto con ese nombre, se actualiza.
     */
    public function import(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:csv,txt,xls,xlsx,ods',
        ]);

        $path = $request->file('archivo')->getRealPath();
        $extension = strtolower($request->file('archivo')->getClientOriginalExtension());

        $rows = [];

        if (in_array($extension, ['xls', 'xlsx', 'ods'])) {
            try {
                if (!class_exists('\PhpOffice\\PhpSpreadsheet\\IOFactory')) {
                    return redirect()->back()->with('error', 'PhpSpreadsheet no está instalado. Instala phpoffice/phpspreadsheet para importar XLS/XLSX.');
                }

                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
                $sheet = $spreadsheet->getActiveSheet();
                $data = $sheet->toArray(null, true, true, true);

                // Primera fila cabecera
                $header = [];
                if (isset($data[1])) {
                    foreach ($data[1] as $col => $val) {
                        $header[$col] = strtolower(trim((string)$val));
                    }
                }

                for ($i = 2; $i <= count($data); $i++) {
                    $row = [];
                    foreach ($data[$i] as $col => $val) {
                        if (isset($header[$col])) $row[$header[$col]] = $val;
                    }
                    if (count($row)) $rows[] = $row;
                }
            } catch (\Throwable $e) {
                return redirect()->back()->with('error', 'Error leyendo el archivo: ' . $e->getMessage());
            }
        } else {
            // CSV
            if (($handle = fopen($path, 'r')) !== false) {
                $header = null;
                while (($data = fgetcsv($handle, 0, ',')) !== false) {
                    if (!$header) {
                        $header = array_map(function ($h) { return strtolower(trim($h)); }, $data);
                    } else {
                        $rows[] = array_combine($header, $data);
                    }
                }
                fclose($handle);
            }
        }

        if (empty($rows)) {
            return redirect()->back()->with('error', 'No se encontraron filas para procesar.');
        }

        $creados = 0;
        $actualizados = 0;
        $errores = [];

        foreach ($rows as $index => $r) {
            try {
                $nombre = trim((string) ($r['nombre'] ?? ''));
                $codigo = $r['codigo'] ?? null;
                $stockFisico = $r['stock_fisico'] ?? null;
                $stockTransito = $r['stock_transito'] ?? null;
                $descripcion = $r['descripcion'] ?? null;
                $marcaNombre = $r['marca'] ?? null;
                $precioRaw = $r['precio'] ?? null;
                $puntosRaw = $r['puntos'] ?? null;
                $puntosCompraRaw = $r['puntos_compra'] ?? null;

                if ($nombre === '') {
                    $errores[] = "Fila " . ($index + 2) . ": nombre vacío";
                    continue;
                }

                $producto = Producto::where('nombre', $nombre)->first();
                if (!$producto) {
                    $producto = new Producto();
                    $producto->orden = Producto::count() + 1;
                    $creados++;
                } else {
                    $actualizados++;
                }

                $producto->nombre = $nombre;
                if (!empty($codigo)) $producto->codigo = trim((string) $codigo);
                if ($stockFisico !== null && $stockFisico !== '') $producto->stock_fisico = $stockFisico;
                if ($stockTransito !== null && $stockTransito !== '') $producto->stock_transito = $stockTransito;
                if (!empty($descripcion)) $producto->descripcion = $descripcion;

                if (!empty($precioRaw)) {
                    $precio = floatval(str_replace([',', ' '], ['.', ''], $precioRaw));
                    $producto->precio = $precio;
                }

                // Puntos: solo se aceptan números enteros
                if ($puntosRaw !== null && $puntosRaw !== '') {
                    $puntos = trim((string) $puntosRaw);
                    if (!ctype_digit($puntos)) {
                        throw new \InvalidArgumentException('puntos debe ser un número entero');
                    }
                    $producto->puntos = (int) $puntos;
                }

                if ($puntosCompraRaw !== null && $puntosCompraRaw !== '') {
                    $puntosCompra = trim((string) $puntosCompraRaw);
                    if (!ctype_digit($puntosCompra)) {
                        throw new \InvalidArgumentException('puntos_compra debe ser un número entero');
                    }
                    $producto->puntos_compra = (int) $puntosCompra;
                }

                if (!empty($marcaNombre)) {
                    $marca = Marca::where('nombre', $marcaNombre)->first();

                    if ($marca) {
                        $producto->marca_id = $marca->id;
                    }
                }

                $producto->save();

            } catch (\Throwable $e) {
                $errores[] = "Fila " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        $mensaje = "Importación completada: {$creados} creados, {$actualizados} actualizados";
        if (count($errores)) $mensaje .= " (" . count($errores) . " errores)";

        return redirect()->route('adm.producto')->with('success', $mensaje);
    }

    /**
     * Subir fotos para la galería del producto (AJAX)
     */
    public function fotosStore(Request $request)
    {
        $request->validate([
            'fotos' => 'required',
            'fotos.*' => 'image|max:2048',
            'productos_id' => 'required|integer',
        ]);

        $productos_id = $request->productos_id;
        $ordenInicial = intval($request->orden) ?? 0;

        $saved = [];
        foreach ($request->file('fotos') as $file) {
            $name = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('producto_fotos', $name, 'public');

            $foto = ProductoFoto::create([
                'productos_id' => $productos_id,
                'orden' => $ordenInicial,
                'foto' => Storage::url($path),
                'active' => true,
            ]);

            $saved[] = [
                'id' => $foto->id,
                'foto' => $foto->foto,
                'orden' => $foto->orden,
            ];
            $ordenInicial++;
        }

        return response()->json([
            'success' => true,
            'message' => 'Fotos subidas correctamente',
            'fotos' => $saved
        ]);
    }

    /**
     * Reordenar fotos (AJAX)
     */
    public function fotosReordenar(Request $request)
    {
        foreach ($request->orden as $item) {
            ProductoFoto::where('id', $item['id'])->update(['orden' => $item['orden']]);
        }

        return response()->json(['success' => true, 'message' => 'Orden de fotos actualizado']);
    }

    /**
     * Eliminar foto de la galería
     */
    public function fotosDestroy($foto_id)
    {
        $foto = ProductoFoto::findOrFail($foto_id);

        if (!empty($foto->foto)) {
            $filePath = str_replace('/storage/', '', $foto->foto);
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
        }

        $foto->delete();

        return response()->json(['success' => true, 'message' => 'Foto eliminada correctamente']);
    }

    /**
     * Alternar activo/inactivo de una foto
     */
    public function fotosSwitch($foto_id)
    {
        $foto = ProductoFoto::findOrFail($foto_id);

        $foto->active = !$foto->active;
        $foto->save();

        return redirect()->route('adm.producto-editor', $foto->productos_id)
            ->with('success', 'Estado de la foto actualizado correctamente.');
    }

    /**
     * Asignar fotos a productos por nombre de archivo (sin extensión = nombre del producto).
     */
    public function fotosMasivas(Request $request)
    {
        $request->validate([
            'fotos'   => 'required|array',
            'fotos.*' => 'image|max:4096',
        ]);

        $asignadas  = 0;
        $sinProducto = 0;
        $errores    = 0;

        // Mapa: nombre (lowercase) => producto
        $productos = Producto::all()
            ->filter(fn ($p) => trim((string) $p->nombre) !== '')
            ->keyBy(fn ($p) => strtolower(trim($p->nombre)));

        // Nombres que recibieron foto en esta carga
        $nombresConFoto = [];

        foreach ($request->file('fotos') as $file) {
            try {
                // Nombre del archivo sin extensión → nombre buscado
                $nombreBuscado = strtolower(trim(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)));

                if (!isset($productos[$nombreBuscado])) {
                    $sinProducto++;
                    continue;
                }

                $producto = $productos[$nombreBuscado];

                // Eliminar foto anterior si existe y guardar la nueva
                $this->borrarArchivo($producto->foto);
                $producto->foto = $this->subirArchivo($file, 'productos');
                $producto->save();

                $nombresConFoto[] = $nombreBuscado;
                $asignadas++;

            } catch (\Throwable $e) {
                $errores++;
            }
        }

        // Productos sin foto coincidente → asignar default.png
        $defaultAsignados = 0;
        foreach ($productos as $nombre => $producto) {
            if (!in_array($nombre, $nombresConFoto)) {
                // Solo actualizar si aún no tiene foto
                if (empty($producto->foto)) {
                    $producto->foto = '/default.png';
                    $producto->save();
                    $defaultAsignados++;
                }
            }
        }

        return redirect()->route('adm.producto')->with('fotos_masivas', [
            'asignadas'   => $asignadas,
            'default'     => $defaultAsignados,
            'sin_producto' => $sinProducto,
            'errores'     => $errores,
        ]);
    }

    private function validar(Request $request): void
    {
        $request->validate([
            'foto' => 'nullable|image|max:2048',
            'codigo' => 'nullable|string|max:255',
            'stock_fisico' => 'nullable|string|max:255',
            'stock_transito' => 'nullable|string|max:255',
            'precio' => 'nullable|numeric',
            'puntos' => 'nullable|integer|min:0',
            'puntos_compra' => 'nullable|integer|min:0',
        ]);
    }

    private function completar(Producto $producto, Request $request): void
    {
        $producto->marca_id = $request->input('marca_id') ?: null;
        $producto->orden = $request->input('orden');
        $producto->nombre = $request->input('nombre');
        $producto->codigo = $request->input('codigo');
        $producto->stock_fisico = $request->input('stock_fisico');
        $producto->stock_transito = $request->input('stock_transito');
        $producto->descripcion = $request->input('descripcion');
        $producto->precio = $request->filled('precio') ? $request->input('precio') : null;
        $producto->puntos = $request->filled('puntos') ? $request->input('puntos') : null;
        $producto->puntos_compra = $request->filled('puntos_compra') ? $request->input('puntos_compra') : null;
        $producto->caracteristicas = $this->procesarLista($request, 'caracteristicas', self::CARACTERISTICAS, $producto->caracteristicas, 'productos');
        $producto->foto = $this->procesarArchivo($request, 'foto', $producto->foto, 'productos');

        $producto->destacado = $request->boolean('destacado');
        $producto->oferta = $request->boolean('oferta');
        $producto->mas_vendido = $request->boolean('mas_vendido');
        $producto->descubre_mas = $request->boolean('descubre_mas');
        $producto->active = $request->has('active') ? $request->boolean('active') : ($producto->active ?? true);
    }
}
