<?php

use Illuminate\Support\Facades\Route;
use Modules\Headline\Http\Controllers\HeadlineController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('headlines', HeadlineController::class)->names('headline');
});
