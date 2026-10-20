<?php

namespace App\Console\Commands;

use App\Support\Idiomas;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Genera un módulo completo del administrador.
 * Ver MANUAL-MODULOS.md en la raíz del proyecto.
 *
 *   php artisan make:modulo Producto --campos="marca:relacion,nombre,codigo,precio,foto,archivo" --orden --destacado
 *   php artisan make:modulo Garantia --campos="titulo,texto,archivo" --unico --banner
 */
class MakeModulo extends Command
{
    protected $signature = 'make:modulo
        {nombre : Nombre del modelo en singular y con mayúscula, ej: Producto}
        {--campos= : Campos separados por coma, ej: "nombre,precio,foto,marca:relacion"}
        {--orden : Listado ordenable con drag & drop}
        {--destacado : Agrega el botón Destacado al listado}
        {--unico : Página única (un solo registro, sin listado)}
        {--banner : Agrega el link Banner de la sección en el sidebar}
        {--sin-traducir= : Sitios con idiomas: campos de texto que NO se traducen, ej: "codigo,sku"}';

    protected $description = 'Genera un módulo del administrador: migración, modelo, controlador, vistas, rutas y sidebar';

    private const TIPOS = ['string', 'text', 'decimal', 'integer', 'boolean', 'date', 'imagen', 'archivo', 'video', 'relacion', 'lista'];
    private const TIPOS_SUBCAMPO = ['string', 'text', 'decimal', 'integer', 'date', 'imagen', 'archivo', 'video'];
    private const TIPOS_ARCHIVO = ['imagen', 'archivo', 'video'];
    private const TIPOS_TRADUCIBLES = ['string', 'text'];
    private const RESERVADOS = ['id', 'orden', 'active', 'destacado', 'created_at', 'updated_at'];

    // Nombres de campo que se reconocen solos cuando no se indica el tipo
    private const INFERIDOS = [
        'foto'        => 'imagen',
        'imagen'      => 'imagen',
        'texto'       => 'text',
        'descripcion' => 'text',
        'precio'      => 'decimal',
        'video'       => 'video',
        'archivo'     => 'archivo',
    ];

    private const INPUT = 'w-full border border-gray-300 rounded px-4 py-2';

    private string $modelo;
    private string $tabla;
    private string $slug;
    private string $seccion;
    private string $titulo;
    private string $tituloPlural;
    private bool $unico;
    private bool $orden;
    private bool $destacado;
    private array $idiomas;
    private bool $multi;
    private array $campos = [];
    private array $sinTraducir = [];

    public function handle(): int
    {
        try {
            $this->preparar();
            $archivos = $this->rutasDeArchivos();
            $this->verificarQueNoExista($archivos);
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        File::ensureDirectoryExists(dirname($archivos['partial'] ?? $archivos['vista']));

        File::put($archivos['migracion'], $this->migracion());
        File::put($archivos['modelo'], $this->modelo());
        File::put($archivos['controlador'], $this->controlador());

        if ($this->unico) {
            File::put($archivos['vista'], $this->vistaUnica());
        } else {
            File::put($archivos['vista'], $this->vistaListado());
            File::put($archivos['creador'], $this->vistaFormulario(false));
            File::put($archivos['editor'], $this->vistaFormulario(true));
            File::put($archivos['partial'], $this->camposFormulario() . "\n");
        }

        $this->agregarRutas();
        $sidebarOk = $this->agregarAlSidebar();

        $this->info("Módulo {$this->modelo} generado" . ($this->unico ? ' (página única)' : ' (listado)') . ($this->multi ? ' con idiomas: ' . implode(', ', $this->idiomas) : '') . '.');
        $this->newLine();
        foreach ($archivos as $ruta) {
            $this->line('  creado:     ' . Str::after($ruta, base_path() . DIRECTORY_SEPARATOR));
        }
        $this->line('  modificado: routes/modulos.php');
        if ($sidebarOk) {
            $this->line('  modificado: resources/views/layouts/sidebar.blade.php');
        }

        $this->newLine();
        $this->line('Siguiente paso: <comment>php artisan migrate</comment>');

        return self::SUCCESS;
    }

    // ------------------------------------------------------------------
    // Preparación
    // ------------------------------------------------------------------

    private function preparar(): void
    {
        $nombre = trim($this->argument('nombre'));

        if (!preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $nombre)) {
            throw new InvalidArgumentException("Nombre inválido: \"{$nombre}\". Usá solo letras y números, ej: Producto");
        }

        $this->modelo = Str::studly($nombre);
        $this->tabla = Str::snake(Str::pluralStudly($this->modelo));
        $this->slug = Str::kebab($this->modelo);
        $this->titulo = Str::headline($this->modelo);
        $this->tituloPlural = Str::headline(Str::pluralStudly($this->modelo));

        $this->unico = (bool) $this->option('unico');
        $this->orden = !$this->unico && $this->option('orden');
        $this->destacado = !$this->unico && $this->option('destacado');
        $this->seccion = $this->unico ? $this->slug : Str::kebab(Str::pluralStudly($this->modelo));

        if ($this->unico && ($this->option('orden') || $this->option('destacado'))) {
            $this->warn('--orden y --destacado no se usan en páginas únicas (--unico): se ignoran.');
        }

        $this->idiomas = Idiomas::todos();
        $this->multi = Idiomas::esMultiple();
        $this->sinTraducir = array_filter(array_map('trim', explode(',', (string) $this->option('sin-traducir'))));

        $this->campos = $this->parsearCampos((string) $this->option('campos'));

        $nombres = [];
        foreach ($this->campos as $c) {
            $nombres[] = $c['nombre'];
            foreach ($c['subcampos'] ?? [] as $s) {
                $nombres[] = $s['nombre'];
            }
        }
        foreach (array_diff($this->sinTraducir, $nombres) as $desconocido) {
            $this->warn("--sin-traducir: \"{$desconocido}\" no es ninguno de los campos, se ignora.");
        }
        if ($this->sinTraducir && !$this->multi) {
            $this->warn('--sin-traducir no hace nada: el sitio tiene un solo idioma (config/sitio.php).');
        }

        if (empty($this->campos)) {
            throw new InvalidArgumentException('Indicá al menos un campo con --campos="..."');
        }
    }

