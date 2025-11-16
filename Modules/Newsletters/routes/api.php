<?php

use Illuminate\Support\Facades\Route;
use Modules\Newsletters\Http\Controllers\NewslettersController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('newsletters', NewslettersController::class)->names('newsletters');
});
