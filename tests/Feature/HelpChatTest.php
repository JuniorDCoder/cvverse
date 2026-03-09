<?php

use App\Models\HelpConversation;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);
    $this->admin = User::factory()->admin()->create([
        'onboarding_completed_at' => now(),
    ]);
});

// --- User Help Center Tests ---

it('shows help center page and auto-creates conversation', function () {
    actingAs($this->user)
        ->get('/help-center')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('HelpCenter')
            ->has('conversation')
            ->has('messages')
        );

    $this->assertDatabaseHas('help_conversations', [
        'user_id' => $this->user->id,
        'status' => 'open',
    ]);
});

it('reuses existing open conversation', function () {
    $conversation = HelpConversation::create([
        'user_id' => $this->user->id,
        'subject' => 'Test',
        'status' => 'open',
    ]);

    actingAs($this->user)
        ->get('/help-center')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('conversation.id', $conversation->id)
        );

    expect(HelpConversation::where('user_id', $this->user->id)->count())->toBe(1);
});

it('allows user to send a message', function () {
    $conversation = HelpConversation::create([
        'user_id' => $this->user->id,
        'subject' => 'Test',
        'status' => 'open',
    ]);

    actingAs($this->user)
        ->postJson('/help-center/send', [
            'conversation_id' => $conversation->id,
            'message' => 'Hello, I need help!',
        ])
        ->assertOk()
        ->assertJson(['success' => true]);

    $this->assertDatabaseHas('help_messages', [
        'help_conversation_id' => $conversation->id,
        'sender_type' => 'user',
        'content' => 'Hello, I need help!',
    ]);
});

it('prevents sending message to closed conversation', function () {
    $conversation = HelpConversation::create([
        'user_id' => $this->user->id,
        'subject' => 'Test',
        'status' => 'closed',
    ]);

    actingAs($this->user)
        ->postJson('/help-center/send', [
            'conversation_id' => $conversation->id,
            'message' => 'Hello',
        ])
        ->assertStatus(422);
});

it('prevents sending message to another user conversation', function () {
    $otherUser = User::factory()->create(['onboarding_completed_at' => now()]);
    $conversation = HelpConversation::create([
        'user_id' => $otherUser->id,
        'subject' => 'Test',
        'status' => 'open',
    ]);

    actingAs($this->user)
        ->postJson('/help-center/send', [
            'conversation_id' => $conversation->id,
            'message' => 'Hello',
        ])
        ->assertStatus(404);
});

it('allows user to poll for new messages', function () {
    $conversation = HelpConversation::create([
        'user_id' => $this->user->id,
        'subject' => 'Test',
        'status' => 'open',
    ]);

    $conversation->messages()->create([
        'sender_type' => 'admin',
        'sender_id' => $this->admin->id,
        'content' => 'Hello, how can I help?',
    ]);

    actingAs($this->user)
        ->getJson('/help-center/poll?conversation_id='.$conversation->id.'&after_id=0')
        ->assertOk()
        ->assertJsonCount(1, 'messages');
});

it('allows user to close conversation', function () {
    $conversation = HelpConversation::create([
        'user_id' => $this->user->id,
        'subject' => 'Test',
        'status' => 'open',
    ]);

    actingAs($this->user)
        ->postJson('/help-center/close', [
            'conversation_id' => $conversation->id,
        ])
        ->assertOk();

    expect($conversation->fresh()->status)->toBe('closed');
});

// --- Admin Support Chat Tests ---

it('allows admin to view support chat index', function () {
    HelpConversation::create([
        'user_id' => $this->user->id,
        'subject' => 'Need help',
        'status' => 'open',
    ]);

    actingAs($this->admin)
        ->get('/admin/support-chat')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/SupportChat/Index')
            ->has('conversations.data', 1)
            ->has('aiTimeout')
        );
});

it('allows admin to view a conversation', function () {
    $conversation = HelpConversation::create([
        'user_id' => $this->user->id,
        'subject' => 'Need help',
        'status' => 'open',
    ]);

    $conversation->messages()->create([
        'sender_type' => 'user',
        'sender_id' => $this->user->id,
        'content' => 'Help needed',
    ]);

    actingAs($this->admin)
        ->get('/admin/support-chat/'.$conversation->id)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/SupportChat/Show')
            ->has('messages', 1)
            ->where('conversation.id', $conversation->id)
        );
});

it('allows admin to reply and suspends AI', function () {
    $conversation = HelpConversation::create([
        'user_id' => $this->user->id,
        'subject' => 'Test',
        'status' => 'ai_active',
    ]);

    actingAs($this->admin)
        ->postJson('/admin/support-chat/'.$conversation->id.'/reply', [
            'message' => 'I am here to help!',
        ])
        ->assertOk();

    $conversation->refresh();
    expect($conversation->status)->toBe('open');
    expect($conversation->admin_id)->toBe($this->admin->id);
    expect($conversation->last_admin_activity_at)->not->toBeNull();

    $this->assertDatabaseHas('help_messages', [
        'help_conversation_id' => $conversation->id,
        'sender_type' => 'admin',
        'content' => 'I am here to help!',
    ]);
});

it('allows admin to close conversation', function () {
    $conversation = HelpConversation::create([
        'user_id' => $this->user->id,
        'subject' => 'Test',
        'status' => 'open',
    ]);

    actingAs($this->admin)
        ->postJson('/admin/support-chat/'.$conversation->id.'/close')
        ->assertOk();

    expect($conversation->fresh()->status)->toBe('closed');

    $this->assertDatabaseHas('help_messages', [
        'help_conversation_id' => $conversation->id,
        'sender_type' => 'admin',
        'content' => 'This conversation has been closed by support. Feel free to start a new one anytime!',
    ]);
});

