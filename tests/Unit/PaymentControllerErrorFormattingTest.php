<?php

use App\Http\Controllers\PaymentController;

function formatPaymentErrorMessage(string $message, string $traceId): string
{
    $controller = new PaymentController;
    $reflection = new \ReflectionClass($controller);
    $method = $reflection->getMethod('formatUserError');
    $method->setAccessible(true);

    return $method->invoke($controller, $message, $traceId);
}

test('insufficient funds errors are user friendly', function () {
    $formatted = formatPaymentErrorMessage(
        'Initiator (sender) Account does not have sufficient funds with the Service Provider to perform this transaction',
        'a24d1c55-8db8-438d-8576-8d0d447e1e66'
    );

    expect($formatted)->toContain('insufficient funds')
        ->and($formatted)->toContain('Ref: a24d1c55-8db8-438d-8576-8d0d447e1e66')
        ->and($formatted)->not->toContain('Initiator (sender) Account');
});

test('network errors are simplified for users', function () {
    $formatted = formatPaymentErrorMessage(
        "Could not connect to MeSomb (https://mesomb.hachther.com/api/v1.1/payment/collect/). Please check your internet connection and try again. If this problem persists, you should check MeSomb's service, let us know at support@mesomb.atlassian.net. (Network error [errno 6]: Could not resolve host: mesomb.hachther.com)",
        '3dcc3adb-bec4-46ef-b690-9f376acd4244'
    );

    expect($formatted)->toContain('Unable to reach the payment provider right now.')
        ->and($formatted)->toContain('Ref: 3dcc3adb-bec4-46ef-b690-9f376acd4244')
        ->and($formatted)->not->toContain('support@mesomb.atlassian.net');
});

test('timeout validation errors instruct user to retry', function () {
    $formatted = formatPaymentErrorMessage(
        'The customer has taken too much time to validate the transaction.',
        '6599529e-e13c-40b2-80d7-c44616eb619c'
    );

    expect($formatted)->toContain('Payment request expired before confirmation.')
        ->and($formatted)->toContain('Ref: 6599529e-e13c-40b2-80d7-c44616eb619c')
        ->and($formatted)->not->toContain('taken too much time');
});
