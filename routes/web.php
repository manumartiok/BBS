<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Middleware\AdminAuth;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\PerfilController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HomeBannerController;
use App\Http\Controllers\NosotroController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProductoBusquedaController;
use App\Http\Controllers\NovedadeController;
use App\Http\Controllers\ContactoController;

use App\Http\Controllers\WebUsuarioController;
use App\Http\Controllers\ListaController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\PresupuestoController;
use App\Http\Controllers\CanjePuntosController;
use App\Http\Controllers\CanjeAdminController;
use App\Http\Controllers\UsuarioPublicoAdminController;
use App\Http\Controllers\PedidoPublicoAdminController;
use App\Http\Controllers\AccesoPublicoController;
use App\Http\Controllers\CarritoPublicoController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\InfoPagoController;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\Banner;
use App\Http\Controllers\BannerController;

use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\LogoController;
use App\Http\Controllers\RedController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\MetadatoController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\ContabiliumApiController;

//ADMIN
Route::middleware(AdminAuth::class)->group(function () {

    //ADMIN
    Route::get('/admin/dashboard', function () {
    return view('content.admin.admin-dashboard');})->name('adm.dashboard');

    //perfil
    Route::get('/admin/perfil', [PerfilController::class, 'show'])->name('adm.perfil');
    Route::post('/admin/perfil/update', [PerfilController::class, 'update'])->name('adm.perfil-update');

    //home banner
    Route::get('/admin/home/banner', [HomeBannerController::class,'index'])->name('adm.home-ban');
    Route::get('/admin/home/banner/creador', [HomeBannerController::class,'create'])->name('adm.home-ban-creador');
    Route::post('/admin/home/banner/store', [HomeBannerController::class,'store'])->name('adm.home-ban-store');
    Route::get('/admin/home/banner/editor/{home_banners_id}', [HomeBannerController::class,'show'])->name('adm.home-ban-editor');
    Route::post('/admin/home/banner/update', [HomeBannerController::class,'update'])->name('adm.home-ban-update');
    Route::get('/admin/home/banner/destroy/{home_banners_id}', [HomeBannerController::class,'destroy'])->name('adm.home-ban-destroy');
    Route::get('/admin/home/banner/switch/{home_banners_id}', [HomeBannerController::class,'switch'])->name('adm.home-ban-switch');
    Route::post('/admin/home-ban/reordenar', [HomeBannerController::class, 'reordenar'])->name('adm.home-ban-reordenar');

    //home 
    Route::get('/admin/home', [HomeController::class, 'show'])->name('adm.home');
    Route::post('/admin/home/update', [HomeController::class,'update'])->name('adm.home-update');

    //nosotros
    Route::get('/admin/nosotros', [NosotroController::class, 'show'])->name('adm.nosotros');
    Route::post('/admin/nosotros/update', [NosotroController::class,'update'])->name('adm.nosotros-update');

     // marcas

    Route::get('/admin/marca', [MarcaController::class, 'index'])->name('adm.marca');
    Route::get('/admin/marca/creador', [MarcaController::class, 'create'])->name('adm.marca-creador');
    Route::post('/admin/marca/store', [MarcaController::class, 'store'])->name('adm.marca-store');
    Route::get('/admin/marca/editor/{marca_id}', [MarcaController::class, 'show'])->name('adm.marca-editor');
    Route::post('/admin/marca/update', [MarcaController::class, 'update'])->name('adm.marca-update');
    Route::get('/admin/marca/destroy/{marca_id}', [MarcaController::class, 'destroy'])->name('adm.marca-destroy');
    Route::get('/admin/marca/switch/{marca_id}', [MarcaController::class, 'switch'])->name('adm.marca-switch');
    Route::get('/admin/marca/destacado/{marca_id}', [MarcaController::class, 'destacado'])->name('adm.marca-destacado');
    Route::post('/admin/marca/reordenar', [MarcaController::class, 'reordenar'])->name('adm.marca-reordenar');

    // productos
    Route::get('/admin/producto', [ProductoController::class, 'index'])->name('adm.producto');
    Route::get('/admin/producto/creador', [ProductoController::class, 'create'])->name('adm.producto-creador');
    Route::post('/admin/producto/store', [ProductoController::class, 'store'])->name('adm.producto-store');
    Route::get('/admin/producto/editor/{producto_id}', [ProductoController::class, 'show'])->name('adm.producto-editor');
    Route::post('/admin/producto/update', [ProductoController::class, 'update'])->name('adm.producto-update');
    Route::get('/admin/producto/destroy/{producto_id}', [ProductoController::class, 'destroy'])->name('adm.producto-destroy');
    Route::get('/admin/producto/switch/{producto_id}', [ProductoController::class, 'switch'])->name('adm.producto-switch');
    Route::get('/admin/producto/destacado/{producto_id}', [ProductoController::class, 'destacado'])->name('adm.producto-destacado');
    Route::get('/admin/producto/oferta/{producto_id}', [ProductoController::class, 'oferta'])->name('adm.producto-oferta');
    Route::get('/admin/producto/mas-vendido/{producto_id}', [ProductoController::class, 'masVendido'])->name('adm.producto-mas-vendido');
    Route::get('/admin/producto/descubre-mas/{producto_id}', [ProductoController::class, 'descubreMas'])->name('adm.producto-descubre-mas');
    Route::post('/admin/producto/import', [ProductoController::class, 'import'])->name('adm.producto-import');
    Route::post('/admin/producto/fotos-masivas', [ProductoController::class, 'fotosMasivas'])->name('adm.producto-fotos-masivas');
    Route::post('/admin/producto/fotos/store', [ProductoController::class, 'fotosStore'])->name('adm.producto-fotos-store');
    Route::post('/admin/producto/fotos/reordenar', [ProductoController::class, 'fotosReordenar'])->name('adm.producto-fotos-reordenar');
    Route::get('/admin/producto/fotos/destroy/{foto_id}', [ProductoController::class, 'fotosDestroy'])->name('adm.producto-fotos-destroy');
    Route::get('/admin/producto/fotos/switch/{foto_id}', [ProductoController::class, 'fotosSwitch'])->name('adm.producto-fotos-switch');
    Route::post('/admin/producto/reordenar', [ProductoController::class, 'reordenar'])->name('adm.producto-reordenar');

    //novedades
    Route::get('/admin/novedade', [NovedadeController::class, 'index'])->name('adm.novedade');
    Route::get('/admin/novedade/creador', [NovedadeController::class, 'create'])->name('adm.novedade-creador');
    Route::post('/admin/novedade/store', [NovedadeController::class, 'store'])->name('adm.novedade-store');
    Route::get('/admin/novedade/editor/{novedade_id}', [NovedadeController::class, 'show'])->name('adm.novedade-editor');
    Route::post('/admin/novedade/update', [NovedadeController::class, 'update'])->name('adm.novedade-update');
    Route::get('/admin/novedade/destroy/{novedade_id}', [NovedadeController::class, 'destroy'])->name('adm.novedade-destroy');
    Route::get('/admin/novedade/switch/{novedade_id}', [NovedadeController::class, 'switch'])->name('adm.novedade-switch');
    Route::get('/admin/novedade/destacado/{novedade_id}', [NovedadeController::class, 'destacado'])->name('adm.novedade-destacado');
    Route::post('/admin/novedade/reordenar', [NovedadeController::class, 'reordenar'])->name('adm.novedade-reordenar');

    // contacto 
    Route::get('/admin/contacto', [ContactoController::class, 'show'])->name('adm.contacto');
    Route::post('/admin/contacto/update', [ContactoController::class, 'update'])->name('adm.contacto.update');

     //web-usuarios
    Route::get('/admin/web-usuarios', [WebUsuarioController::class,'index'])->name('adm.web-usuarios');
    Route::get('/admin/web-usuarios/editor/{usuario_id}', [WebUsuarioController::class,'show'])->name('adm.web-usuarios-editor');
    Route::post('/admin/web-usuarios/import', [WebUsuarioController::class, 'import'])->name('adm.web-usuarios-import');
    Route::get('web-usuarios/export', [WebUsuarioController::class, 'export'])->name('adm.web-usuarios-export');
    Route::post('/admin/web-usuarios/update', [WebUsuarioController::class,'update'])->name('adm.web-usuarios-update');
    Route::get('/admin/web-usuarios/destroy/{usuario_id}', [WebUsuarioController::class,'destroy'])->name('adm.web-usuarios-destroy');
    Route::get('/admin/web-usuarios/switch/{usuario_id}', [WebUsuarioController::class,'switch'])->name('adm.web-usuarios-switch');

    // Lista
    Route::get('/admin/lista', [ListaController::class, 'index'])->name('adm.lista');
    Route::get('/admin/lista/creador', [ListaController::class, 'create'])->name('adm.lista-creador');
    Route::post('/admin/lista/store', [ListaController::class, 'store'])->name('adm.lista-store');
    Route::get('/admin/lista/editor/{lista_id}', [ListaController::class, 'show'])->name('adm.lista-editor');
    Route::post('/admin/lista/update', [ListaController::class, 'update'])->name('adm.lista-update');
    Route::get('/admin/lista/destroy/{lista_id}', [ListaController::class, 'destroy'])->name('adm.lista-destroy');
    Route::get('/admin/lista/switch/{lista_id}', [ListaController::class, 'switch'])->name('adm.lista-switch');
    Route::post('/admin/lista/reordenar', [ListaController::class, 'reordenar'])->name('adm.lista-reordenar');

    // Info
    Route::get('/admin/info', [InfoController::class, 'show'])->name('adm.info');
    Route::post('/admin/info/update', [InfoController::class, 'update'])->name('adm.info-update');

    //newsletter
    Route::get('/admin/newsletter', [NewsletterController::class,'index'])->name('adm.newsletter');
    
    Route::post('/admin/newsletter/send', [NewsletterController::class,'send'])->name('adm.newsletter-send'); // NUEVA RUTA
    Route::get('/admin/newsletter/destroy/{newsletter_id}', [NewsletterController::class,'destroy'])->name('adm.newsletter-destroy');
    Route::get('/admin/newsletter/switch/{newsletter_id}', [NewsletterController::class,'switch'])->name('adm.newsletter-switch');
    Route::post('/admin/newsletter/reordenar', [NewsletterController::class, 'reordenar'])->name('adm.newsletter-reordenar');

    //logo
    Route::get('/admin/logo', [LogoController::class, 'show'])->name('adm.logo');
    Route::post('/admin/logo/update', [LogoController::class,'update'])->name('adm.logo-update');

    //usuarios
    Route::get('/admin/usuarios', [UsuarioController::class,'index'])->name('adm.usuarios');
    Route::get('/admin/usuarios/creador', [UsuarioController::class,'create'])->name('adm.usuarios-creador');
    Route::post('/admin/usuarios/store', [UsuarioController::class,'store'])->name('adm.usuarios-store');
    Route::get('/admin/usuarios/editor/{usuario_id}', [UsuarioController::class,'show'])->name('adm.usuarios-editor');
    Route::post('/admin/usuarios/update', [UsuarioController::class,'update'])->name('adm.usuarios-update');
    Route::get('/admin/usuarios/destroy/{usuario_id}', [UsuarioController::class,'destroy'])->name('adm.usuarios-destroy');
    Route::get('/admin/usuarios/switch/{usuario_id}', [UsuarioController::class,'switch'])->name('adm.usuarios-switch');

    //historico
    Route::get('/admin/pedidos', [PedidoController::class, 'adminIndex'])->name('adm.pedidos-historico');
    Route::get('/admin/pedidos/editor/{pedido}', [PedidoController::class, 'adminShow'])->name('adm.pedidos-historico-editor');
    Route::post('/admin/pedidos/update', [PedidoController::class, 'adminUpdate'])->name('adm.pedidos-historico-update');
    Route::get('/admin/pedidos/destroy/{pedido}', [PedidoController::class, 'adminDestroy'])->name('adm.pedidos-historico-destroy');
    Route::get('/admin/pedidos/switch/{pedido}', [PedidoController::class, 'adminSwitch'])->name('adm.pedidos-historico-switch');

    //historico de presupuestos
    Route::get('/admin/presupuestos', [PresupuestoController::class, 'adminIndex'])->name('adm.presupuestos-historico');
    Route::get('/admin/presupuestos/editor/{presupuesto}', [PresupuestoController::class, 'adminShow'])->name('adm.presupuestos-historico-editor');
    Route::post('/admin/presupuestos/update', [PresupuestoController::class, 'adminUpdate'])->name('adm.presupuestos-historico-update');
    Route::get('/admin/presupuestos/destroy/{presupuesto}', [PresupuestoController::class, 'adminDestroy'])->name('adm.presupuestos-historico-destroy');

    //historico de canjes
    Route::get('/admin/canjes', [CanjeAdminController::class, 'index'])->name('adm.canjes-historico');
    Route::get('/admin/canjes/editor/{canje}', [CanjeAdminController::class, 'show'])->name('adm.canjes-historico-editor');
    Route::post('/admin/canjes/update', [CanjeAdminController::class, 'update'])->name('adm.canjes-historico-update');
    Route::get('/admin/canjes/destroy/{canje}', [CanjeAdminController::class, 'destroy'])->name('adm.canjes-historico-destroy');
    Route::get('/admin/canjes/switch/{canje}', [CanjeAdminController::class, 'switch'])->name('adm.canjes-historico-switch');

    //zona publica: usuarios registrados e historico de pedidos
    Route::get('/admin/usuarios-publicos', [UsuarioPublicoAdminController::class, 'index'])->name('adm.usuarios-publicos');
    Route::get('/admin/pedidos-publicos', [PedidoPublicoAdminController::class, 'index'])->name('adm.pedidos-publicos');
    Route::get('/admin/pedidos-publicos/editor/{pedido}', [PedidoPublicoAdminController::class, 'show'])->name('adm.pedidos-publicos-editor');
    Route::post('/admin/pedidos-publicos/update', [PedidoPublicoAdminController::class, 'update'])->name('adm.pedidos-publicos-update');
    Route::get('/admin/pedidos-publicos/destroy/{pedido}', [PedidoPublicoAdminController::class, 'destroy'])->name('adm.pedidos-publicos-destroy');
    Route::get('/admin/pedidos-publicos/switch/{pedido}', [PedidoPublicoAdminController::class, 'switch'])->name('adm.pedidos-publicos-switch');

    //metadatos
    Route::get('/admin/metadatos', [MetadatoController::class,'index'])->name('adm.metadatos');
    Route::get('/admin/metadatos/creador', [MetadatoController::class,'create'])->name('adm.metadatos-creador');
    Route::post('/admin/metadatos/store', [MetadatoController::class,'store'])->name('adm.metadatos-store');
    Route::get('/admin/metadatos/editor/{metadato_id}', [MetadatoController::class,'show'])->name('adm.metadatos-editor');
    Route::post('/admin/metadatos/update', [MetadatoController::class,'update'])->name('adm.metadatos-update');
    Route::get('/admin/metadatos/destroy/{metadato_id}', [MetadatoController::class,'destroy'])->name('adm.metadatos-destroy');

    //banners (uno por sección: nosotros, novedades, contacto, productos, ...)
    Route::get('/admin/banner/{seccion}', [BannerController::class, 'show'])->name('adm.banner');
    Route::post('/admin/banner/{seccion}', [BannerController::class, 'update'])->name('adm.banner-update');

    

    //módulos generados con php artisan make:modulo
    require __DIR__.'/modulos.php';
});

