<?php

use Illuminate\Support\Facades\Storage;

it('public logo and favicon exist', function () {
    expect(file_exists(public_path('logo.svg')))->toBeTrue();
    expect(file_exists(public_path('favicon.svg')))->toBeTrue();
});
