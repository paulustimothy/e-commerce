<?php

use App\Livewire\Auth\Forgot;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\Reset;
use App\Livewire\Cancel;
use App\Livewire\Cart;
use App\Livewire\Categories;
use App\Livewire\Checkout;
use App\Livewire\HomePage;
use App\Livewire\MyOrders;
use App\Livewire\MyOrdersDetail;
use App\Livewire\Product;
use App\Livewire\ProductDetail;
use App\Livewire\Success;
use Illuminate\Support\Facades\Route;

Route::get('/', HomePage::class);
Route::get('/categories', Categories::class);
Route::get('/products', Product::class);
Route::get('/cart', Cart::class);
Route::get('/products/{slug}', ProductDetail::class);

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class);
    Route::get('/forgot', Forgot::class);
    Route::get('/reset/{token}', Reset::class);
});

Route::middleware('auth')->group(function () {
    Route::get('/logout', function () {
        auth()->logout();
        return redirect('/');
    });
    Route::get('/checkout', Checkout::class);
    Route::get('/myorders', MyOrders::class);
    Route::get('/myorders/{order_id}', MyOrdersDetail::class)->name('myorders.show');
    Route::get('/success', Success::class)->name('success');
    Route::get('/cancel', Cancel::class)->name('cancel');
});