//contabilium (datos de la API en JSON)
    Route::get('/api', [ContabiliumApiController::class, 'cuentasCorrientes'])->name('adm.api');
    Route::get('/api/clientes', [ContabiliumApiController::class, 'clientes'])->name('adm.api-clientes');
    Route::get('/api/productos', [ContabiliumApiController::class, 'productos'])->name('adm.api-productos');
    
//idioma
Route::get('/idioma/{idioma}', function ($idioma) {
    if (in_array($idioma, \App\Support\Idiomas::todos())) {
        session(['idioma' => $idioma]);
    }
    return back();
})->name('idioma.cambiar');


//login
Route::get('/admin/login', [AuthController::class,'showLogin'])->name('login');
Route::post('/admin/login', [AuthController::class,'login'])->name('login.post');
Route::post('/admin/logout', [AuthController::class,'logout'])->name('logout');
Route::post('/admin/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');

// ============================================
// WEB USUARIOS (ZONA PRIVADA)
// ============================================
Route::post('/web/register', [AuthController::class, 'webRegister'])->name('web.register');
Route::post('/web/login', [AuthController::class, 'webLogin'])->name('web.login');
Route::post('/web/logout', [AuthController::class, 'webLogout'])->name('web.logout'); 

// ========== ZONA PRIVADA (requiere autenticación web_usuario) ==========
Route::middleware('web.usuario.auth')->group(function () {

    Route::get('/clientes/productos', [ProductoBusquedaController::class, 'webProductos'])->name('web-productos');

    Route::get('/clientes/carrito', [PedidoController::class, 'index'])->name('web-carrito');

    // Carrito API
    Route::post('/web/carrito/agregar', [PedidoController::class, 'agregar'])->name('web-carrito.agregar');
    Route::post('/web/carrito/actualizar', [PedidoController::class, 'actualizar'])->name('web-carrito.actualizar');
    Route::post('/web/carrito/eliminar', [PedidoController::class, 'eliminar'])->name('web-carrito.eliminar');
    Route::post('/web/carrito/vaciar', [PedidoController::class, 'vaciar'])->name('web-carrito.vaciar');
    Route::post('/web/carrito/completar', [PedidoController::class, 'completar'])->name('web-carrito.completar');
    Route::post('/web/carrito/presupuesto', [PresupuestoController::class, 'store'])->name('web-carrito.presupuesto');

    Route::get('/clientes/informacion-pago', function () {
        return view('content.web.webinfo');})->name('web-info');

    Route::post('/info-pago/enviar', [InfoPagoController::class, 'enviar'])
        ->name('info-pago.enviar');

    Route::get('/clientes/pedidos', [PedidoController::class, 'historico'])->name('web-pedidos');
    Route::get('/clientes/pedidos/{pedido}/pdf', [PedidoController::class, 'pdf'])->name('web-pedidos.pdf');
    Route::post('/clientes/pedidos/{pedido}/recomprar', [PedidoController::class, 'recomprar'])->name('web-pedidos.recomprar');

    Route::get('/clientes/canje-puntos', [CanjePuntosController::class, 'index'])->name('web-canje');
    Route::get('/clientes/canje-puntos/carrito', [CanjePuntosController::class, 'carrito'])->name('web-canje.carrito');
    Route::post('/clientes/canje-puntos/carrito/agregar', [CanjePuntosController::class, 'agregar'])->name('web-canje.agregar');
    Route::post('/clientes/canje-puntos/carrito/quitar', [CanjePuntosController::class, 'quitar'])->name('web-canje.quitar');
    Route::post('/clientes/canje-puntos/envio', [CanjePuntosController::class, 'guardarEnvio'])->name('web-canje.envio');
    Route::get('/clientes/canje-puntos/detalle', [CanjePuntosController::class, 'detalle'])->name('web-canje.detalle');
    Route::get('/clientes/canje-puntos/movimientos', [CanjePuntosController::class, 'movimientos'])->name('web-canje.movimientos');
    Route::get('/clientes/canje-puntos/movimientos/{canje}/ver', [CanjePuntosController::class, 'ver'])->name('web-canje.ver');
    Route::post('/clientes/canje-puntos/realizar', [CanjePuntosController::class, 'realizar'])->name('web-canje.realizar');

    Route::get('/clientes/presupuestos', [PresupuestoController::class, 'index'])->name('web-presupuestos');
    Route::get('/clientes/presupuestos/{presupuesto}/ver', [PresupuestoController::class, 'ver'])->name('web-presupuestos.ver');
    Route::get('/clientes/presupuestos/{presupuesto}/pdf', [PresupuestoController::class, 'descargar'])->name('web-presupuestos.pdf');
    Route::post('/clientes/presupuestos/{presupuesto}/pedido', [PresupuestoController::class, 'pasarAPedido'])->name('web-presupuestos.pedido');

    Route::get('/clientes/lista', function () {
        return view('content.web.weblista');
    })->name('web-lista');

});


