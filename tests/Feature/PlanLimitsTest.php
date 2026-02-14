<?php

use App\Models\User;

test('free users are redirected to pricing when cv limit is reached', function () {
    config([
        'plans.free.limits.cvs' => 1,
    ]);

    $user = User::factory()->create([
        'subscription_status' => 'free',
        'pricing_plan_id' => null,
        'subscription_ends_at' => null,
    ]);

    $user->cvs()->create([
        'name' => 'Existing CV',
        'template' => 'modern',
    ]);

    $response = $this->actingAs($user)->get(route('cvs.create'));

    $response->assertRedirect(route('pricing'));
    $response->assertSessionHas('error');
});

test('free users cannot generate cover letters with ai', function () {
    config([
        'plans.free.features.ai_cover_letter_generation' => false,
    ]);

    $user = User::factory()->create([
        'subscription_status' => 'free',
        'pricing_plan_id' => null,
        'subscription_ends_at' => null,
    ]);

    $cv = $user->cvs()->create([
        'name' => 'My CV',
        'template' => 'modern',
    ]);

    $job = $user->jobApplications()->create([
        'title' => 'Backend Developer',
        'status' => 'saved',
    ]);

    $response = $this->actingAs($user)->postJson(route('cover-letters.generate'), [
        'job_application_id' => $job->id,
        'cv_id' => $cv->id,
        'tone' => 'professional',
    ]);

    $response->assertForbidden();
    $response->assertJson([
        'success' => false,
    ]);
    $response->assertJsonPath('upgrade_url', route('pricing'));
});
