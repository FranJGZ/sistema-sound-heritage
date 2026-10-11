<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;

// 1. Ruta para crear productos "al vuelo" con los moldes (templates)
Route::post('/products/quick', [ProductController::class, 'storeQuick']);

// 2. Rutas automáticas para el catálogo de productos (excepto la de crear normal)
Route::apiResource('products', ProductController::class)->except(['store']);

// 3. Rutas automáticas para registrar y anular compras
Route::apiResource('purchases', PurchaseController::class);