Route::post('/admin/newsletter/store', [NewsletterController::class,'store'])->name('adm.newsletter-store');
//WEB
Route::get('/', function () {
    return view('content.web.home');})->name('home');

Route::get('/acceso', [AccesoPublicoController::class, 'show'])->name('web-acceso');
Route::post('/acceso/registro', [AccesoPublicoController::class, 'registro'])->name('acceso.registro');
Route::post('/acceso/login', [AccesoPublicoController::class, 'login'])->middleware('throttle:6,1')->name('acceso.login');
Route::post('/acceso/logout', [AccesoPublicoController::class, 'logout'])->name('acceso.logout');

// proceso de compra de los usuarios públicos
Route::middleware('usuario.publico.auth')->group(function () {
    Route::get('/compra/carrito', function () {
        return view('content.web.compra-carrito');
    })->name('compra.carrito');
    Route::get('/compra/envio', [CarritoPublicoController::class, 'pasoEnvio'])->name('compra.envio');
    Route::post('/compra/envio', [CarritoPublicoController::class, 'guardarEnvio'])->name('compra.envio.guardar');
    Route::get('/compra/pago', [CarritoPublicoController::class, 'pasoPago'])->name('compra.pago');
    Route::post('/compra/pago', [CarritoPublicoController::class, 'guardarPago'])->name('compra.pago.guardar');
    Route::get('/compra/finalizar', [CarritoPublicoController::class, 'pasoFinal'])->name('compra.finalizar');
    Route::post('/compra/finalizar', [CarritoPublicoController::class, 'realizarPedido'])->name('compra.realizar');
});

