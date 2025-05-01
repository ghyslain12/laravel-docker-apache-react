<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/docs/api-docs.yaml', function () {
    $filePath = '/var/www/storage/api-docs/api-docs.yaml';
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        return Response::make($content, 200, [
            'Content-Type' => 'application/yaml',
            'Content-Disposition' => 'inline',
        ]);
    }
    abort(404, 'File not found');
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/crud-angular/{any?}', function () {
    return file_get_contents('/var/www/public/crud-angular/index.html');
})->where('any', '.*');