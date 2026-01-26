<?php

use App\Http\Controllers\CoverLetterController;
use App\Http\Controllers\CvController;
use App\Http\Controllers\CvShareController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\OnboardingController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('/about', function () {
    return Inertia::render('landing/About');
})->name('about');

Route::get('/services', function () {
    return Inertia::render('landing/Services');
})->name('services');

Route::get('/pricing', function () {
    return Inertia::render('landing/Pricing');
})->name('pricing');

Route::get('/contact', function () {
    return Inertia::render('landing/Contact');
})->name('contact');

// Onboarding routes (for authenticated & verified users who haven't completed onboarding)
Route::middleware(['auth', 'verified'])->prefix('onboarding')->name('onboarding.')->group(function () {
    Route::get('/', [OnboardingController::class, 'welcome'])->name('welcome');
    Route::get('/profile', [OnboardingController::class, 'profile'])->name('profile');
    Route::post('/profile', [OnboardingController::class, 'storeProfile'])->name('profile.store');
    Route::get('/interests', [OnboardingController::class, 'interests'])->name('interests');
    Route::post('/interests', [OnboardingController::class, 'storeInterests'])->name('interests.store');
    Route::get('/complete', [OnboardingController::class, 'complete'])->name('complete');
    Route::post('/finish', [OnboardingController::class, 'finish'])->name('finish');
});

// Protected routes (require auth, verified email, and completed onboarding)
Route::middleware(['auth', 'verified', 'onboarding'])->group(function () {
    // User Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
    Route::post('dashboard/chat', [DashboardController::class, 'chat'])->name('dashboard.chat');

    // Job Applications
    Route::resource('jobs', JobApplicationController::class)->parameters(['jobs' => 'job']);
    Route::post('jobs/crawl', [JobApplicationController::class, 'crawl'])->name('jobs.crawl');
    Route::patch('jobs/{job}/status', [JobApplicationController::class, 'updateStatus'])->name('jobs.update-status');
    Route::post('jobs/{job}/analyze', [JobApplicationController::class, 'analyze'])->name('jobs.analyze');

    // CVs
    Route::post('cvs/generate', [CvController::class, 'generate'])->name('cvs.generate');
    Route::post('cvs/upload', [CvController::class, 'upload'])->name('cvs.upload');
    Route::resource('cvs', CvController::class);
    Route::get('cvs/{cv}/file', [CvController::class, 'file'])->name('cvs.file');
    Route::post('cvs/{cv}/suggestions', [CvController::class, 'suggestions'])->name('cvs.suggestions');
    Route::post('cvs/{cv}/generate-summary', [CvController::class, 'generateSummary'])->name('cvs.generate-summary');
    Route::post('cvs/{cv}/rewrite-section', [CvController::class, 'rewriteSection'])->name('cvs.rewrite-section');
    Route::get('cvs/{cv}/versions', [CvController::class, 'versions'])->name('cvs.versions');
    Route::post('cvs/{cv}/restore/{versionId}', [CvController::class, 'restoreVersion'])->name('cvs.restore-version');
    Route::post('cvs/{cv}/chat', [CvController::class, 'chat'])->name('cvs.chat');
    Route::get('cvs/{cv}/export/pdf', [CvController::class, 'exportPdf'])->name('cvs.export.pdf');
    Route::get('cvs/{cv}/export/docx', [CvController::class, 'exportDocx'])->name('cvs.export.docx');

    // Cover Letters
    Route::resource('cover-letters', CoverLetterController::class);
    Route::post('cover-letters/generate', [CoverLetterController::class, 'generate'])->name('cover-letters.generate');
    Route::post('cover-letters/{cover_letter}/improve', [CoverLetterController::class, 'improve'])->name('cover-letters.improve');

    // Admin routes
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/users', [\App\Http\Controllers\Admin\AdminController::class, 'users'])->name('users');
        Route::get('/cvs', [\App\Http\Controllers\Admin\AdminController::class, 'cvs'])->name('cvs');
        Route::get('/cover-letters', [\App\Http\Controllers\Admin\AdminController::class, 'coverLetters'])->name('cover-letters');
        Route::get('/applications', [\App\Http\Controllers\Admin\AdminController::class, 'applications'])->name('applications');
        Route::get('/chat-sessions', [\App\Http\Controllers\Admin\AdminController::class, 'chatSessions'])->name('chat-sessions');
        Route::get('/templates', [\App\Http\Controllers\Admin\AdminController::class, 'templates'])->name('templates');
        Route::get('/analytics', [\App\Http\Controllers\Admin\AdminController::class, 'analytics'])->name('analytics');
        Route::get('/settings', [\App\Http\Controllers\Admin\AdminController::class, 'settings'])->name('settings');
    });

    // AI Chat History
    Route::get('chat/sessions', [\App\Http\Controllers\AiChatController::class, 'index'])->name('chat.sessions.index');
    Route::get('chat/sessions/{session}', [\App\Http\Controllers\AiChatController::class, 'show'])->name('chat.sessions.show');
    Route::post('chat/messages', [\App\Http\Controllers\AiChatController::class, 'store'])->name('chat.messages.store');
    Route::delete('chat/sessions/{session}', [\App\Http\Controllers\AiChatController::class, 'destroy'])->name('chat.sessions.destroy');

    // CV Distribution/Sharing
    Route::get('cvs/{cv}/shares', [CvShareController::class, 'getShares'])->name('cvs.shares.index');
    Route::post('cvs/{cv}/shares', [CvShareController::class, 'storeShare'])->name('cvs.shares.store');
    Route::delete('cvs/{cv}/shares/{share}', [CvShareController::class, 'destroyShare'])->name('cvs.shares.destroy');
});

// Public Share Routes
Route::get('s/{token}', [CvShareController::class, 'show'])->name('cvs.shared.show');
Route::post('s/{token}/update', [CvShareController::class, 'update'])->name('cvs.shared.update');
Route::post('s/{token}/comment', [CvShareController::class, 'comment'])->name('cvs.shared.comment');

require __DIR__.'/settings.php';