// carrito de los usuarios públicos
Route::middleware('usuario.publico.auth')->prefix('carrito-publico')->group(function () {
    Route::get('/', [CarritoPublicoController::class, 'index'])->name('carrito-publico');
    Route::post('/agregar', [CarritoPublicoController::class, 'agregar'])->name('carrito-publico.agregar');
    Route::post('/actualizar', [CarritoPublicoController::class, 'actualizar'])->name('carrito-publico.actualizar');
    Route::post('/eliminar', [CarritoPublicoController::class, 'eliminar'])->name('carrito-publico.eliminar');
});

Route::get('/nosotros', function () {
    return view('content.web.nosotros');})->name('nosotros');

Route::get('/productos', [ProductoBusquedaController::class, 'productos'])->name('productos');

Route::get('/productos/marca/{id}', [ProductoBusquedaController::class, 'categoria'])->name('productos.categoria');

// Producto detalle
Route::get('/productos/detalle/{id}', function ($id) {
    $producto = Producto::with('marca', 'fotos')->findOrFail($id);
    $related = Producto::with('marca')->where('marca_id', $producto->marca_id)
        ->where('active', true)
        ->where('id', '!=', $producto->id)
        ->orderBy('orden')
        ->limit(3)
        ->get();

    return view('content.web.productos-detalle', compact('producto', 'related'));
})->name('productos.detalle');

Route::get('/novedades', function () {
    return view('content.web.novedade');})->name('novedades');

Route::get('/novedades/detalle/{id}', [NovedadeController::class, 'detalle'])->name('novedades-detalle');

Route::get('/catalogo', function () {
    return view('content.web.catalogo');
})->name('catalogo');

Route::get('/contacto', function () {
    return view('content.web.contacto');})->name('contacto'); 


Route::post('/contacto/enviar', [FormController::class, 'contacto'])->name('form.contacto');
