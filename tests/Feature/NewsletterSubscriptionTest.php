<?php

use App\Mail\NewsletterWelcome;
use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\Mail;

test('user can subscribe to newsletter', function () {
    Mail::fake();

    $response = $this->postJson(route('newsletter.subscribe'), [
        'email' => 'subscriber@example.com',
        'name' => 'John Doe',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
        ]);

    $this->assertDatabaseHas('newsletter_subscribers', [
        'email' => 'subscriber@example.com',
        'name' => 'John Doe',
        'status' => 'active',
        'source' => 'popup',
    ]);

    Mail::assertQueued(NewsletterWelcome::class);
});

test('subscribing with existing active email returns already subscribed', function () {
    Mail::fake();

    NewsletterSubscriber::factory()->create(['email' => 'existing@example.com']);

    $response = $this->postJson(route('newsletter.subscribe'), [
        'email' => 'existing@example.com',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'already_subscribed' => true,
        ]);

    Mail::assertNothingQueued();
});

test('unsubscribed user can re-subscribe via subscribe endpoint', function () {
    Mail::fake();

    $subscriber = NewsletterSubscriber::factory()->unsubscribed()->create([
        'email' => 'comeback@example.com',
    ]);

    $response = $this->postJson(route('newsletter.subscribe'), [
        'email' => 'comeback@example.com',
        'name' => 'Comeback User',
    ]);

    $response->assertOk()
        ->assertJson(['success' => true]);

    $subscriber->refresh();
    expect($subscriber->status)->toBe('active')
        ->and($subscriber->name)->toBe('Comeback User')
        ->and($subscriber->unsubscribed_at)->toBeNull();

    Mail::assertQueued(NewsletterWelcome::class);
});

test('subscribe requires valid email', function () {
    $response = $this->postJson(route('newsletter.subscribe'), [
        'email' => 'not-an-email',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

test('user can unsubscribe via token', function () {
    $subscriber = NewsletterSubscriber::factory()->create();

    $response = $this->get(route('newsletter.unsubscribe', $subscriber->token));

    $response->assertOk();

    $subscriber->refresh();
    expect($subscriber->status)->toBe('unsubscribed')
        ->and($subscriber->unsubscribed_at)->not->toBeNull();
});

test('unsubscribe with invalid token returns 404', function () {
    $response = $this->get(route('newsletter.unsubscribe', 'invalid-token'));

    $response->assertNotFound();
});

test('user can resubscribe', function () {
    $subscriber = NewsletterSubscriber::factory()->unsubscribed()->create();

    $response = $this->postJson(route('newsletter.resubscribe'), [
        'email' => $subscriber->email,
    ]);

    $response->assertOk()
        ->assertJson(['success' => true]);

    $subscriber->refresh();
    expect($subscriber->status)->toBe('active')
        ->and($subscriber->unsubscribed_at)->toBeNull();
});

test('resubscribe with non-existent email fails validation', function () {
    $response = $this->postJson(route('newsletter.resubscribe'), [
        'email' => 'nonexistent@example.com',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

test('check subscription returns true for active subscriber', function () {
    NewsletterSubscriber::factory()->create(['email' => 'active@example.com']);

    $response = $this->postJson(route('newsletter.check'), [
        'email' => 'active@example.com',
    ]);

    $response->assertOk()
        ->assertJson(['subscribed' => true]);
});

test('check subscription returns false for unsubscribed user', function () {
    NewsletterSubscriber::factory()->unsubscribed()->create(['email' => 'inactive@example.com']);

    $response = $this->postJson(route('newsletter.check'), [
        'email' => 'inactive@example.com',
    ]);

    $response->assertOk()
        ->assertJson(['subscribed' => false]);
});

test('check subscription returns false for non-existent email', function () {
    $response = $this->postJson(route('newsletter.check'), [
        'email' => 'nobody@example.com',
    ]);

    $response->assertOk()
        ->assertJson(['subscribed' => false]);
});
