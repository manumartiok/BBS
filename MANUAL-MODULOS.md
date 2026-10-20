# Manual: idiomas, banners y generador de módulos

Este manual explica tres herramientas de Administrador para arrancar proyectos más rápido:

1. **Idiomas del sitio**: se configuran una sola vez por proyecto.
2. **Banners unificados**: una sola tabla para los banners de todas las secciones.
3. **`php artisan make:modulo`**: genera un módulo completo del administrador con un solo comando.

---

## 1. Idiomas del sitio

Se configuran en **`config/sitio.php`**:

```php
'idiomas' => ['es'],
```

| Idioma | Código |
|---|---|
| Español | `es` |
| Inglés | `en` |
| Italiano | `it` |
| Francés | `fr` |
| Portugués | `pt` |
| Alemán | `de` |

- **El primero de la lista es el idioma principal**: el que se muestra por defecto y el que se usa si falta una traducción.
- Sitio solo en español: `['es']`. Sitio bilingüe: `['es', 'en']`. Se pueden poner los que quieras, por ejemplo `['es', 'en', 'pt']`.
- Va en `config/sitio.php` y no en el `.env` porque define cómo se crean las tablas, así que tiene que estar en git.

> ⚠️ **Definilo al arrancar el proyecto, antes de generar módulos y antes del primer `migrate`.**
> Si después pasás de `['es']` a `['es', 'en']`, lo que generes de ahí en adelante sale traducible, pero las tablas que ya existían no se convierten solas. Habría que migrar esas columnas a mano.

### Qué cambia cuando el sitio tiene más de un idioma

- Los campos de texto (`string` y `text`) se guardan como **json, con un valor por idioma**:
  `{"es": "Tornillo", "en": "Screw"}`.
- En el admin, cada campo de texto aparece una vez por idioma: *Nombre (Español)*, *Nombre (Inglés)*.
- Imágenes, archivos, precios, números, fechas, sí/no y relaciones **no se traducen**: son uno solo.

### Mostrar textos en la web

Todos los modelos generados (y `Banner`) tienen el método **`t()`**, que devuelve el texto en el idioma actual del visitante:

```blade
<h1>{{ $producto->t('nombre') }}</h1>
{!! $producto->t('descripcion') !!}
```

- Si el sitio tiene **un solo idioma**, `t('nombre')` devuelve lo mismo que `$producto->nombre`. Por eso conviene usar **siempre `t()`** en las vistas de la web: si el sitio pasa a ser bilingüe, no hay que tocarlas.
- Para pedir un idioma puntual: `$producto->t('nombre', 'en')`.
- Para valores sueltos, como los subcampos de una lista: `\App\Support\Idiomas::texto($fila['nombre'])`.

### Cambiar de idioma en la web

Ya existe la ruta **`idioma.cambiar`**. El idioma elegido se guarda en la sesión y se aplica en todas las páginas:

```blade
@foreach (\App\Support\Idiomas::todos() as $idioma)
    <a href="{{ route('idioma.cambiar', $idioma) }}"
       class="{{ app()->getLocale() === $idioma ? 'font-bold' : '' }}">
        {{ strtoupper($idioma) }}
    </a>
@endforeach
```

Si alguien pide un idioma que no está en `config/sitio.php`, se ignora.

---

## 2. Banners unificados

Antes había una tabla, un modelo, un controlador y una vista **por cada banner** (`nosotro_banners`, `novedade_banners`, `contacto_banners`…), todos iguales: foto + texto.

Ahora hay **una sola tabla `banners`**, con una fila por sección:

| id | seccion | foto | texto |
|---|---|---|---|
| 1 | nosotros | /storage/banners/... | Quiénes somos |
| 2 | novedades | /storage/banners/... | Novedades |
| 3 | contacto | /storage/banners/... | Contacto |
| 4 | productos | /storage/banners/... | Productos |

**En el admin no cambia nada visible**: cada sección sigue teniendo su link *Banner* en el sidebar (Nosotros → Banner, Contacto → Banner…). Todos abren la misma pantalla, pero cada uno edita la fila de su sección.

- URL del admin: `/admin/banner/{seccion}` (ruta `adm.banner`).
- Si la sección todavía no tiene banner, la fila se crea sola la primera vez que se usa.
- Si el sitio tiene varios idiomas, el texto del banner se carga en cada idioma.

