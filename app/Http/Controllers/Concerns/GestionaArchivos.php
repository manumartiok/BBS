<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Subida y borrado de archivos (imágenes, PDFs, videos) en el disco "public".
 * Los archivos se guardan como URL: /storage/carpeta/archivo.ext
 */
trait GestionaArchivos
{
    protected function subirArchivo(UploadedFile $file, string $carpeta): string
    {
        $nombre = time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($carpeta, $nombre, 'public');

        return Storage::url($path);
    }

    protected function borrarArchivo(?string $url): void
    {
        if (empty($url)) {
            return;
        }

        $path = Str::after($url, '/storage/');

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Reemplaza (o quita) el archivo de un campo.
     * Devuelve la URL que hay que guardar en el modelo.
     */
    protected function procesarArchivo(Request $request, string $campo, ?string $actual, string $carpeta): ?string
    {
        if ($request->hasFile($campo)) {
            $this->borrarArchivo($actual);
            return $this->subirArchivo($request->file($campo), $carpeta);
        }

        if ($request->boolean('quitar_' . $campo)) {
            $this->borrarArchivo($actual);
            return null;
        }

        return $actual;
    }

    /**
     * Procesa un campo de tipo lista (filas repetibles guardadas como json).
     *
     * @param  array  $subcampos  ['nombre' => 'string', 'foto' => 'imagen', ...]
     * @param  array|null  $anterior  valor actual de la lista (para borrar archivos que ya no se usan)
     */
    protected function procesarLista(Request $request, string $campo, array $subcampos, ?array $anterior, string $carpeta): array
    {
        $tiposArchivo = ['imagen', 'archivo', 'video'];
        $filas = $request->input($campo, []);
        $archivos = $request->file($campo, []);
        $resultado = [];

        foreach ($filas as $i => $fila) {
            $nueva = [];

            foreach ($subcampos as $sub => $tipo) {
                if (in_array($tipo, $tiposArchivo)) {
                    $url = $fila[$sub . '_actual'] ?? null;
                    $subido = $archivos[$i][$sub] ?? null;

                    if ($subido instanceof UploadedFile) {
                        $this->borrarArchivo($url);
                        $url = $this->subirArchivo($subido, $carpeta);
                    }

                    $nueva[$sub] = $url ?: null;
                } else {
                    $nueva[$sub] = $fila[$sub] ?? null;
                }
            }

            $resultado[] = $nueva;
        }

        // Borrar archivos de filas eliminadas
        $usados = [];
        foreach ($resultado as $fila) {
            foreach ($subcampos as $sub => $tipo) {
                if (in_array($tipo, $tiposArchivo) && !empty($fila[$sub])) {
                    $usados[] = $fila[$sub];
                }
            }
        }

        foreach ($anterior ?? [] as $fila) {
            foreach ($subcampos as $sub => $tipo) {
                if (in_array($tipo, $tiposArchivo) && !empty($fila[$sub]) && !in_array($fila[$sub], $usados)) {
                    $this->borrarArchivo($fila[$sub]);
                }
            }
        }

        return $resultado;
    }

    /**
     * Borra todos los archivos de un registro (campos y subcampos de listas).
     *
     * @param  array  $campos  campos de archivo: ['foto', 'archivo']
     * @param  array  $listas  ['items' => ['foto' => 'imagen', 'nombre' => 'string']]
     */
    protected function borrarArchivosDe($modelo, array $campos, array $listas = []): void
    {
        foreach ($campos as $campo) {
            $this->borrarArchivo($modelo->{$campo});
        }

        foreach ($listas as $lista => $subcampos) {
            foreach ($modelo->{$lista} ?? [] as $fila) {
                foreach ($subcampos as $sub => $tipo) {
                    if (in_array($tipo, ['imagen', 'archivo', 'video'])) {
                        $this->borrarArchivo($fila[$sub] ?? null);
                    }
                }
            }
        }
    }
}
