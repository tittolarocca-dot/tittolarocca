<?php

use App\Http\Controllers\MediaStreamController;
use Illuminate\Support\Facades\Route;

/*
 * Öffentliche Bild-/Video-Auslieferung – bewusst OHNE die Web-Gruppe:
 * keine Session, keine Cookies, kein CSRF. Nur Route-Model-Binding.
 * Dadurch tragen die Antworten KEIN Set-Cookie und sind CDN-cachebar.
 * Es werden ausschliesslich public+approved Medien ausgeliefert
 * (serverseitig in pubStream/pubVariant geprüft).
 */
Route::get('/media/pub/{media}',           [MediaStreamController::class, 'pubStream'])->name('media.pub.stream');
Route::get('/media/pub/{media}/{variant}', [MediaStreamController::class, 'pubVariant'])
    ->where('variant', 'thumbnail|card|full')->name('media.pub.variant');