    private function parsearCampos(string $texto): array
    {
        $campos = [];
        $tokens = preg_split('/,(?![^\[]*\])/', $texto);

        foreach ($tokens as $token) {
            $token = trim($token);
            if ($token === '') {
                continue;
            }

            if (!preg_match('/^([a-z][a-z0-9_]*)(?::([a-z]+)(?:\[(.*)\])?)?$/', $token, $m)) {
                throw new InvalidArgumentException("Campo inválido: \"{$token}\". Formato: nombre o nombre:tipo (en minúsculas).");
            }

            $nombre = $m[1];
            $tipo = $m[2] ?? '';
            $tipo = $tipo !== '' ? $tipo : (self::INFERIDOS[$nombre] ?? 'string');

            if (in_array($nombre, self::RESERVADOS)) {
                throw new InvalidArgumentException("\"{$nombre}\" es un campo reservado (se maneja con las opciones del comando).");
            }
            if (!in_array($tipo, self::TIPOS)) {
                throw new InvalidArgumentException("Tipo desconocido \"{$tipo}\" en \"{$token}\". Tipos: " . implode(', ', self::TIPOS));
            }

            $campo = [
                'nombre'     => $nombre,
                'tipo'       => $tipo,
                'columna'    => $nombre,
                'label'      => Str::headline($nombre),
                'traducible' => $this->esTraducible($nombre, $tipo),
            ];

            if ($tipo === 'relacion') {
                $modeloRel = Str::studly($nombre);
                $campo['columna'] = $nombre . '_id';
                $campo['modeloRel'] = $modeloRel;
                $campo['tablaRel'] = Str::snake(Str::pluralStudly($modeloRel));
                $campo['variable'] = Str::camel(Str::pluralStudly($modeloRel));

                if (!File::exists(app_path("Models/{$modeloRel}.php"))) {
                    $this->warn("El modelo {$modeloRel} todavía no existe: crealo antes de migrar (la relación apunta a la tabla {$campo['tablaRel']}).");
                }
            }

            if ($tipo === 'lista') {
                if (empty($m[3])) {
                    throw new InvalidArgumentException("La lista \"{$nombre}\" necesita sus subcampos, ej: {$nombre}:lista[nombre|valor]");
                }
                $campo['subcampos'] = $this->parsearSubcampos($nombre, $m[3]);
            } elseif (!empty($m[3])) {
                throw new InvalidArgumentException("Solo el tipo lista lleva corchetes: \"{$token}\"");
            }

            if (isset($campos[$campo['columna']])) {
                throw new InvalidArgumentException("El campo \"{$nombre}\" está repetido.");
            }

            $campos[$campo['columna']] = $campo;
        }

        return array_values($campos);
    }

    private function parsearSubcampos(string $lista, string $texto): array
    {
        $subcampos = [];

        foreach (explode('|', $texto) as $token) {
            $token = trim($token);
            if (!preg_match('/^([a-z][a-z0-9_]*)(?::([a-z]+))?$/', $token, $m)) {
                throw new InvalidArgumentException("Subcampo inválido \"{$token}\" en la lista \"{$lista}\".");
            }

            $nombre = $m[1];
            $tipo = ($m[2] ?? '') !== '' ? $m[2] : (self::INFERIDOS[$nombre] ?? 'string');

            if (!in_array($tipo, self::TIPOS_SUBCAMPO)) {
                throw new InvalidArgumentException("Tipo \"{$tipo}\" no permitido dentro de una lista. Tipos: " . implode(', ', self::TIPOS_SUBCAMPO));
            }

            $subcampos[$nombre] = [
                'nombre'     => $nombre,
                'tipo'       => $tipo,
                'label'      => Str::headline($nombre),
                'traducible' => $this->esTraducible($nombre, $tipo),
            ];
        }

        return array_values($subcampos);
    }

    private function esTraducible(string $nombre, string $tipo): bool
    {
        return $this->multi
            && in_array($tipo, self::TIPOS_TRADUCIBLES)
            && !in_array($nombre, $this->sinTraducir);
    }

    private function rutasDeArchivos(): array
    {
        $vistas = resource_path('views/content/admin');

        $archivos = [
            'migracion'   => database_path('migrations/' . date('Y_m_d_His') . "_create_{$this->tabla}_table.php"),
            'modelo'      => app_path("Models/{$this->modelo}.php"),
            'controlador' => app_path("Http/Controllers/{$this->modelo}Controller.php"),
            'vista'       => "{$vistas}/{$this->slug}.blade.php",
        ];

        if (!$this->unico) {
            $archivos['creador'] = "{$vistas}/{$this->slug}-creador.blade.php";
            $archivos['editor'] = "{$vistas}/{$this->slug}-editor.blade.php";
            $archivos['partial'] = "{$vistas}/partials/{$this->slug}-campos.blade.php";
        }

        return $archivos;
    }

