<?php

use App\Http\Controllers\Admin\AsistenLogController as AdminAsistenLogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\PortfolioController as AdminPortfolioController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\ShippingController as AdminShippingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AsistenController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\User\CartController as UserCartController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\OrderController as UserOrderController;
use App\Http\Controllers\User\PaymentController as UserPaymentController;
use App\Http\Controllers\User\ProductController as UserProductController;
use App\Http\Controllers\User\ProfileController as UserProfileController;
use App\Http\Controllers\User\ShippingApiController as UserShippingApiController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicPageController::class, 'home'])->name('home');
Route::get('/galeri-layanan', [PublicPageController::class, 'galeri'])->name('galeri');
Route::get('/tentang-kami', [PublicPageController::class, 'about'])->name('about');

// Guest can browse products and view details without login
Route::get('/products', [UserProductController::class, 'index'])->name('products.guest');
Route::get('/products/{id}', [UserProductController::class, 'show'])->name('products.guest.show');

// Public cart (guest + authenticated user)
Route::get('/cart', [UserCartController::class, 'index'])->name('cart.index');
Route::post('/cart', [UserCartController::class, 'store'])->name('cart.store');
Route::put('/cart/{id}', [UserCartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{id}', [UserCartController::class, 'destroy'])->name('cart.destroy');
Route::get('/checkout', function () {
    $items = request()->query('items');
    if (is_array($items)) {
        $items = implode(',', $items);
    }
    $items = (string) $items;

    if (!Auth::check()) {
        return redirect()->route('login')->with('notice', 'Silakan login terlebih dahulu untuk melanjutkan checkout.');
    }

    return redirect()->route('user.orders.checkout', [
        'items' => $items,
    ]);
})->name('checkout.guard');

Route::post('/asisten/message', [AsistenController::class, 'message'])->name('asisten.message');
Route::post('/public/asisten/message', [AsistenController::class, 'message']);

Route::post('/payments/midtrans/callback', [UserPaymentController::class, 'midtransCallback'])
    ->name('payments.midtrans.callback');

// Auth routes (login/register)
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('admin')->middleware('role:admin')->group(function (): void {
        Route::get('/', fn () => redirect()->route('admin.dashboard'));
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::post('/dashboard/target', [AdminDashboardController::class, 'updateTarget'])->name('admin.dashboard.target');
        Route::get('/dashboard/export-sales', [AdminDashboardController::class, 'downloadSalesExcel'])->name('admin.dashboard.export-sales');

        Route::get('/products', [AdminProductController::class, 'index'])->name('admin.products.index');
        Route::post('/products', [AdminProductController::class, 'store'])->name('admin.products.store');
        Route::put('/products/{id}', [AdminProductController::class, 'update'])->name('admin.products.update');
        Route::delete('/products/{id}', [AdminProductController::class, 'destroy'])->name('admin.products.destroy');

        Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users.index');
        Route::post('/users', [AdminUserController::class, 'store'])->name('admin.users.store');
        Route::put('/users/{id}', [AdminUserController::class, 'update'])->name('admin.users.update');
        Route::delete('/users/{id}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');

        Route::get('/shipping', [AdminShippingController::class, 'index'])->name('admin.shipping.index');
        Route::put('/shipping/{id}/status', [AdminShippingController::class, 'updateStatus'])->name('admin.shipping.status');

        Route::get('/portfolio', [AdminPortfolioController::class, 'index'])->name('admin.portfolio.index');
        Route::post('/portfolio', [AdminPortfolioController::class, 'store'])->name('admin.portfolio.store');
        Route::put('/portfolio/{id}', [AdminPortfolioController::class, 'update'])->name('admin.portfolio.update');
        Route::delete('/portfolio/{id}', [AdminPortfolioController::class, 'destroy'])->name('admin.portfolio.destroy');

        Route::get('/asisten-faq', [AdminFaqController::class, 'index'])->name('admin.faq.index');
        Route::post('/asisten-faq', [AdminFaqController::class, 'store'])->name('admin.faq.store');
        Route::put('/asisten-faq/{id}', [AdminFaqController::class, 'update'])->name('admin.faq.update');
        Route::delete('/asisten-faq/{id}', [AdminFaqController::class, 'destroy'])->name('admin.faq.destroy');

        Route::get('/asisten-logs', [AdminAsistenLogController::class, 'index'])->name('admin.asisten-logs.index');

        Route::get('/reviews', [AdminReviewController::class, 'index'])->name('admin.reviews.index');
        Route::delete('/reviews/{id}', [AdminReviewController::class, 'destroy'])->name('admin.reviews.destroy');

        Route::get('/profile', [AdminProfileController::class, 'edit'])->name('admin.profile.edit');
        Route::put('/profile', [AdminProfileController::class, 'update'])->name('admin.profile.update');
    });

    Route::prefix('user')->middleware('role:user')->group(function (): void {
        Route::get('/', fn () => redirect()->route('home'));
        Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');

        Route::get('/products', [UserProductController::class, 'index'])->name('user.products.index');
        Route::get('/products/{id}', [UserProductController::class, 'show'])->name('user.products.show');

        // Backward-compatible aliases
        Route::get('/cart', fn () => redirect()->route('cart.index'))->name('user.cart.index');
        Route::post('/cart', [UserCartController::class, 'store'])->name('user.cart.store');
        Route::put('/cart/{id}', [UserCartController::class, 'update'])->name('user.cart.update');
        Route::delete('/cart/{id}', [UserCartController::class, 'destroy'])->name('user.cart.destroy');

        Route::get('/checkout', [UserOrderController::class, 'checkout'])->name('user.orders.checkout');
        Route::post('/checkout', [UserOrderController::class, 'place'])->name('user.orders.place');
        Route::get('/invoice', [UserOrderController::class, 'invoice'])->name('user.orders.invoice');
        Route::get('/invoice/pdf', [UserOrderController::class, 'invoicePdf'])->name('user.orders.invoice.pdf');
        Route::get('/orders', [UserOrderController::class, 'history'])->name('user.orders.history');
        Route::get('/track', [UserOrderController::class, 'track'])->name('user.orders.track');
        Route::post('/track/complete', [UserOrderController::class, 'markArrived'])->name('user.orders.track.complete');
        Route::post('/track/review', [UserOrderController::class, 'submitReview'])->name('user.orders.track.review');
        Route::post('/payments/snap-token', [UserPaymentController::class, 'snapToken'])->name('user.payments.snap-token');
        Route::post('/payments/verify', [UserPaymentController::class, 'verifyStatus'])->name('user.payments.verify');
        Route::post('/shipping/estimate', [UserShippingApiController::class, 'estimate'])->name('user.shipping.estimate');
        Route::get('/shipping/destinations', [UserShippingApiController::class, 'destinations'])->name('user.shipping.destinations');

        Route::get('/profile', [UserProfileController::class, 'edit'])->name('user.profile.edit');
        Route::put('/profile', [UserProfileController::class, 'update'])->name('user.profile.update');
    });
});
