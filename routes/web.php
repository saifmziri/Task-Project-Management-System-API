<?php
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Support\Facades\Route;

// معالجة الملفات الافتراضية التي يطلبها ZAP بشكل مباشر وبدون جلسات (Sessions)
Route::middleware([SecurityHeaders::class])->group(function () {
    Route::get('/sitemap.xml', function () {
        return response()->json(['message' => 'Not Found'], 404);
    });
    
    Route::get('/robots.txt', function () {
        return response()->json(['message' => 'Not Found'], 404);
    });
});