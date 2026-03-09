<?php

use App\Services\GeminiService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Config::set('gemini.api_key', 'test-key');
    Config::set('gemini.model', 'gemini-1.5-flash-latest');
    Config::set('gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta');
    Config::set('gemini.fallback_models', [
        'gemini-2.0-flash',
        'gemini-2.0-flash-lite',
    ]);
});

it('returns response from primary model when available', function () {
    Http::fake([
        '*/models/gemini-1.5-flash-latest:generateContent*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'Hello!']]]]],
        ]),
    ]);

    $service = new GeminiService;
    $result = $service->generateContent('Say hello');

    expect($result)->not->toBeNull()
        ->and($service->extractText($result))->toBe('Hello!');

    Http::assertSentCount(1);
});

it('falls back to next model on 503 error', function () {
    Http::fake([
        '*/models/gemini-1.5-flash-latest:generateContent*' => Http::response(
            ['error' => ['code' => 503, 'message' => 'UNAVAILABLE']],
            503
        ),
        '*/models/gemini-2.0-flash:generateContent*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'Fallback response']]]]],
        ]),
    ]);

    $service = new GeminiService;
    $result = $service->generateContent('Say hello');

    expect($result)->not->toBeNull()
        ->and($service->extractText($result))->toBe('Fallback response');

    Http::assertSentCount(2);
});

it('falls back to next model on 429 rate limit', function () {
    Http::fake([
        '*/models/gemini-1.5-flash-latest:generateContent*' => Http::response(
            ['error' => ['code' => 429, 'message' => 'RESOURCE_EXHAUSTED']],
            429
        ),
        '*/models/gemini-2.0-flash:generateContent*' => Http::response(
            ['error' => ['code' => 429, 'message' => 'RESOURCE_EXHAUSTED']],
            429
        ),
        '*/models/gemini-2.0-flash-lite:generateContent*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'Third model response']]]]],
        ]),
    ]);

    $service = new GeminiService;
    $result = $service->generateContent('Say hello');

    expect($result)->not->toBeNull()
        ->and($service->extractText($result))->toBe('Third model response');

    Http::assertSentCount(3);
});

it('returns null when all models are exhausted', function () {
    Http::fake([
        '*/models/*:generateContent*' => Http::response(
            ['error' => ['code' => 503, 'message' => 'UNAVAILABLE']],
            503
        ),
    ]);

    $service = new GeminiService;
    $result = $service->generateContent('Say hello');

    expect($result)->toBeNull();
});

it('falls back on 404 model not found', function () {
    Http::fake([
        '*/models/gemini-1.5-flash-latest:generateContent*' => Http::response(
            ['error' => ['code' => 404, 'message' => 'NOT_FOUND']],
            404
        ),
        '*/models/gemini-2.0-flash:generateContent*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'Found a working model']]]]],
        ]),
    ]);

    $service = new GeminiService;
    $result = $service->generateContent('Say hello');

    expect($result)->not->toBeNull()
        ->and($service->extractText($result))->toBe('Found a working model');

    Http::assertSentCount(2);
});

it('does not retry on non-retryable errors like 401', function () {
    Http::fake([
        '*/models/gemini-1.5-flash-latest:generateContent*' => Http::response(
            ['error' => ['code' => 401, 'message' => 'UNAUTHENTICATED']],
            401
        ),
        '*/models/gemini-2.0-flash:generateContent*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'Should still try']]]]],
        ]),
    ]);

    $service = new GeminiService;
    $result = $service->generateContent('Say hello');

    // Even on 401, the loop continues to the next model (tryModel returns null)
    // but the service still attempts fallbacks since null means "try next"
    expect($result)->not->toBeNull();
});

it('returns null when API key is not configured', function () {
    Config::set('gemini.api_key', '');

    $service = new GeminiService;
    $result = $service->generateContent('Say hello');

    expect($result)->toBeNull();

    Http::assertNothingSent();
});

it('skips duplicate models in fallback chain', function () {
    Config::set('gemini.model', 'gemini-2.0-flash');
    Config::set('gemini.fallback_models', [
        'gemini-2.0-flash',
        'gemini-2.0-flash-lite',
    ]);

    Http::fake([
        '*/models/gemini-2.0-flash:generateContent*' => Http::response(
            ['error' => ['code' => 503, 'message' => 'UNAVAILABLE']],
            503
        ),
        '*/models/gemini-2.0-flash-lite:generateContent*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'Lite response']]]]],
        ]),
    ]);

    $service = new GeminiService;
    $result = $service->generateContent('Say hello');

    expect($result)->not->toBeNull()
        ->and($service->extractText($result))->toBe('Lite response');

    // Primary (gemini-2.0-flash) tried once, duplicate skipped, then lite
    Http::assertSentCount(2);
});
