<?php

use App\Http\Controllers\NetflowDataController;
use App\Http\Middleware\IpAddressCheck;
use Illuminate\Support\Facades\Route;

Route::post("/process_data", [NetflowDataController::class, "receiveFile"])
    ->middleware(IpAddressCheck::class);
