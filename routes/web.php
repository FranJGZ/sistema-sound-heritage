<?php

use App\Livewire\HomeTienda; 
use App\Livewire\CatalogoProductos;
use App\Livewire\CatalogoEventos;
use App\Livewire\ReservarSalon;
use App\Livewire\CheckoutTienda;
use App\Livewire\DetalleProducto;
use App\Livewire\DetalleEvento;
use App\Livewire\MiCuenta;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// -----------------------------------------------------------------------------
// RUTAS PÚBLICAS
// -----------------------------------------------------------------------------
Route::get('/', HomeTienda::class)->name('home');
Route::get('/productos', CatalogoProductos::class)->name('productos');
Route::get('/ofertas', CatalogoProductos::class)->name('ofertas');
Route::get('/eventos', CatalogoEventos::class)->name('eventos');
Route::get('/reservar-salon', ReservarSalon::class)->name('reservar-salon');
Route::get('/checkout', CheckoutTienda::class)->name('checkout');
Route::redirect('/carrito', '/checkout')->name('carrito');
Route::get('/checkout/success', CheckoutTienda::class)->name('checkout.success');
Route::get('/checkout/pending', CheckoutTienda::class)->name('checkout.pending');
Route::get('/checkout/failure', CheckoutTienda::class)->name('checkout.failure');
Route::get('/producto/{id}', DetalleProducto::class)->name('producto.detalle');
Route::get('/evento/{id}', DetalleEvento::class)->name('evento.detalle');

// -----------------------------------------------------------------------------
// RUTAS BÁSICAS DE AUTENTICACIÓN (Cualquiera que inicie sesión)
// -----------------------------------------------------------------------------
Route::get('/dashboard', function () {
    if (auth()->check() && auth()->user()->hasRole('Administrador')) {
        return redirect('/admin');
    }
    return redirect('/');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Dashboard de cuenta estilo CompraGamer de Sound Heritage
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/mi-cuenta', MiCuenta::class)->name('mi-cuenta');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// -----------------------------------------------------------------------------
// MÓDULO DE USUARIOS Y ROLES (Protegido por Spatie)
// -----------------------------------------------------------------------------

/* * NIVEL 1: Lectura. 
 * Separamos el "index" para que en el futuro más roles (ej: Supervisor, Director) 
 * puedan entrar a ver la tabla. Aquí es donde brilla el @can en la vista.
 */
Route::middleware(['auth', 'role:Administrador|Supervisor'])->group(function () {
    Route::get('/usuarios', [UserController::class, 'index'])->name('users.index'); 
});

/* * NIVEL 2: Escritura/Edición. 
 * Estas rutas son críticas. Las dejamos en un grupo exclusivo donde SOLO 
 * el Administrador puede entrar a ver el formulario y guardar cambios.
 */
Route::middleware(['auth', 'role:Administrador'])->group(function () {
    Route::get('/usuarios/{user}/roles', [UserController::class, 'editRoles'])->name('users.roles.edit');
    Route::put('/usuarios/{user}/roles', [UserController::class, 'updateRoles'])->name('users.roles.update');
});



// -----------------------------------------------------------------------------

require __DIR__.'/auth.php';