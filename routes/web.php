<?php

use App\Http\Controllers\Admin\TemplateBuilderController;
use App\Http\Controllers\AiCvGeneratorController;
use App\Http\Controllers\CoverLetterController;
use App\Http\Controllers\CvController;
use App\Http\Controllers\CvShareController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\TemplateController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'welcome'])->name('home');

Route::get('/about', [LandingController::class, 'about'])->name('about');

Route::get('/services', [LandingController::class, 'services'])->name('services');

Route::get('/pricing', [LandingController::class, 'pricing'])->name('pricing');

Route::get('/payment/checkout/{plan}', [\App\Http\Controllers\PaymentController::class, 'checkout'])
    ->name('payment.checkout')
    ->middleware('auth');

Route::post('/payment/process/{plan}', [\App\Http\Controllers\PaymentController::class, 'process'])
    ->name('payment.process')
    ->middleware('auth');

Route::get('/contact', [LandingController::class, 'contact'])->name('contact');

// Public Template Routes
Route::prefix('templates')->name('templates.')->group(function () {
    Route::get('/', [TemplateController::class, 'index'])->name('index');
    Route::get('/{template}', [TemplateController::class, 'show'])->name('show');
    Route::get('/{template}/editor', [TemplateController::class, 'editor'])->name('editor');
    Route::get('/{template}/preview', [TemplateController::class, 'preview'])->name('preview');
    Route::post('/{template}/preview', [TemplateController::class, 'previewWithData'])->name('preview.with-data');
    Route::get('/{template}/download', [TemplateController::class, 'download'])->name('download');
    Route::post('/{template}/save-as-cv', [TemplateController::class, 'saveAsCv'])
        ->middleware(['auth', 'verified'])
        ->name('save-as-cv');
});

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
    // AI Chat Page
    \Inertia\Inertia::macro('aiChat', function () {
        return \Inertia\Inertia::render('ai-chat');
    });
    Route::get('/ai-chat', fn () => \Inertia\Inertia::render('ai-chat'))->name('ai-chat');
    Route::post('/ai-chat', [\App\Http\Controllers\AiChatController::class, 'chat'])->name('ai-chat.send');
    Route::get('/ai-chat/history', [\App\Http\Controllers\AiChatController::class, 'history'])->name('ai-chat.history');

    // AI CV Generator
    Route::prefix('ai-cv-generator')->name('ai-cv-generator.')->group(function () {
        Route::get('/', [AiCvGeneratorController::class, 'index'])->name('index');
        Route::post('/generate', [AiCvGeneratorController::class, 'generate'])->name('generate');
        Route::post('/refine', [AiCvGeneratorController::class, 'refine'])->name('refine');
        Route::post('/save', [AiCvGeneratorController::class, 'save'])->name('save');
    });

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

    // User Subscription Management
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/subscription', [\App\Http\Controllers\SubscriptionController::class, 'index'])->name('subscription');
    });

    // Admin routes
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('dashboard');

        // User Management
        Route::get('/users', [\App\Http\Controllers\Admin\AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users', [\App\Http\Controllers\Admin\AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users/export', [\App\Http\Controllers\Admin\AdminUserController::class, 'export'])->name('users.export');
        Route::get('/users/{user}', [\App\Http\Controllers\Admin\AdminUserController::class, 'show'])->name('users.show');
        Route::put('/users/{user}', [\App\Http\Controllers\Admin\AdminUserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [\App\Http\Controllers\Admin\AdminUserController::class, 'destroy'])->name('users.destroy');
        Route::patch('/users/{user}/toggle-role', [\App\Http\Controllers\Admin\AdminUserController::class, 'toggleRole'])->name('users.toggle-role');
        Route::post('/users/{user}/resend-verification', [\App\Http\Controllers\Admin\AdminUserController::class, 'resendVerification'])->name('users.resend-verification');
        Route::delete('/users/{user}/clear-activity', [\App\Http\Controllers\Admin\AdminUserController::class, 'clearActivity'])->name('users.clear-activity');

        // CV Management
        Route::get('/cvs', [\App\Http\Controllers\Admin\AdminCvController::class, 'index'])->name('cvs.index');
        Route::get('/cvs/create', [\App\Http\Controllers\Admin\AdminCvController::class, 'create'])->name('cvs.create');
        Route::post('/cvs', [\App\Http\Controllers\Admin\AdminCvController::class, 'store'])->name('cvs.store');
        Route::post('/cvs/generate', [\App\Http\Controllers\Admin\AdminCvController::class, 'generate'])->name('cvs.generate');
        Route::get('/cvs/{cv}', [\App\Http\Controllers\Admin\AdminCvController::class, 'show'])->name('cvs.show');
        Route::get('/cvs/{cv}/edit', [\App\Http\Controllers\Admin\AdminCvController::class, 'edit'])->name('cvs.edit');
        Route::put('/cvs/{cv}', [\App\Http\Controllers\Admin\AdminCvController::class, 'update'])->name('cvs.update');
        Route::delete('/cvs/{cv}', [\App\Http\Controllers\Admin\AdminCvController::class, 'destroy'])->name('cvs.destroy');
        Route::patch('/cvs/{cv}/toggle-primary', [\App\Http\Controllers\Admin\AdminCvController::class, 'togglePrimary'])->name('cvs.toggle-primary');
        Route::post('/cvs/{cv}/duplicate', [\App\Http\Controllers\Admin\AdminCvController::class, 'duplicate'])->name('cvs.duplicate');
        Route::get('/cvs/{cv}/download-pdf', [\App\Http\Controllers\Admin\AdminCvController::class, 'downloadPdf'])->name('cvs.download-pdf');
        Route::post('/cvs/{cv}/suggestions', [\App\Http\Controllers\Admin\AdminCvController::class, 'generateSuggestions'])->name('cvs.suggestions');
        Route::get('/users/{user}/job-applications', [\App\Http\Controllers\Admin\AdminCvController::class, 'getUserJobApplications'])->name('users.job-applications');

        // Cover Letter Management
        Route::get('/cover-letters', [\App\Http\Controllers\Admin\AdminCoverLetterController::class, 'index'])->name('cover-letters.index');
        Route::get('/cover-letters/create', [\App\Http\Controllers\Admin\AdminCoverLetterController::class, 'create'])->name('cover-letters.create');
        Route::post('/cover-letters', [\App\Http\Controllers\Admin\AdminCoverLetterController::class, 'store'])->name('cover-letters.store');
        Route::post('/cover-letters/generate', [\App\Http\Controllers\Admin\AdminCoverLetterController::class, 'generate'])->name('cover-letters.generate');
        Route::get('/cover-letters/{coverLetter}', [\App\Http\Controllers\Admin\AdminCoverLetterController::class, 'show'])->name('cover-letters.show');
        Route::get('/cover-letters/{coverLetter}/edit', [\App\Http\Controllers\Admin\AdminCoverLetterController::class, 'edit'])->name('cover-letters.edit');
        Route::put('/cover-letters/{coverLetter}', [\App\Http\Controllers\Admin\AdminCoverLetterController::class, 'update'])->name('cover-letters.update');
        Route::delete('/cover-letters/{coverLetter}', [\App\Http\Controllers\Admin\AdminCoverLetterController::class, 'destroy'])->name('cover-letters.destroy');
        Route::post('/cover-letters/{coverLetter}/duplicate', [\App\Http\Controllers\Admin\AdminCoverLetterController::class, 'duplicate'])->name('cover-letters.duplicate');
        Route::post('/cover-letters/{coverLetter}/improve', [\App\Http\Controllers\Admin\AdminCoverLetterController::class, 'improve'])->name('cover-letters.improve');
        Route::get('/users/{user}/resources', [\App\Http\Controllers\Admin\AdminCoverLetterController::class, 'getUserResources'])->name('users.resources');

        Route::get('/applications', [\App\Http\Controllers\Admin\AdminController::class, 'applications'])->name('applications');
        Route::get('/chat-sessions', [\App\Http\Controllers\Admin\AdminController::class, 'chatSessions'])->name('chat-sessions');
        Route::get('/templates', [\App\Http\Controllers\Admin\AdminController::class, 'templates'])->name('templates');
        Route::get('/analytics', [\App\Http\Controllers\Admin\AdminController::class, 'analytics'])->name('analytics');
        Route::get('/settings', [\App\Http\Controllers\Admin\AdminController::class, 'settings'])->name('settings');
        Route::resource('pricing-plans', \App\Http\Controllers\Admin\PricingPlanController::class);

        // Template Builder Routes
        Route::prefix('template-builder')->name('template-builder.')->group(function () {
            Route::get('/', [TemplateBuilderController::class, 'index'])->name('index');
            Route::get('/create', [TemplateBuilderController::class, 'create'])->name('create');
            Route::post('/', [TemplateBuilderController::class, 'store'])->name('store');
            Route::get('/{template}/edit', [TemplateBuilderController::class, 'edit'])->name('edit');
            Route::put('/{template}', [TemplateBuilderController::class, 'update'])->name('update');
            Route::delete('/{template}', [TemplateBuilderController::class, 'destroy'])->name('destroy');
            Route::patch('/{template}/toggle-status', [TemplateBuilderController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{template}/duplicate', [TemplateBuilderController::class, 'duplicate'])->name('duplicate');
            Route::get('/{template}/preview', [TemplateBuilderController::class, 'preview'])->name('preview');
        });

        // Testimonials & Site Settings Routes
        Route::prefix('testimonials')->name('testimonials.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\TestimonialController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Admin\TestimonialController::class, 'store'])->name('store');
            Route::put('/{testimonial}', [\App\Http\Controllers\Admin\TestimonialController::class, 'update'])->name('update');
            Route::delete('/{testimonial}', [\App\Http\Controllers\Admin\TestimonialController::class, 'destroy'])->name('destroy');
            Route::patch('/{testimonial}/toggle-status', [\App\Http\Controllers\Admin\TestimonialController::class, 'toggleStatus'])->name('toggle-status');
            Route::patch('/{testimonial}/toggle-featured', [\App\Http\Controllers\Admin\TestimonialController::class, 'toggleFeatured'])->name('toggle-featured');
        });
        Route::put('/site-stats', [\App\Http\Controllers\Admin\TestimonialController::class, 'updateStats'])->name('site-stats.update');

        // Site Settings Routes
        Route::prefix('site-settings')->name('site-settings.')->group(function () {
            Route::put('/general', [\App\Http\Controllers\Admin\SiteSettingController::class, 'updateGeneral'])->name('general');
            Route::put('/contact', [\App\Http\Controllers\Admin\SiteSettingController::class, 'updateContact'])->name('contact');
            Route::put('/social', [\App\Http\Controllers\Admin\SiteSettingController::class, 'updateSocial'])->name('social');
            Route::put('/email', [\App\Http\Controllers\Admin\SiteSettingController::class, 'updateEmail'])->name('email');
            Route::put('/stats', [\App\Http\Controllers\Admin\SiteSettingController::class, 'updateStats'])->name('stats');
        });
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
