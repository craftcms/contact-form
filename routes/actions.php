<?php

declare(strict_types=1);

use CraftCms\ContactForm\Http\Controllers\SendController;
use Illuminate\Support\Facades\Route;

// Keeps the `contact-form/send` action path from 3.x. Submissions are anonymous, so there’s no `auth` middleware.
Route::post('send', SendController::class)
    ->middleware('throttle:contact-form');