> El **banner del Home** (varios slides, con imagen o video) es distinto y queda aparte, en `home_banners`.

### Usarlo en la web

```blade
@php $banner = App\Models\Banner::seccion('nosotros'); @endphp

<div style="background-image: url('{{ $banner->foto }}');">
    <h1>{{ $banner->t('texto') }}</h1>
</div>
```

### Agregar un banner a una sección nueva

- **Si la sección la generás con el comando**: agregá `--banner` y listo.
- **A mano**: alcanza con agregar el link en el sidebar, por ejemplo
  `<a href="{{ route('adm.banner', 'servicios') }}">Banner</a>`, y leerlo en la web con `Banner::seccion('servicios')`.
  No hace falta crear tabla, modelo ni controlador.

### Pasar un proyecto existente a la tabla única

La migración `create_banners_table` **copia los banners que ya existan** en `nosotro_banners`, `novedade_banners` y `contacto_banners` a la tabla nueva, y después borra esas tablas viejas. Con correr `php artisan migrate` alcanza.

---

## 3. Generador de módulos: `make:modulo`

### Qué hace

Con un solo comando crea todo lo que hoy armás copiando y pegando otro módulo:

| Genera | Listado | Página única (`--unico`) |
|---|---|---|
| Migración | ✅ | ✅ |
| Modelo | ✅ | ✅ |
| Controlador | ✅ index, create, store, show, update, destroy, switch (+ destacado, reordenar) | ✅ show, update |
| Vistas del admin | ✅ listado, creador, editor (+ partial con los campos) | ✅ una sola vista (el editor) |
| Rutas `adm.*` | ✅ en `routes/modulos.php` | ✅ en `routes/modulos.php` |
| Ítem en el sidebar | ✅ Contenido (+ Banner) | ✅ Contenido (+ Banner) |

**No genera la parte pública de la web**, porque el diseño cambia en cada cliente. Esa la armás vos con los datos del modelo (ver sección 4).

### Sintaxis

```bash
php artisan make:modulo NombreDelModelo --campos="campo1,campo2:tipo,..." [opciones]
```

- **Nombre del modelo**: en singular y con mayúscula, igual que hoy (`Producto`, `Nosotro`, `Novedade`). Con eso se arman los demás nombres:

  | Comando | Tabla | Controlador | Vistas | Rutas |
  |---|---|---|---|---|
  | `Producto` | `productos` | `ProductoController` | `producto.blade.php`… | `adm.producto`, `adm.producto-creador`… |
  | `PostVenta` | `post_ventas` | `PostVentaController` | `post-venta.blade.php`… | `adm.post-venta`… |

- **`--campos`**: los campos, separados por coma, **sin espacios y entre comillas**. Cada campo es `nombre` o `nombre:tipo`.

### Opciones

| Opción | Qué hace |
|---|---|
| *(ninguna)* | **Listado** con alta, edición, borrar y activar/desactivar. Es el tipo normal. |
| `--orden` | Listado **ordenable**: agrega la columna `orden`, el drag & drop y la ruta `reordenar`. |
| `--destacado` | Agrega la columna `destacado` (arranca en **no**) y el botón para destacar. |
| `--unico` | **Página única**: un solo registro que se edita (como Nosotros, Home, Contacto). Sin listado. Acá `--orden` y `--destacado` se ignoran. |
| `--banner` | Agrega el link **Banner** de la sección en el sidebar. **No crea tablas**: usa la tabla única `banners`. |
| `--sin-traducir="codigo,sku"` | Solo en sitios con varios idiomas: campos de texto que **no** se traducen (códigos, SKU, emails, teléfonos…). |

Siempre se agregan solos: `id`, `timestamps` y, en los listados, `active` (arranca activo).

### Tipos de campo

| Tipo | Columna en la tabla | Input en el admin |
|---|---|---|
| `string` | texto corto (`string`) | campo de texto |
| `text` | texto largo (`text`) | editor CKEditor |
| `decimal` | `decimal(10, 2)` | número con decimales |
| `integer` | `integer` | número entero |
| `boolean` | sí/no (arranca en no) | checkbox |
| `date` | fecha | selector de fecha |
| `imagen` | ruta del archivo | subir imagen con vista previa y "Quitar" |
| `archivo` | ruta del archivo | subir PDF u otro archivo, con link para verlo y "Quitar" |
| `video` | ruta del archivo | subir video (50MB máximo) |
| `relacion` | `xxx_id`, enlazada a otra tabla | desplegable con los registros de esa tabla |
| `lista[...]` | `json` | filas repetibles con "Agregar fila" y "Quitar fila" |

