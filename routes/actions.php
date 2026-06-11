<?php

use CraftCms\ContactForm\Http\Controllers\SendController;

Route::post('send', SendController::class)
    ->middleware('throttle:contact-form');
