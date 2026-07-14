<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

// Language switcher – stores locale in session, redirects back
Route::get('/lang/{locale}', [LocaleController::class, 'switch'])
    ->name('lang.switch');

// ── Public ────────────────────────────────────────────────────────────────────
Route::get('/',                          [HomeController::class,   'index'])->name('home');
Route::get('/suchen',                    [SearchController::class, 'index'])->name('search');
Route::get('/stadt/{city:slug}',         [HomeController::class,   'city'])->name('city');
Route::get('/kategorie/{category:slug}', [HomeController::class,   'category'])->name('category');
Route::get('/service/{tag:slug}',        [HomeController::class,   'service'])->name('service');
Route::get('/profil/{profile:slug}',     [ProfileController::class,'show'])->name('profile.show');
Route::get('/neue-bilder',               [\App\Http\Controllers\NewImagesController::class, 'index'])->name('neue-bilder');
Route::get('/mitglied/{user}',           [\App\Http\Controllers\Member\MemberProfileController::class, 'show'])->name('mitglied.show');

// ── Auth ──────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/registrieren',  [RegisterController::class, 'create'])->name('register');
    Route::post('/registrieren', [RegisterController::class, 'store']);
    Route::get('/login',         [LoginController::class,   'create'])->name('login');
    Route::post('/login',        [LoginController::class,   'store']);
});
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout')->middleware('auth');

// ── Admin: Verifikationsfoto streamen (nur für Admins) ────────────────────────
Route::get('/admin/verification-photo/{profile}', function (\App\Models\Profile $profile) {
    abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);
    abort_unless($profile->verification_photo && \Illuminate\Support\Facades\Storage::disk('local')->exists($profile->verification_photo), 404);
    return response()->file(storage_path('app/' . $profile->verification_photo));
})->middleware('auth')->name('admin.verification.photo');

// ── Inserent-Bereich ──────────────────────────────────────────────────────────
Route::middleware(['auth'])->prefix('inserat')->name('inserat.')->group(function () {
    Route::get('/dashboard',   [\App\Http\Controllers\Inserent\DashboardController::class,  'index'])->name('dashboard');
    Route::get('/profil',      [\App\Http\Controllers\Inserent\ProfileController::class,    'edit'])->name('profile.edit');
    Route::post('/profil',     [\App\Http\Controllers\Inserent\ProfileController::class,    'store'])->name('profile.store');
    Route::put('/profil',      [\App\Http\Controllers\Inserent\ProfileController::class,    'update'])->name('profile.update');
    Route::delete('/profil',   [\App\Http\Controllers\Inserent\ProfileController::class,    'destroy'])->name('profile.destroy');
    Route::post('/profil/deaktivieren', [\App\Http\Controllers\Inserent\ProfileController::class, 'deactivate'])->name('profile.deactivate');
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
    Route::get('/auszahlungen',             [\App\Http\Controllers\Inserent\PayoutController::class, 'index'])->name('payouts');
    Route::post('/auszahlungen/bankdaten',  [\App\Http\Controllers\Inserent\PayoutController::class, 'updateBankDetails'])->name('payouts.bank');
    Route::post('/bewertung/{review}/antworten', [\App\Http\Controllers\Member\ReviewController::class, 'reply'])->name('review.reply');
    Route::post('/verifikation',  [\App\Http\Controllers\Inserent\VerificationController::class, 'store'])->name('verification.store');
    Route::post('/reaktivieren', [\App\Http\Controllers\Inserent\ListingController::class,       'reactivate'])->name('reactivate');
    Route::get('/besucher',      [\App\Http\Controllers\Inserent\ListingVisitorController::class, 'index'])->name('visitors');
});

// ── Mitglieder-Bereich ────────────────────────────────────────────────────────
Route::middleware(['auth'])->prefix('konto')->name('konto.')->group(function () {
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
    Route::post('/bewertung/{profile:slug}',               [\App\Http\Controllers\Member\ReviewController::class, 'store'])->name('review.store');
    Route::get('/favoriten',                               [\App\Http\Controllers\Member\FavoriteController::class, 'index'])->name('favorites');
    Route::post('/favoriten/{profile:slug}',               [\App\Http\Controllers\Member\FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::get('/profilbesucher',                          [\App\Http\Controllers\Member\ProfileVisitorController::class, 'index'])->name('visitors');
});

// ── Stripe Webhooks ───────────────────────────────────────────────────────────
Route::post('/stripe/webhook', [\App\Http\Controllers\StripeWebhookController::class, 'handle'])
    ->name('stripe.webhook')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// ── Media streams ─────────────────────────────────────────────────────────────
Route::get('/media/{media}',       [\App\Http\Controllers\MediaStreamController::class, 'show'])->name('media.stream');
Route::get('/media/{media}/vorschau', [\App\Http\Controllers\MediaStreamController::class, 'preview'])->name('media.preview');
Route::get('/media/{media}/{variant}', [\App\Http\Controllers\MediaStreamController::class, 'variant'])
    ->where('variant', 'thumbnail|card|full')->name('media.variant');
Route::get('/media/ppv/{message}', [\App\Http\Controllers\MediaStreamController::class, 'ppv'])->middleware('auth')->name('media.ppv');

// ── Payment pages ─────────────────────────────────────────────────────────────
Route::get('/zahlung/erfolg',      fn () => inertia('Payment/Success'))->name('payment.success');
Route::get('/zahlung/abgebrochen', fn () => inertia('Payment/Cancelled'))->name('payment.cancelled');
