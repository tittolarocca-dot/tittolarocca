<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

// Language switcher (no locale prefix needed)
Route::get('/lang/{locale}', [LocaleController::class, 'switch'])
    ->where('locale', implode('|', SetLocale::SUPPORTED))
    ->name('lang.switch');

// Root redirect → default locale
Route::get('/', fn () => redirect('/' . SetLocale::DEFAULT));

// ── Public routes with /{locale} prefix (SEO-friendly) ───────────────────────
Route::prefix('{locale}')
    ->where(['locale' => implode('|', SetLocale::SUPPORTED)])
    ->middleware('setlocale')
    ->group(function () {
        Route::get('/',                          [HomeController::class,  'index'])->name('home');
        Route::get('/stadt/{city:slug}',         [HomeController::class,  'city'])->name('city');
        Route::get('/kategorie/{category:slug}', [HomeController::class,  'category'])->name('category');
        Route::get('/service/{tag:slug}',        [HomeController::class,  'service'])->name('service');
        Route::get('/profil/{profile:slug}',     [ProfileController::class, 'show'])->name('profile.show');
    });

// ── Auth ──────────────────────────────────────────────────────────────────────
Route::middleware(['guest', 'setlocale'])->group(function () {
    Route::get('/registrieren',  [RegisterController::class, 'create'])->name('register');
    Route::post('/registrieren', [RegisterController::class, 'store']);
    Route::get('/login',         [LoginController::class,   'create'])->name('login');
    Route::post('/login',        [LoginController::class,   'store']);
});
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout')->middleware('auth');

// ── Inserent-Bereich ──────────────────────────────────────────────────────────
Route::middleware(['auth', 'setlocale'])->prefix('inserat')->name('inserat.')->group(function () {
    Route::get('/dashboard',   [\App\Http\Controllers\Inserent\DashboardController::class,  'index'])->name('dashboard');
    Route::get('/profil',      [\App\Http\Controllers\Inserent\ProfileController::class,    'edit'])->name('profile.edit');
    Route::post('/profil',     [\App\Http\Controllers\Inserent\ProfileController::class,    'store'])->name('profile.store');
    Route::put('/profil',      [\App\Http\Controllers\Inserent\ProfileController::class,    'update'])->name('profile.update');
    Route::delete('/profil',   [\App\Http\Controllers\Inserent\ProfileController::class,    'destroy'])->name('profile.destroy');
    Route::get('/paket',       [\App\Http\Controllers\Inserent\ListingController::class,    'selectPackage'])->name('package.select');
    Route::post('/paket/pay',  [\App\Http\Controllers\Inserent\ListingController::class,    'checkout'])->name('package.checkout');
    Route::post('/pushen',     [\App\Http\Controllers\Inserent\ListingController::class,    'push'])->name('push');
    Route::get('/medien',      [\App\Http\Controllers\Inserent\MediaController::class,      'index'])->name('media.index');
    Route::post('/medien',     [\App\Http\Controllers\Inserent\MediaController::class,      'store'])->name('media.store');
    Route::delete('/medien/{media}',        [\App\Http\Controllers\Inserent\MediaController::class, 'destroy'])->name('media.destroy');
    Route::post('/medien/reihenfolge',      [\App\Http\Controllers\Inserent\MediaController::class, 'reorder'])->name('media.reorder');
    Route::get('/nachrichten',              [\App\Http\Controllers\Inserent\MessageController::class, 'index'])->name('messages');
    Route::post('/nachrichten/{userId}',    [\App\Http\Controllers\Inserent\MessageController::class, 'reply'])->name('messages.reply');
    Route::post('/nachrichten/{userId}/ppv',[\App\Http\Controllers\Inserent\MessageController::class, 'sendPpv'])->name('messages.ppv');
    Route::get('/nachrichten/{userId}/verlauf', [\App\Http\Controllers\Inserent\MessageController::class, 'conversation'])->name('messages.conversation');
    Route::get('/auszahlungen',             [\App\Http\Controllers\Inserent\PayoutController::class,  'index'])->name('payouts');
    Route::post('/auszahlungen/bankdaten',  [\App\Http\Controllers\Inserent\PayoutController::class,  'updateBankDetails'])->name('payouts.bank');
    Route::post('/bewertung/{review}/antworten', [\App\Http\Controllers\Member\ReviewController::class, 'reply'])->name('review.reply');
});

// ── Mitglieder-Bereich ────────────────────────────────────────────────────────
Route::middleware(['auth', 'setlocale'])->prefix('konto')->name('konto.')->group(function () {
    Route::get('/',              [\App\Http\Controllers\Member\DashboardController::class,    'index'])->name('dashboard');
    Route::get('/abonnements',   [\App\Http\Controllers\Member\SubscriptionController::class, 'index'])->name('subscriptions');
    Route::post('/abonnieren/{profile:slug}',  [\App\Http\Controllers\Member\SubscriptionController::class, 'subscribe'])->name('subscribe');
    Route::post('/gratis-test/{profile:slug}', [\App\Http\Controllers\Member\SubscriptionController::class, 'trial'])->name('trial');
    Route::post('/kuendigen/{profile:slug}',   [\App\Http\Controllers\Member\SubscriptionController::class, 'cancel'])->name('cancel');
    Route::get('/nachrichten',                              [\App\Http\Controllers\Member\MessageController::class, 'index'])->name('messages');
    Route::post('/nachrichten/{profile:slug}',             [\App\Http\Controllers\Member\MessageController::class, 'send'])->name('messages.send');
    Route::post('/nachrichten/{profile:slug}/bild',        [\App\Http\Controllers\Member\MessageController::class, 'sendMedia'])->name('messages.send.media');
    Route::get('/nachrichten/{userId}/verlauf',            [\App\Http\Controllers\Member\MessageController::class, 'conversation'])->name('messages.conversation');
    Route::post('/nachrichten/{message}/ppv-kaufen',       [\App\Http\Controllers\Member\MessageController::class, 'ppvCheckout'])->name('messages.ppv.checkout');
    Route::post('/bewertung/{profile:slug}',               [\App\Http\Controllers\Member\ReviewController::class,  'store'])->name('review.store');
});

// ── Stripe Webhooks ───────────────────────────────────────────────────────────
Route::post('/stripe/webhook', [\App\Http\Controllers\StripeWebhookController::class, 'handle'])
    ->name('stripe.webhook')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// ── Media streams ─────────────────────────────────────────────────────────────
Route::get('/media/{media}',       [\App\Http\Controllers\MediaStreamController::class, 'show'])->name('media.stream');
Route::get('/media/ppv/{message}', [\App\Http\Controllers\MediaStreamController::class, 'ppv'])->middleware('auth')->name('media.ppv');

// ── Payment pages ─────────────────────────────────────────────────────────────
Route::get('/zahlung/erfolg',      fn () => inertia('Payment/Success'))->name('payment.success');
Route::get('/zahlung/abgebrochen', fn () => inertia('Payment/Cancelled'))->name('payment.cancelled');
