<?php

use App\Mail\NewsletterBroadcast;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

test('non-admin cannot access newsletter index', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.newsletter.index'))
        ->assertForbidden();
});

test('admin can view newsletter subscribers', function () {
    $admin = User::factory()->admin()->create();
    NewsletterSubscriber::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.newsletter.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/Newsletter/Index')
            ->has('subscribers.data', 3)
            ->has('stats')
            ->where('stats.total', 3)
            ->where('stats.active', 3)
        );
});

test('admin can filter subscribers by status', function () {
    $admin = User::factory()->admin()->create();
    NewsletterSubscriber::factory()->count(2)->create();
    NewsletterSubscriber::factory()->unsubscribed()->create();

    $this->actingAs($admin)
        ->get(route('admin.newsletter.index', ['status' => 'active']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('subscribers.data', 2)
        );
});

test('admin can search subscribers', function () {
    $admin = User::factory()->admin()->create();
    NewsletterSubscriber::factory()->create(['email' => 'findme@example.com']);
    NewsletterSubscriber::factory()->create(['email' => 'other@example.com']);

    $this->actingAs($admin)
        ->get(route('admin.newsletter.index', ['search' => 'findme']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('subscribers.data', 1)
        );
});

test('admin can delete a subscriber', function () {
    $admin = User::factory()->admin()->create();
    $subscriber = NewsletterSubscriber::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.newsletter.destroy', $subscriber))
        ->assertRedirect();

    $this->assertDatabaseMissing('newsletter_subscribers', ['id' => $subscriber->id]);
});

test('admin can view compose page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.newsletter.compose'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/Newsletter/Compose')
            ->has('stats.active_subscribers')
            ->has('stats.total_users')
        );
});

test('admin can send newsletter to subscribers', function () {
    Mail::fake();

    $admin = User::factory()->admin()->create();
    NewsletterSubscriber::factory()->count(3)->create();

    $this->actingAs($admin)
        ->post(route('admin.newsletter.send'), [
            'subject' => 'Test Newsletter',
            'body' => 'Hello subscribers!',
            'audience' => 'subscribers',
        ])
        ->assertRedirect();

    Mail::assertQueued(NewsletterBroadcast::class, 3);
});

test('admin can send newsletter to registered users', function () {
    Mail::fake();

    $admin = User::factory()->admin()->create();
    User::factory()->count(2)->create();

    $this->actingAs($admin)
        ->post(route('admin.newsletter.send'), [
            'subject' => 'Test Newsletter',
            'body' => 'Hello users!',
            'audience' => 'users',
        ])
        ->assertRedirect();

    // 2 created users + 1 admin = 3 users total
    Mail::assertQueued(NewsletterBroadcast::class, 3);
});

test('admin can send newsletter to all', function () {
    Mail::fake();

    $admin = User::factory()->admin()->create();
    User::factory()->count(2)->create();
    NewsletterSubscriber::factory()->count(2)->create();

    $this->actingAs($admin)
        ->post(route('admin.newsletter.send'), [
            'subject' => 'Test Newsletter',
            'body' => 'Hello everyone!',
            'audience' => 'all',
        ])
        ->assertRedirect();

    // 2 subscribers + 3 users = 5 total
    Mail::assertQueued(NewsletterBroadcast::class, 5);
});

test('send newsletter validates required fields', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.newsletter.send'), [])
        ->assertSessionHasErrors(['subject', 'body', 'audience']);
});

test('send newsletter validates audience value', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.newsletter.send'), [
            'subject' => 'Test',
            'body' => 'Body',
            'audience' => 'invalid',
        ])
        ->assertSessionHasErrors('audience');
});

test('admin can export subscribers as csv', function () {
    $admin = User::factory()->admin()->create();
    NewsletterSubscriber::factory()->count(3)->create();

    $response = $this->actingAs($admin)
        ->get(route('admin.newsletter.export'));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
});