    private function verificarQueNoExista(array $archivos): void
    {
        foreach ($archivos as $clave => $ruta) {
            if ($clave !== 'migracion' && File::exists($ruta)) {
                throw new InvalidArgumentException('Ya existe: ' . Str::after($ruta, base_path() . DIRECTORY_SEPARATOR) . '. No se generó nada.');
            }
        }

        if (File::glob(database_path("migrations/*_create_{$this->tabla}_table.php"))) {
            throw new InvalidArgumentException("Ya hay una migración que crea la tabla {$this->tabla}. No se generó nada.");
        }

        foreach ([base_path('routes/web.php'), base_path('routes/modulos.php')] as $rutas) {
            if (File::exists($rutas) && str_contains(File::get($rutas), "name('adm.{$this->slug}')")) {
                throw new InvalidArgumentException("Ya existe la ruta adm.{$this->slug}. No se generó nada.");
            }
        }
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function camposDeTipo(array $tipos): array
    {
        return array_values(array_filter($this->campos, fn ($c) => in_array($c['tipo'], $tipos)));
    }

    private function relaciones(): array
    {
        return $this->camposDeTipo(['relacion']);
    }

    private function indentar(string $texto, int $espacios): string
    {
        $pad = str_repeat(' ', $espacios);

        return implode("\n", array_map(fn ($l) => $l === '' ? '' : $pad . $l, explode("\n", $texto)));
    }

    private function exportarArray(array $valores): string
    {
        return '[' . implode(', ', array_map(fn ($v) => "'{$v}'", $valores)) . ']';
    }

    private function subcamposComoArray(array $campo): string
    {
        $partes = array_map(fn ($s) => "'{$s['nombre']}' => '{$s['tipo']}'", $campo['subcampos']);

        return '[' . implode(', ', $partes) . ']';
    }

    // ------------------------------------------------------------------
    // Migración
    // ------------------------------------------------------------------

    private function migracion(): string
    {
        $lineas = ['$table->id();'];

        foreach ($this->relaciones() as $c) {
            $lineas[] = "\$table->foreignId('{$c['columna']}')->nullable()->constrained('{$c['tablaRel']}')->nullOnDelete();";
        }

        if ($this->orden) {
            $lineas[] = "\$table->string('orden')->nullable();";
        }

        foreach ($this->campos as $c) {
            $n = $c['columna'];
            $linea = match ($c['tipo']) {
                'relacion' => null,
                'string'   => $c['traducible'] ? "\$table->json('{$n}')->nullable();" : "\$table->string('{$n}')->nullable();",
                'text'     => $c['traducible'] ? "\$table->json('{$n}')->nullable();" : "\$table->text('{$n}')->nullable();",
                'decimal'  => "\$table->decimal('{$n}', 10, 2)->nullable();",
                'integer'  => "\$table->integer('{$n}')->nullable();",
                'boolean'  => "\$table->boolean('{$n}')->default(false);",
                'date'     => "\$table->date('{$n}')->nullable();",
                'lista'    => "\$table->json('{$n}')->nullable();",
                default    => "\$table->string('{$n}')->nullable();", // imagen, archivo, video
            };
            if ($linea) {
                $lineas[] = $linea;
            }
        }

        if (!$this->unico) {
            $lineas[] = "\$table->boolean('active')->default(true);";
        }
        if ($this->destacado) {
            $lineas[] = "\$table->boolean('destacado')->default(false);";
        }
        $lineas[] = '$table->timestamps();';

        $columnas = $this->indentar(implode("\n", $lineas), 12);

        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('{$this->tabla}', function (Blueprint \$table) {
{$columnas}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('{$this->tabla}');
    }
};

PHP;
    }

    // ------------------------------------------------------------------
    // Modelo
    // ------------------------------------------------------------------

    private function modelo(): string
    {
        $fillable = array_column($this->relaciones(), 'columna');
        if ($this->orden) {
            $fillable[] = 'orden';
        }
        foreach ($this->campos as $c) {
            if ($c['tipo'] !== 'relacion') {
                $fillable[] = $c['columna'];
            }
        }
        if (!$this->unico) {
            $fillable[] = 'active';
        }
        if ($this->destacado) {
            $fillable[] = 'destacado';
        }

        $casts = [];
        foreach ($this->campos as $c) {
            $cast = match ($c['tipo']) {
                'decimal' => 'decimal:2',
                'integer' => 'integer',
                'boolean' => 'boolean',
                'date'    => 'date',
                'lista'   => 'array',
                default   => null,
            };
            if ($cast) {
                $casts[$c['columna']] = $cast;
            }
        }
        if (!$this->unico) {
            $casts['active'] = 'boolean';
        }
        if ($this->destacado) {
            $casts['destacado'] = 'boolean';
        }

        $traducibles = array_column(array_filter($this->campos, fn ($c) => $c['traducible']), 'columna');

        $fillableTxt = $this->indentar(implode("\n", array_map(fn ($f) => "'{$f}',", $fillable)), 8);
        $castsTxt = $this->indentar(implode("\n", array_map(fn ($k, $v) => "'{$k}' => '{$v}',", array_keys($casts), $casts)), 8);
        $traduciblesTxt = $this->exportarArray($traducibles);

        $relaciones = '';
        foreach ($this->relaciones() as $c) {
            $metodo = Str::camel($c['nombre']);
            $relaciones .= <<<PHP


    public function {$metodo}()
    {
        return \$this->belongsTo({$c['modeloRel']}::class);
    }
PHP;
        }

        return <<<PHP
<?php

namespace App\Models;

use App\Models\Concerns\Traducible;
use Illuminate\Database\Eloquent\Model;

class {$this->modelo} extends Model
{
    use Traducible;

    protected \$fillable = [
{$fillableTxt}
    ];

    protected \$casts = [
{$castsTxt}
    ];

    // Campos que se guardan por idioma (según config/sitio.php)
    protected array \$traducibles = {$traduciblesTxt};{$relaciones}
}

PHP;
    }

    // ------------------------------------------------------------------
    // Controlador
    // ------------------------------------------------------------------

    private function controlador(): string
    {
        $m = $this->modelo;
        $vista = "content.admin.{$this->slug}";
        $ruta = "adm.{$this->slug}";

        $usesModelos = ["use App\\Models\\{$m};"];
        $opciones = [];
        foreach ($this->relaciones() as $c) {
            $usesModelos[] = "use App\\Models\\{$c['modeloRel']};";
            $opciones[] = "'{$c['variable']}' => {$c['modeloRel']}::all(),";
        }
        $usesModelos = implode("\n", array_unique($usesModelos));

        $datosVista = function (string $item) use ($opciones) {
            $lineas = array_merge(["'item' => {$item},"], $opciones);
            return $this->indentar(implode("\n", $lineas), 12);
        };

        $datosUnico = $datosVista("{$m}::firstOrCreate([])");
        $datosNuevo = $datosVista("new {$m}()");
        $datosEditar = $datosVista("{$m}::findOrFail(\$id)");

        $completar = $this->indentar($this->lineasCompletar(), 8);
        $validar = $this->metodoValidar();
        $tieneValidar = $validar !== '';
        $llamarValidar = $tieneValidar ? "\$this->validar(\$request);\n\n        " : '';

        $archivos = $this->exportarArray(array_column($this->camposDeTipo(self::TIPOS_ARCHIVO), 'columna'));
        $listas = [];
        foreach ($this->camposDeTipo(['lista']) as $c) {
            $listas[] = "'{$c['columna']}' => " . $this->subcamposComoArray($c);
        }
        $listasTxt = '[' . implode(', ', $listas) . ']';

        if ($this->unico) {
            $metodos = <<<PHP
    public function show()
    {
        return view('{$vista}', [
{$datosUnico}
        ]);
    }

    public function update(Request \$request)
    {
        {$llamarValidar}\$item = {$m}::firstOrCreate([]);
        \$this->completar(\$item, \$request);
        \$item->save();

        return redirect()->route('{$ruta}')
            ->with('success', 'Cambios guardados correctamente.');
    }
PHP;
        } else {
            $consulta = $this->orden ? "{$m}::orderBy('orden')->get()" : "{$m}::latest()->get()";

            $metodos = <<<PHP
    public function index()
    {
        \$items = {$consulta};

        return view('{$vista}', [
            'items' => \$items,
        ]);
    }

    public function create()
    {
        return view('{$vista}-creador', [
{$datosNuevo}
        ]);
    }

    public function store(Request \$request)
    {
        {$llamarValidar}\$item = new {$m}();
        \$this->completar(\$item, \$request);
        \$item->save();

        return redirect()->route('{$ruta}')
            ->with('success', 'Registro creado correctamente.');
    }

    public function show(\$id)
    {
        return view('{$vista}-editor', [
{$datosEditar}
        ]);
    }

    public function update(Request \$request, \$id)
    {
        {$llamarValidar}\$item = {$m}::findOrFail(\$id);
        \$this->completar(\$item, \$request);
        \$item->save();

        return redirect()->route('{$ruta}')
            ->with('success', 'Registro actualizado correctamente.');
    }

    public function destroy(\$id)
    {
        \$item = {$m}::findOrFail(\$id);
        \$this->borrarArchivosDe(\$item, {$archivos}, {$listasTxt});
        \$item->delete();

        return redirect()->route('{$ruta}')
            ->with('success', 'Registro eliminado correctamente.');
    }

    public function switch(\$id)
    {
        \$item = {$m}::findOrFail(\$id);
        \$item->active = !\$item->active;
        \$item->save();

        return redirect()->route('{$ruta}')
            ->with('success', 'Estado actualizado correctamente.');
    }
PHP;

            if ($this->destacado) {
                $metodos .= <<<PHP


    public function destacado(\$id)
    {
        \$item = {$m}::findOrFail(\$id);
        \$item->destacado = !\$item->destacado;
        \$item->save();

        return redirect()->route('{$ruta}')
            ->with('success', 'Destacado actualizado correctamente.');
    }
PHP;
            }

            if ($this->orden) {
                $metodos .= <<<PHP


    public function reordenar(Request \$request)
    {
        foreach (\$request->orden as \$fila) {
            {$m}::where('id', \$fila['id'])->update(['orden' => \$fila['orden']]);
        }

        return response()->json(['success' => true]);
    }
PHP;
            }
        }

        $validarTxt = $tieneValidar ? "\n\n" . $validar : '';

        return <<<PHP
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\GestionaArchivos;
{$usesModelos}
use Illuminate\Http\Request;

class {$m}Controller extends Controller
{
    use GestionaArchivos;

{$metodos}{$validarTxt}

    private function completar({$m} \$item, Request \$request): void
    {
{$completar}
    }
}

PHP;
    }

    private function lineasCompletar(): string
    {
        $lineas = [];

        foreach ($this->relaciones() as $c) {
            $lineas[] = "\$item->{$c['columna']} = \$request->input('{$c['columna']}') ?: null;";
        }
        if ($this->orden) {
            $lineas[] = "\$item->orden = \$request->input('orden');";
        }

        foreach ($this->campos as $c) {
            $n = $c['columna'];
            $linea = match ($c['tipo']) {
                'relacion'                   => null,
                'string', 'text'             => "\$item->{$n} = \$request->input('{$n}');",
                'decimal', 'integer', 'date' => "\$item->{$n} = \$request->filled('{$n}') ? \$request->input('{$n}') : null;",
                'boolean'                    => "\$item->{$n} = \$request->boolean('{$n}');",
                'lista'                      => "\$item->{$n} = \$this->procesarLista(\$request, '{$n}', " . $this->subcamposComoArray($c) . ", \$item->{$n}, '{$this->tabla}');",
                default                      => "\$item->{$n} = \$this->procesarArchivo(\$request, '{$n}', \$item->{$n}, '{$this->tabla}');",
            };
            if ($linea) {
                $lineas[] = $linea;
            }
        }

        return implode("\n", $lineas);
    }

    private function reglaArchivo(string $tipo): string
    {
        return match ($tipo) {
            'imagen'  => 'nullable|image|max:2048',
            'video'   => 'nullable|file|mimetypes:video/mp4,video/webm,video/quicktime|max:51200',
            default   => 'nullable|file|max:10240',
        };
    }

    private function metodoValidar(): string
    {
        $reglas = [];

        foreach ($this->campos as $c) {
            if (in_array($c['tipo'], self::TIPOS_ARCHIVO)) {
                $reglas[] = "'{$c['columna']}' => '" . $this->reglaArchivo($c['tipo']) . "',";
            }
            if ($c['tipo'] === 'lista') {
                foreach ($c['subcampos'] as $s) {
                    if (in_array($s['tipo'], self::TIPOS_ARCHIVO)) {
                        $reglas[] = "'{$c['columna']}.*.{$s['nombre']}' => '" . $this->reglaArchivo($s['tipo']) . "',";
                    }
                }
            }
        }

        if (empty($reglas)) {
            return '';
        }

        $reglasTxt = $this->indentar(implode("\n", $reglas), 12);

        return <<<PHP
    private function validar(Request \$request): void
    {
        \$request->validate([
{$reglasTxt}
        ]);
    }
PHP;
    }

    // ------------------------------------------------------------------
    // Vistas
    // ------------------------------------------------------------------

    private function tieneTexto(): bool
    {
        return !empty($this->camposDeTipo(['text']));
    }

    private function scriptsVista(): string
    {
        $txt = "    @include('includes.modulo-scripts')";
        if ($this->tieneTexto()) {
            $txt .= "\n    @include('includes.ckeditor-init')";
        }

        return $txt;
    }

    private function bloqueErrores(): string
    {
        return <<<'BLADE'
@if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif
BLADE;
    }

    private function vistaListado(): string
    {
        $r = "adm.{$this->slug}";
        $columnas = [];

        if ($this->orden) {
            $columnas[] = ['Orden', 'text-left', '{{ $item->orden }}'];
        }

        $imagen = $this->camposDeTipo(['imagen'])[0] ?? null;
        if ($imagen) {
            $n = $imagen['columna'];
            $columnas[] = [$imagen['label'], 'text-center', "@if (!empty(\$item->{$n}))\n    <img src=\"{{ asset(\$item->{$n}) }}\" alt=\"\" class=\"w-[60px] h-[60px] object-cover mx-auto rounded\">\n@endif"];
        }

        $visibles = array_slice($this->camposDeTipo(['string', 'decimal', 'integer', 'date', 'relacion', 'boolean']), 0, 3);
        foreach ($visibles as $c) {
            $n = $c['columna'];
            $valor = match (true) {
                $c['tipo'] === 'relacion' => "{{ \\App\\Support\\Idiomas::texto(\$item->" . Str::camel($c['nombre']) . "?->nombre ?? \$item->" . Str::camel($c['nombre']) . "?->titulo) }}",
                $c['tipo'] === 'date'     => "{{ optional(\$item->{$n})->format('d/m/Y') }}",
                $c['tipo'] === 'boolean'  => "{{ \$item->{$n} ? 'Sí' : 'No' }}",
                $c['traducible']          => "{{ \$item->t('{$n}') }}",
                default                   => "{{ \$item->{$n} }}",
            };
            $columnas[] = [$c['label'], 'text-left', $valor];
        }

        $columnas[] = ['Activo', 'text-center', <<<BLADE
<a href="{{ route('{$r}-switch', \$item->id) }}"
   class="badge {{ \$item->active ? 'bg-green-400' : 'bg-red-400' }} text-white px-4 py-2 rounded">
    {{ \$item->active ? 'Activo' : 'Inactivo' }}
</a>
BLADE];

        $columnas[] = ['Acciones', 'text-center', <<<BLADE
<a href="{{ route('{$r}-editor', \$item->id) }}" class="text-blue-600 hover:underline font-bold">Editar</a>
<span>|</span>
<a href="{{ route('{$r}-destroy', \$item->id) }}" class="text-red-600 hover:underline font-bold"
   onclick="return confirm('¿Eliminar este registro?')">Borrar</a>
BLADE];

        if ($this->destacado) {
            $columnas[] = ['Destacado', 'text-center', <<<BLADE
<a href="{{ route('{$r}-destacado', \$item->id) }}"
   class="badge {{ \$item->destacado ? 'bg-green-400' : 'bg-red-400' }} text-white px-4 py-2 rounded">
    {{ \$item->destacado ? 'Destacado' : 'No destacado' }}
</a>
BLADE];
        }

        $ths = implode("\n", array_map(fn ($c) => "<th class=\"px-4 py-3 {$c[1]}\">{$c[0]}</th>", $columnas));
        $tds = implode("\n", array_map(fn ($c) => "<td class=\"px-4 py-3 {$c[1]}\">\n" . $this->indentar($c[2], 4) . "\n</td>", $columnas));
        $ths = $this->indentar($ths, 24);
        $tds = $this->indentar($tds, 28);

        $claseFila = $this->orden ? 'border-t hover:bg-gray-50 cursor-move' : 'border-t hover:bg-gray-50';
        $script = $this->orden ? <<<BLADE


<script>
    document.addEventListener('DOMContentLoaded', function () {
        activarOrdenDragDrop('#{$this->slug}-tabla', '{{ route('{$r}-reordenar') }}');
    });
</script>
BLADE : '';

        return <<<BLADE
@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')

   {{-- cabezal --}}
    <div>
        <h3 class="text-[20px] font-bold text-gray-500">{$this->tituloPlural}</h3>
        <hr class="mx-6">
    </div>

   {{-- Listado --}}
   <div class="p-4">
        <div class="overflow-x-auto rounded-2xl shadow">
            <div class="flex justify-end px-6 py-3 border rounded">
                <a href="{{ route('{$r}-creador') }}"
                   class="hover:text-gray-400 hover:scale-105 transition-transform duration-300">
                    <i class="fa-solid fa-plus"></i>
                </a>
            </div>

            <table class="min-w-full border border-gray-200 bg-white">
                <thead class="bg-gray-100">
                    <tr class="text-gray-700 text-sm uppercase">
{$ths}
                    </tr>
                </thead>

                <tbody id="{$this->slug}-tabla" class="text-gray-600">
                    @foreach(\$items as \$item)
                        <tr class="{$claseFila}" data-id="{{ \$item->id }}">
{$tds}
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>{$script}

@endsection

BLADE;
    }

    private function vistaFormulario(bool $editar): string
    {
        $r = "adm.{$this->slug}";
        $titulo = $editar ? "Editar {$this->titulo}" : "Crear {$this->titulo}";
        $subtitulo = $editar ? 'Editar registro' : 'Nuevo registro';
        $accion = $editar ? "{{ route('{$r}-update', \$item->id) }}" : "{{ route('{$r}-store') }}";
        $boton = $editar ? 'Actualizar' : 'Crear';
        $errores = $this->indentar($this->bloqueErrores(), 12);
        $scripts = $this->scriptsVista();

        return <<<BLADE
@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')

   {{-- cabezal --}}
    <div>
        <h3 class="text-[20px] font-bold text-gray-500">{$titulo}</h3>
        <hr class="mx-6">
    </div>

    {{-- Formulario --}}
    <div class="mx-20 pt-6">
        <div class="border border-rounded p-4">
            <div>
                <h3 class="text-[18px] font-semibold text-gray-600">{$subtitulo}</h3>
            </div>
            <hr class="my-3">

{$errores}

            <form method="POST" action="{$accion}" enctype="multipart/form-data">
                @csrf

                @include('content.admin.partials.{$this->slug}-campos')

                <button type="submit" class="border rounded p-4 bg-white hover:bg-gray-50 transition-colors">{$boton}</button>
            </form>
        </div>
    </div>

{$scripts}
@endsection

BLADE;
    }

    private function vistaUnica(): string
    {
        $r = "adm.{$this->slug}";
        $errores = $this->indentar($this->bloqueErrores(), 12);
        $campos = $this->indentar($this->camposFormulario(), 16);
        $scripts = $this->scriptsVista();

        return <<<BLADE
@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')

   {{-- cabezal --}}
    <div>
        <h3 class="text-[20px] font-bold text-gray-500">{$this->titulo}</h3>
        <hr class="mx-6">
    </div>

    {{-- Editor --}}
    <div class="mx-20 pt-6">
        <div class="border border-rounded p-4">
            <div>
                <h3 class="text-[18px] font-semibold text-gray-600">Editar contenido</h3>
            </div>
            <hr class="my-3">

{$errores}

            <form method="POST" action="{{ route('{$r}-update') }}" enctype="multipart/form-data">
                @csrf

{$campos}

                <button type="submit" class="border rounded p-4 bg-white hover:bg-gray-50 transition-colors">Actualizar</button>
            </form>
        </div>
    </div>

{$scripts}
@endsection

BLADE;
    }

    // ------------------------------------------------------------------
    // Campos del formulario
    // ------------------------------------------------------------------

    private function camposFormulario(): string
    {
        $bloques = [];

        foreach ($this->relaciones() as $c) {
            $bloques[] = $this->campoRelacion($c);
        }

        if ($this->orden) {
            $bloques[] = $this->envolver('Orden', $this->input('text', 'orden', "{{ old('orden', \$item->orden) }}"));
        }

        foreach ($this->campos as $c) {
            if ($c['tipo'] === 'relacion') {
                continue;
            }
            $bloques[] = "{{-- {$c['label']} --}}\n" . $this->campo($c);
        }

        return implode("\n\n", $bloques);
    }

    private function envolver(string $label, string $contenido, string $clase = 'mb-4'): string
    {
        return "<div class=\"{$clase}\">\n"
            . "    <label class=\"block text-gray-700 font-medium mb-2\">{$label}</label>\n"
            . $this->indentar($contenido, 4) . "\n"
            . '</div>';
    }

    private function input(string $type, string $name, string $value, string $extra = ''): string
    {
        $extra = $extra ? " {$extra}" : '';

        return "<input type=\"{$type}\" name=\"{$name}\" value=\"{$value}\"{$extra}\n       class=\"" . self::INPUT . '">';
    }

    private function campo(array $c): string
    {
        $n = $c['columna'];
        $label = $c['label'];

        if ($c['traducible']) {
            $bloques = [];
            foreach ($this->idiomas as $idioma) {
                $nombreIdioma = Idiomas::nombre($idioma);
                $valor = "{{ old('{$n}.{$idioma}', \$item->{$n}['{$idioma}'] ?? '') }}";
                $control = $c['tipo'] === 'text'
                    ? "<textarea name=\"{$n}[{$idioma}]\" rows=\"6\" class=\"ckeditor " . self::INPUT . "\">{$valor}</textarea>"
                    : $this->input('text', "{$n}[{$idioma}]", $valor);
                $bloques[] = $this->envolver("{$label} ({$nombreIdioma})", $control);
            }
            return implode("\n", $bloques);
        }

        return match ($c['tipo']) {
            'string'  => $this->envolver($label, $this->input('text', $n, "{{ old('{$n}', \$item->{$n}) }}")),
            'text'    => $this->envolver($label, "<textarea name=\"{$n}\" rows=\"6\" class=\"ckeditor " . self::INPUT . "\">{{ old('{$n}', \$item->{$n}) }}</textarea>"),
            'decimal' => $this->envolver($label, $this->input('number', $n, "{{ old('{$n}', \$item->{$n}) }}", 'step="0.01"')),
            'integer' => $this->envolver($label, $this->input('number', $n, "{{ old('{$n}', \$item->{$n}) }}", 'step="1"')),
            'date'    => $this->envolver($label, $this->input('date', $n, "{{ old('{$n}', optional(\$item->{$n})->format('Y-m-d')) }}")),
            'boolean' => "<div class=\"mb-4\">\n"
                . "    <input type=\"hidden\" name=\"{$n}\" value=\"0\">\n"
                . "    <label class=\"inline-flex items-center gap-2 text-gray-700 font-medium\">\n"
                . "        <input type=\"checkbox\" name=\"{$n}\" value=\"1\" @checked(old('{$n}', \$item->{$n}))> {$label}\n"
                . "    </label>\n"
                . '</div>',
            'imagen', 'archivo', 'video' => $this->campoArchivo($c),
            'lista'   => $this->campoLista($c),
        };
    }

    private function campoArchivo(array $c): string
    {
        $n = $c['columna'];

        [$label, $accept, $vista] = match ($c['tipo']) {
            'imagen' => [
                "{$c['label']} (2MB máximo)",
                'image/*',
                "<img src=\"{{ \$item->{$n} }}\" alt=\"\"\n     class=\"vista-previa mb-3 max-w-[300px] max-h-[200px] object-cover rounded {{ empty(\$item->{$n}) ? 'hidden' : '' }}\">",
            ],
            'video' => [
                "{$c['label']} (50MB máximo)",
                'video/*',
                "@if (!empty(\$item->{$n}))\n    <video src=\"{{ \$item->{$n} }}\" controls class=\"mb-3 max-w-[400px] rounded\"></video>\n@endif",
            ],
            default => [
                "{$c['label']} (10MB máximo)",
                '',
                "@if (!empty(\$item->{$n}))\n    <a href=\"{{ \$item->{$n} }}\" target=\"_blank\" class=\"block mb-2 text-blue-600 hover:underline\">Ver archivo actual</a>\n@endif",
            ],
        };

        $acceptAttr = $accept ? " accept=\"{$accept}\"" : '';
        $onchange = $c['tipo'] === 'imagen' ? ' onchange="previsualizarImagen(this)"' : '';

        $contenido = $vista . "\n"
            . "<input type=\"file\" name=\"{$n}\"{$acceptAttr}{$onchange}\n       class=\"" . self::INPUT . "\">\n"
            . "@if (!empty(\$item->{$n}))\n"
            . "    <label class=\"inline-flex items-center gap-2 mt-2 text-sm text-gray-600\">\n"
            . "        <input type=\"checkbox\" name=\"quitar_{$n}\" value=\"1\"> Quitar {$c['label']}\n"
            . "    </label>\n"
            . '@endif';

        return $this->envolver($label, $contenido, 'mb-4 campo-archivo');
    }

    private function campoRelacion(array $c): string
    {
        $n = $c['columna'];
        $var = $c['variable'];

        $select = "<select name=\"{$n}\" class=\"" . self::INPUT . "\">\n"
            . "    <option value=\"\">-- Sin {$c['label']} --</option>\n"
            . "    @foreach (\${$var} as \$opcion)\n"
            . "        <option value=\"{{ \$opcion->id }}\" @selected(old('{$n}', \$item->{$n}) == \$opcion->id)>\n"
            . "            {{ \\App\\Support\\Idiomas::texto(\$opcion->nombre ?? \$opcion->titulo) ?? '#' . \$opcion->id }}\n"
            . "        </option>\n"
            . "    @endforeach\n"
            . '</select>';

        return "{{-- {$c['label']} --}}\n" . $this->envolver($c['label'], $select);
    }

    private function campoLista(array $c): string
    {
        $n = $c['columna'];
        $id = "lista-{$n}";

        $filaServidor = $this->filaLista($c, '{{ $i }}', true);
        $filaTemplate = $this->filaLista($c, '__i__', false);

        $contenido = "<div id=\"{$id}\" class=\"space-y-3\" data-siguiente=\"{{ count(\$item->{$n} ?? []) }}\">\n"
            . "    @foreach (array_values(\$item->{$n} ?? []) as \$i => \$fila)\n"
            . $this->indentar($filaServidor, 8) . "\n"
            . "    @endforeach\n"
            . "</div>\n\n"
            . "<template id=\"{$id}-template\">\n"
            . $this->indentar($filaTemplate, 4) . "\n"
            . "</template>\n\n"
            . "<button type=\"button\" onclick=\"agregarFila('{$id}')\"\n"
            . "        class=\"mt-3 border rounded px-4 py-2 bg-white hover:bg-gray-50\">\n"
            . "    <i class=\"fa-solid fa-plus mr-1\"></i> Agregar fila\n"
            . '</button>';

        return $this->envolver($c['label'], $contenido);
    }

    private function filaLista(array $c, string $i, bool $conValores): string
    {
        $subs = [];

        foreach ($c['subcampos'] as $s) {
            $sn = $s['nombre'];
            $base = "{$c['columna']}[{$i}][{$sn}]";
            $valor = fn (string $clave = '') => $conValores ? "{{ \$fila['{$sn}']{$clave} ?? '' }}" : '';
            $label = fn (string $txt) => "<label class=\"block text-sm text-gray-600 mb-1\">{$txt}</label>";

            if ($s['traducible']) {
                foreach ($this->idiomas as $idioma) {
                    $txt = "{$s['label']} (" . Idiomas::nombre($idioma) . ')';
                    $v = $valor("['{$idioma}']");
                    $control = $s['tipo'] === 'text'
                        ? "<textarea name=\"{$base}[{$idioma}]\" rows=\"3\" class=\"" . self::INPUT . "\">{$v}</textarea>"
                        : "<input type=\"text\" name=\"{$base}[{$idioma}]\" value=\"{$v}\" class=\"" . self::INPUT . '">';
                    $subs[] = "<div>\n    " . $label($txt) . "\n    {$control}\n</div>";
                }
                continue;
            }

            if (in_array($s['tipo'], self::TIPOS_ARCHIVO)) {
                $v = $valor();
                $actual = "<input type=\"hidden\" name=\"{$c['columna']}[{$i}][{$sn}_actual]\" value=\"{$v}\">";
                $accept = match ($s['tipo']) { 'imagen' => ' accept="image/*" onchange="previsualizarImagen(this)"', 'video' => ' accept="video/*"', default => '' };

                if ($s['tipo'] === 'imagen') {
                    $oculto = $conValores ? "{{ empty(\$fila['{$sn}']) ? 'hidden' : '' }}" : 'hidden';
                    $vista = "<img src=\"{$v}\" alt=\"\" class=\"vista-previa h-[80px] object-cover rounded mb-2 {$oculto}\">";
                } else {
                    $vista = $conValores
                        ? "@if (!empty(\$fila['{$sn}']))\n        <a href=\"{$v}\" target=\"_blank\" class=\"block text-sm text-blue-600 hover:underline mb-1\">Ver actual</a>\n    @endif"
                        : '';
                }

                $lineas = array_filter([$label($s['label']), $actual, $vista, "<input type=\"file\" name=\"{$base}\"{$accept} class=\"" . self::INPUT . '">']);
                $subs[] = "<div class=\"campo-archivo\">\n    " . implode("\n    ", $lineas) . "\n</div>";
                continue;
            }

            $v = $valor();
            $control = match ($s['tipo']) {
                'text'    => "<textarea name=\"{$base}\" rows=\"3\" class=\"" . self::INPUT . "\">{$v}</textarea>",
                'decimal' => "<input type=\"number\" step=\"0.01\" name=\"{$base}\" value=\"{$v}\" class=\"" . self::INPUT . '">',
                'integer' => "<input type=\"number\" step=\"1\" name=\"{$base}\" value=\"{$v}\" class=\"" . self::INPUT . '">',
                'date'    => "<input type=\"date\" name=\"{$base}\" value=\"{$v}\" class=\"" . self::INPUT . '">',
                default   => "<input type=\"text\" name=\"{$base}\" value=\"{$v}\" class=\"" . self::INPUT . '">',
            };
            $subs[] = "<div>\n    " . $label($s['label']) . "\n    {$control}\n</div>";
        }

        return "<div class=\"fila-lista border rounded p-3 bg-gray-50\">\n"
            . "    <div class=\"grid grid-cols-1 md:grid-cols-2 gap-3\">\n"
            . $this->indentar(implode("\n", $subs), 8) . "\n"
            . "    </div>\n"
            . "    <button type=\"button\" onclick=\"quitarFila(this)\" class=\"mt-2 text-sm text-red-600 hover:underline\">Quitar fila</button>\n"
            . '</div>';
    }

    // ------------------------------------------------------------------
    // Rutas y sidebar
    // ------------------------------------------------------------------

    private function agregarRutas(): void
    {
        $archivo = base_path('routes/modulos.php');
        $ctrl = "[\\App\\Http\\Controllers\\{$this->modelo}Controller::class";
        $url = "/admin/{$this->slug}";
        $r = "adm.{$this->slug}";

        if ($this->unico) {
            $rutas = [
                "Route::get('{$url}', {$ctrl}, 'show'])->name('{$r}');",
                "Route::post('{$url}/update', {$ctrl}, 'update'])->name('{$r}-update');",
            ];
        } else {
            $rutas = [
                "Route::get('{$url}', {$ctrl}, 'index'])->name('{$r}');",
                "Route::get('{$url}/creador', {$ctrl}, 'create'])->name('{$r}-creador');",
                "Route::post('{$url}/store', {$ctrl}, 'store'])->name('{$r}-store');",
                "Route::get('{$url}/editor/{id}', {$ctrl}, 'show'])->name('{$r}-editor');",
                "Route::post('{$url}/update/{id}', {$ctrl}, 'update'])->name('{$r}-update');",
                "Route::get('{$url}/destroy/{id}', {$ctrl}, 'destroy'])->name('{$r}-destroy');",
                "Route::get('{$url}/switch/{id}', {$ctrl}, 'switch'])->name('{$r}-switch');",
            ];
            if ($this->destacado) {
                $rutas[] = "Route::get('{$url}/destacado/{id}', {$ctrl}, 'destacado'])->name('{$r}-destacado');";
            }
            if ($this->orden) {
                $rutas[] = "Route::post('{$url}/reordenar', {$ctrl}, 'reordenar'])->name('{$r}-reordenar');";
            }
        }

        if (!File::exists($archivo)) {
            File::put($archivo, "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n");
        }

        File::append($archivo, "\n// {$this->tituloPlural} (make:modulo)\n" . implode("\n", $rutas) . "\n");
    }

    private function agregarAlSidebar(): bool
    {
        $archivo = resource_path('views/layouts/sidebar.blade.php');
        $marca = '{{-- make:modulo:';
        $label = $this->unico ? $this->titulo : $this->tituloPlural;

        $links = [];
        if ($this->option('banner')) {
            $links[] = "<li><a href=\"{{route('adm.banner', '{$this->seccion}')}}\" class=\"block py-1 px-2 hover:bg-gray-600 rounded\">Banner</a></li>";
        }
        $links[] = "<li><a href=\"{{route('adm.{$this->slug}')}}\" class=\"block py-1 px-2 hover:bg-gray-600 rounded\">Contenido</a></li>";

        $bloque = "<li class=\"group\">\n"
            . "  <button class=\"flex justify-between items-center w-full py-2 px-3 hover:bg-gray-700 rounded transition focus:outline-none\" onclick=\"toggleSubMenu(this)\">\n"
            . "    <span><i class=\"fas fa-folder mr-2\"></i>{$label}</span>\n"
            . "    <i class=\"fas fa-chevron-down transition-transform duration-300 group-[.open]:rotate-180\"></i>\n"
            . "  </button>\n"
            . "  <ul class=\"submenu hidden pl-8 mt-1 space-y-1\">\n"
            . $this->indentar(implode("\n", $links), 4) . "\n"
            . "  </ul>\n"
            . '</li>';

        $contenido = File::exists($archivo) ? File::get($archivo) : '';
        $pos = strpos($contenido, $marca);

        if ($pos === false) {
            $this->warn('No encontré la marca "{{-- make:modulo: ... --}}" en el sidebar. Agregá este bloque a mano:');
            $this->line($bloque);
            return false;
        }

        $inicioLinea = strrpos(substr($contenido, 0, $pos), "\n");
        $inicioLinea = $inicioLinea === false ? 0 : $inicioLinea + 1;
        $sangria = substr($contenido, $inicioLinea, $pos - $inicioLinea);

        $insertar = $this->indentar($bloque, strlen($sangria)) . "\n";
        File::put($archivo, substr($contenido, 0, $inicioLinea) . $insertar . substr($contenido, $inicioLinea));

        return true;
    }
}
