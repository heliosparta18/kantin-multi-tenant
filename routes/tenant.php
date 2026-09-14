<?php

use App\Models\Menu;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

Route::get('/tenant/dashboard', function () {
    return view('tenant.index');
});

Route::middleware('tenant.context')
    ->prefix('tenant/{tenant:slug}')
    ->name('tenant.')
    ->scopeBindings()
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('tenant.index');
        })->name('dashboard');

        // Menus
        Route::get('/menus', function () {
            return response()->json(Menu::all());
        })->name('menus.index');

        Route::post('/menus', function (Request $request) {
            $menu = Menu::create($request->all());

            return response()->json($menu, 201);
        })->name('menus.store');

        Route::get('/menus/{menu}', function (Tenant $tenant, Menu $menu) {
            Gate::authorize('view', $menu);

            return response()->json($menu);
        })->name('menus.show');

        Route::put('/menus/{menu}', function (Request $request, Tenant $tenant, Menu $menu) {
            Gate::authorize('update', $menu);
            $menu->update($request->only(['name', 'price', 'is_available']));

            return response()->json($menu);
        })->name('menus.update');

        Route::delete('/menus/{menu}', function (Tenant $tenant, Menu $menu) {
            Gate::authorize('delete', $menu);
            $menu->delete();

            return response()->json(['status' => 'deleted']);
        })->name('menus.destroy');

        // Orders
        Route::get('/orders', function () {
            return response()->json(Order::all());
        })->name('orders.index');

        Route::get('/orders/{order}', function (Tenant $tenant, Order $order) {
            Gate::authorize('view', $order);

            return response()->json($order);
        })->name('orders.show');

        Route::put('/orders/{order}', function (Request $request, Tenant $tenant, Order $order) {
            Gate::authorize('update', $order);
            $order->update($request->only(['status']));

            return response()->json($order);
        })->name('orders.update');

        // Withdrawals
        Route::get('/withdrawals', function () {
            return response()->json(Withdrawal::all());
        })->name('withdrawals.index');

        Route::post('/withdrawals', function (Request $request) {
            $withdrawal = Withdrawal::create($request->all());

            return response()->json($withdrawal, 201);
        })->name('withdrawals.store');

        Route::get('/withdrawals/{withdrawal}', function (Tenant $tenant, Withdrawal $withdrawal) {
            Gate::authorize('view', $withdrawal);

            return response()->json($withdrawal);
        })->name('withdrawals.show');
    });