**Sin tipo, el campo es `string`.** Solo hace falta poner el tipo cuando querés algo distinto.

**Nombres que el comando reconoce solos** (no hace falta poner el tipo):

| Nombre | Tipo |
|---|---|
| `foto`, `imagen` | `imagen` |
| `texto`, `descripcion` | `text` |
| `precio` | `decimal` |
| `video` | `video` |
| `archivo` | `archivo` |

Si querés otra cosa, el tipo que escribas manda: `texto:string` es un texto corto.

**Ningún campo es obligatorio**: todas las columnas son `nullable`.

### Relaciones

`marca:relacion` crea la columna `marca_id`, enlazada a la tabla `marcas`. Si se borra la marca, el campo queda vacío (`nullOnDelete`). También agrega en el modelo el método `marca()` y en el formulario un desplegable con las marcas.

- **El modelo relacionado (`Marca`) tiene que existir antes de migrar.** Si no existe, el comando avisa.
- En el desplegable se muestra el `nombre` del registro relacionado (o `titulo`, si no tiene `nombre`).

### Listas (json con filas repetibles)

Para datos como una ficha técnica: varias filas, cada una con los mismos subcampos. Los subcampos van entre corchetes, separados con `|`:

```bash
--campos="ficha_tecnica:lista[nombre|valor]"
--campos="caracteristicas:lista[titulo|foto:imagen]"
```

- Los subcampos usan los mismos tipos y nombres reconocidos (`foto` → imagen), salvo `relacion`, `lista` y `boolean`.
- Los subcampos de texto largo son un textarea simple, sin CKEditor.
- Al quitar una fila o reemplazar una foto, el archivo viejo se borra del disco.
- En un sitio con idiomas, los subcampos de texto también se cargan por idioma.

### Qué hace el `orden`

Tu drag & drop guarda el orden como letras (`AA`, `AB`, `AC`…), por eso la columna `orden` es `string`. Se ordena bien porque todas las letras tienen el mismo largo. También se puede escribir a mano en el formulario.

### Protecciones

- Si ya existe el modelo, el controlador, alguna vista, una migración para esa tabla o la ruta `adm.xxx`, **no genera nada** y avisa.
- Si un tipo no existe o falta algo, avisa y no genera nada.
- Los nombres `id`, `orden`, `active`, `destacado`, `created_at` y `updated_at` no se pueden usar como campos: se manejan con las opciones.
- En el sidebar, los módulos se agregan arriba de la línea
  `{{-- make:modulo: los módulos nuevos se agregan arriba de esta línea (no borrar) --}}`. **No la borres.** Si no está, el comando te muestra el bloque para pegarlo a mano.

### Después de generar

1. Correr **`php artisan migrate`**.
2. Revisar los textos que conviene ajustar a mano:
   - **Acentos en títulos y labels**: el comando no los sabe poner (`Descripcion` → `Descripción`, `Garantia` → `Garantía`).
   - **Medidas recomendadas** de las imágenes en el label.
   - **Ícono del sidebar**: por defecto `fa-folder`.
3. Armar la página pública de la web (sección 4).

---

## 4. Ejemplos

### Ejemplo 1: Productos en una web común (un solo idioma)

`config/sitio.php` → `'idiomas' => ['es']`

```bash
php artisan make:modulo Producto --campos="marca:relacion,nombre,codigo,precio,foto,archivo" --orden --destacado --banner
```

Migración generada:

```php
Schema::create('productos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('marca_id')->nullable()->constrained('marcas')->nullOnDelete();
    $table->string('orden')->nullable();
    $table->string('nombre')->nullable();
    $table->string('codigo')->nullable();
    $table->decimal('precio', 10, 2)->nullable();
    $table->string('foto')->nullable();
    $table->string('archivo')->nullable();
    $table->boolean('active')->default(true);
    $table->boolean('destacado')->default(false);
    $table->timestamps();
});
```

En el modelo: `protected array $traducibles = [];` (no hay nada para traducir).

