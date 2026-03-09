<?php

use App\Mail\ContactConfirmation;
use App\Mail\ContactNotification;
use App\Mail\ContactReply;
use App\Models\ContactMessage;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

// === Public Contact Form ===

it('can submit the contact form', function () {
    Mail::fake();

    $response = $this->post('/contact', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'company' => 'Acme Inc',
        'subject' => 'Testing contact',
        'message' => 'Hello, this is a test message.',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('contact_messages', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'subject' => 'Testing contact',
        'status' => 'new',
    ]);
});

it('sends confirmation email to the sender', function () {
    Mail::fake();

    $this->post('/contact', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'subject' => 'Help needed',
        'message' => 'I need help with my CV.',
    ]);

    Mail::assertQueued(ContactConfirmation::class, function ($mail) {
        return $mail->hasTo('jane@example.com');
    });
});

it('sends notification email to admin emails from settings', function () {
    Mail::fake();

    SiteSetting::setValue('support_email', 'support@cvverse.test', 'string', 'contact');
    SiteSetting::setValue('sales_email', 'sales@cvverse.test', 'string', 'contact');

    $this->post('/contact', [
        'name' => 'Test User',
        'email' => 'user@example.com',
        'subject' => 'Inquiry',
        'message' => 'This is an inquiry.',
    ]);

    Mail::assertQueued(ContactNotification::class, function ($mail) {
        return $mail->hasTo('support@cvverse.test');
    });
    Mail::assertQueued(ContactNotification::class, function ($mail) {
        return $mail->hasTo('sales@cvverse.test');
    });
});

it('validates required fields on contact form', function () {
    $response = $this->post('/contact', []);

    $response->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
});

it('validates email format', function () {
    $response = $this->post('/contact', [
        'name' => 'Test',
        'email' => 'not-an-email',
        'subject' => 'Test',
        'message' => 'Test message',
    ]);

    $response->assertSessionHasErrors(['email']);
});

it('validates message max length', function () {
    $response = $this->post('/contact', [
        'name' => 'Test',
        'email' => 'test@example.com',
        'subject' => 'Test',
        'message' => str_repeat('a', 5001),
    ]);

    $response->assertSessionHasErrors(['message']);
});

// === Admin Contact Messages ===

it('denies non-admin access to contact messages', function () {
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)
        ->get('/admin/contact-messages')
        ->assertForbidden();
});

it('allows admin to view contact messages', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    ContactMessage::factory()->count(3)->create();

    $response = $this->actingAs($admin)->get('/admin/contact-messages');

    $response->assertSuccessful();
});

it('allows admin to search contact messages', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    ContactMessage::factory()->create(['name' => 'Unique Test Name']);
    ContactMessage::factory()->create(['name' => 'Another Person']);

    $response = $this->actingAs($admin)->get('/admin/contact-messages?search=Unique');

    $response->assertSuccessful();
});

it('allows admin to filter by status', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    ContactMessage::factory()->create(['status' => 'new']);
    ContactMessage::factory()->read()->create();

    $response = $this->actingAs($admin)->get('/admin/contact-messages?status=read');

    $response->assertSuccessful();
});

it('allows admin to mark a message as read', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $message = ContactMessage::factory()->create(['status' => 'new']);

    $this->actingAs($admin)
        ->patch("/admin/contact-messages/{$message->id}/read")
        ->assertRedirect();

    expect($message->fresh()->status)->toBe('read');
});

it('allows admin to reply to a contact message', function () {
    Mail::fake();

    $admin = User::factory()->create(['role' => 'admin']);
    $message = ContactMessage::factory()->create();

    $response = $this->actingAs($admin)
        ->post("/admin/contact-messages/{$message->id}/reply", [
            'reply' => 'Thank you for your message. We will look into this.',
        ]);

    $response->assertRedirect();

    $message->refresh();
    expect($message->status)->toBe('replied');
    expect($message->admin_reply)->toBe('Thank you for your message. We will look into this.');
    expect($message->replied_by)->toBe($admin->id);
    expect($message->replied_at)->not()->toBeNull();

    Mail::assertQueued(ContactReply::class, function ($mail) use ($message) {
        return $mail->hasTo($message->email);
    });
});

it('validates reply is required', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $message = ContactMessage::factory()->create();

    $response = $this->actingAs($admin)
        ->post("/admin/contact-messages/{$message->id}/reply", [
            'reply' => '',
        ]);

    $response->assertSessionHasErrors(['reply']);
});

it('allows admin to delete a contact message', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $message = ContactMessage::factory()->create();

    $this->actingAs($admin)
        ->delete("/admin/contact-messages/{$message->id}")
        ->assertRedirect();

    $this->assertDatabaseMissing('contact_messages', ['id' => $message->id]);
});
