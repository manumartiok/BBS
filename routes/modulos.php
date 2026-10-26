<?php

/*
|--------------------------------------------------------------------------
| Rutas de módulos generados
|--------------------------------------------------------------------------
|
| php artisan make:modulo agrega acá las rutas de cada módulo nuevo.
| Este archivo se carga desde routes/web.php, dentro del grupo AdminAuth.
|
*/

use Illuminate\Support\Facades\Route;

// Catalogos (make:modulo)
Route::get('/admin/catalogo', [\App\Http\Controllers\CatalogoController::class, 'index'])->name('adm.catalogo');
Route::get('/admin/catalogo/creador', [\App\Http\Controllers\CatalogoController::class, 'create'])->name('adm.catalogo-creador');
Route::post('/admin/catalogo/store', [\App\Http\Controllers\CatalogoController::class, 'store'])->name('adm.catalogo-store');
Route::get('/admin/catalogo/editor/{id}', [\App\Http\Controllers\CatalogoController::class, 'show'])->name('adm.catalogo-editor');
Route::post('/admin/catalogo/update/{id}', [\App\Http\Controllers\CatalogoController::class, 'update'])->name('adm.catalogo-update');
Route::get('/admin/catalogo/destroy/{id}', [\App\Http\Controllers\CatalogoController::class, 'destroy'])->name('adm.catalogo-destroy');
Route::get('/admin/catalogo/switch/{id}', [\App\Http\Controllers\CatalogoController::class, 'switch'])->name('adm.catalogo-switch');
Route::post('/admin/catalogo/reordenar', [\App\Http\Controllers\CatalogoController::class, 'reordenar'])->name('adm.catalogo-reordenar');