### Ejemplo 2: Productos en una web traducida

`config/sitio.php` → `'idiomas' => ['es', 'en']`

```bash
php artisan make:modulo Producto --campos="marca:relacion,nombre,codigo,precio,foto,archivo" --orden --destacado --banner --sin-traducir=codigo
```

Migración generada:

```php
Schema::create('productos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('marca_id')->nullable()->constrained('marcas')->nullOnDelete();
    $table->string('orden')->nullable();
    $table->json('nombre')->nullable();          // {"es": "Tornillo", "en": "Screw"}
    $table->string('codigo')->nullable();        // no se traduce (--sin-traducir)
    $table->decimal('precio', 10, 2)->nullable();
    $table->string('foto')->nullable();
    $table->string('archivo')->nullable();
    $table->boolean('active')->default(true);
    $table->boolean('destacado')->default(false);
    $table->timestamps();
});
```

En el modelo: `protected array $traducibles = ['nombre'];`

> Sin `--sin-traducir=codigo`, `codigo` también saldría como `json` por idioma. Para un código como `1ab`, que es igual en todos los idiomas, no tiene sentido.

### Ejemplo 3: campo con un nombre que no se reconoce

```bash
php artisan make:modulo Producto --campos="nombre,precio,detalle"             # detalle = string
php artisan make:modulo Producto --campos="nombre,precio,detalle:text"        # detalle = texto largo con CKEditor
php artisan make:modulo Producto --campos="nombre,precio,ficha:lista[nombre|valor]"   # detalle por filas
```

### Ejemplo 4: página única con banner

```bash
php artisan make:modulo Garantia --campos="titulo,texto,archivo" --unico --banner
```

En el sidebar queda: **Garantia → Banner** y **Garantia → Contenido**.

### Ejemplo 5: listado simple, sin orden

```bash
php artisan make:modulo Cliente --campos="nombre,foto"
```

---

## 5. Usar los datos en la web pública

```blade
@php
    $productos = App\Models\Producto::where('active', true)->orderBy('orden')->get();
    $banner = App\Models\Banner::seccion('productos');
@endphp

<div style="background-image: url('{{ $banner->foto }}');">
    <h1>{{ $banner->t('texto') }}</h1>
</div>

@foreach ($productos as $producto)
    <img src="{{ $producto->foto }}" alt="{{ $producto->t('nombre') }}">
    <h2>{{ $producto->t('nombre') }}</h2>
    <p>{{ $producto->marca?->nombre }} · Código {{ $producto->codigo }}</p>
    <p>$ {{ number_format($producto->precio, 2, ',', '.') }}</p>

    @if ($producto->archivo)
        <a href="{{ $producto->archivo }}" target="_blank">Descargar ficha</a>
    @endif
@endforeach
```

Una lista (por ejemplo `ficha:lista[nombre|valor]`) se recorre así:

```blade
@foreach ($producto->ficha ?? [] as $fila)
    <tr>
        <td>{{ \App\Support\Idiomas::texto($fila['nombre']) }}</td>
        <td>{{ \App\Support\Idiomas::texto($fila['valor']) }}</td>
    </tr>
@endforeach
```

> `Idiomas::texto()` sirve en los dos modos: si el valor es texto simple, lo devuelve tal cual.

---

## 6. Archivos de soporte (referencia)

| Archivo | Para qué sirve |
|---|---|
| `config/sitio.php` | Idiomas del proyecto |
| `app/Support/Idiomas.php` | Idiomas configurados, principal, nombres, `Idiomas::texto()` |
| `app/Models/Concerns/Traducible.php` | Trait de los modelos: método `t()` y json por idioma |
| `app/Http/Middleware/SetIdioma.php` | Aplica el idioma elegido en cada request |
| `app/Http/Controllers/Concerns/GestionaArchivos.php` | Subir y borrar archivos, procesar listas |
| `app/Models/Banner.php` + `BannerController.php` | Banners unificados |
| `resources/views/content/admin/banner.blade.php` | Pantalla de edición de cualquier banner |
| `resources/views/includes/modulo-scripts.blade.php` | JS de vista previa de imágenes y listas repetibles |
| `routes/modulos.php` | Rutas de los módulos generados (se carga dentro del grupo AdminAuth) |
| `app/Console/Commands/MakeModulo.php` | El comando `make:modulo` |
