<?php

use Illuminate\Support\Facades\Route;

Route::view('/{path?}', 'spa')->where('path', '^(?!api).*');