it('allows admin to update AI timeout settings', function () {
    actingAs($this->admin)
        ->postJson('/admin/support-chat/settings', [
            'ai_timeout' => 10,
        ])
        ->assertOk();

    expect(\App\Models\SiteSetting::getValue('help_chat_ai_timeout'))->toBe(10);
});

it('prevents non-admin from accessing support chat', function () {
    actingAs($this->user)
        ->get('/admin/support-chat')
        ->assertForbidden();
});

it('returns unread count for admin', function () {
    $conversation = HelpConversation::create([
        'user_id' => $this->user->id,
        'subject' => 'Test',
        'status' => 'open',
    ]);
    $conversation->messages()->create([
        'sender_type' => 'user',
        'sender_id' => $this->user->id,
        'content' => 'Hello',
    ]);

    actingAs($this->admin)
        ->getJson('/admin/support-chat/unread-count')
        ->assertOk()
        ->assertJson(['count' => 1]);
});

// --- Reopen Tests ---

it('allows user to reopen a closed conversation', function () {
    $conversation = HelpConversation::create([
        'user_id' => $this->user->id,
        'subject' => 'Test',
        'status' => 'closed',
    ]);

    actingAs($this->user)
        ->postJson('/help-center/reopen', [
            'conversation_id' => $conversation->id,
        ])
        ->assertOk();

    expect($conversation->fresh()->status)->toBe('open');

    $this->assertDatabaseHas('help_messages', [
        'help_conversation_id' => $conversation->id,
        'content' => 'This conversation has been reopened. How can we help you further?',
    ]);
});

it('prevents user from reopening non-closed conversation', function () {
    $conversation = HelpConversation::create([
        'user_id' => $this->user->id,
        'subject' => 'Test',
        'status' => 'open',
    ]);

    actingAs($this->user)
        ->postJson('/help-center/reopen', [
            'conversation_id' => $conversation->id,
        ])
        ->assertStatus(422);
});

it('allows admin to reopen a closed conversation', function () {
    $conversation = HelpConversation::create([
        'user_id' => $this->user->id,
        'subject' => 'Test',
        'status' => 'closed',
    ]);

    actingAs($this->admin)
        ->postJson('/admin/support-chat/'.$conversation->id.'/reopen')
        ->assertOk();

    $conversation->refresh();
    expect($conversation->status)->toBe('open');
    expect($conversation->admin_id)->toBe($this->admin->id);

    $this->assertDatabaseHas('help_messages', [
        'help_conversation_id' => $conversation->id,
        'content' => 'This conversation has been reopened by support.',
    ]);
});

// --- Typing Indicator Tests ---

it('allows user to send typing indicator', function () {
    $conversation = HelpConversation::create([
        'user_id' => $this->user->id,
        'subject' => 'Test',
        'status' => 'open',
    ]);

    actingAs($this->user)
        ->postJson('/help-center/typing', [
            'conversation_id' => $conversation->id,
            'typing' => true,
        ])
        ->assertOk();

    expect($conversation->fresh()->user_typing_at)->not->toBeNull();
});

it('allows admin to send typing indicator', function () {
    $conversation = HelpConversation::create([
        'user_id' => $this->user->id,
        'subject' => 'Test',
        'status' => 'open',
    ]);

    actingAs($this->admin)
        ->postJson('/admin/support-chat/'.$conversation->id.'/typing', [
            'typing' => true,
        ])
        ->assertOk();

    expect($conversation->fresh()->admin_typing_at)->not->toBeNull();
});

// --- Conversation History Tests ---

it('returns conversations list in help center', function () {
    HelpConversation::create([
        'user_id' => $this->user->id,
        'subject' => 'First',
        'status' => 'closed',
    ]);
    HelpConversation::create([
        'user_id' => $this->user->id,
        'subject' => 'Second',
        'status' => 'open',
    ]);

    actingAs($this->user)
        ->get('/help-center')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('HelpCenter')
            ->has('conversations', 2)
        );
});

it('allows user to view a specific conversation', function () {
    $conversation = HelpConversation::create([
        'user_id' => $this->user->id,
        'subject' => 'Past convo',
        'status' => 'closed',
    ]);

    actingAs($this->user)
        ->get('/help-center/'.$conversation->id)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('conversation.id', $conversation->id)
        );
});

// --- Read Receipt Tests ---

it('returns read updates in user poll', function () {
    $conversation = HelpConversation::create([
        'user_id' => $this->user->id,
        'subject' => 'Test',
        'status' => 'open',
    ]);

    $message = $conversation->messages()->create([
        'sender_type' => 'user',
        'sender_id' => $this->user->id,
        'content' => 'Hello',
        'read_at' => now(),
    ]);

    actingAs($this->user)
        ->getJson('/help-center/poll?conversation_id='.$conversation->id.'&after_id=0')
        ->assertOk()
        ->assertJsonStructure(['read_updates']);
});

it('returns read updates in admin poll', function () {
    $conversation = HelpConversation::create([
        'user_id' => $this->user->id,
        'subject' => 'Test',
        'status' => 'open',
    ]);

    $message = $conversation->messages()->create([
        'sender_type' => 'admin',
        'sender_id' => $this->admin->id,
        'content' => 'Hello from admin',
        'read_at' => now(),
    ]);

    actingAs($this->admin)
        ->getJson('/admin/support-chat/'.$conversation->id.'/poll?after_id=0')
        ->assertOk()
        ->assertJsonStructure(['read_updates']);
});
