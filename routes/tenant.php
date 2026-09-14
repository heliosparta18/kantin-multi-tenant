<?php

use Illuminate\Support\Facades\Route;

Route::get('/tenant/dashboard', function () {
    return view('tenant.index');
});
