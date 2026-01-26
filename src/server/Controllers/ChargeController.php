<?php

declare(strict_types=1);

/**
 * ChargeController
 * * Orchestrates the payment settlement process. 
 * Key Patterns: Idempotency, Request Validation, and Secure Gateway Communication.
 */

try {
    // 1. Inbound Security Check: Ensure the request is AJAX
    if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
        http_response_code(403);
        die(json_encode(['error' => 'Forbidden: Direct access not allowed']));
    }

    // 2. Data Parsing
    $input = json_decode(file_get_contents('php://input'), true);
    
    // 3. Request Validation
    if (!isset($input['token'], $input['orderId'])) {
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'error' => 'Required payment fields are missing.']);
        exit;
    }

    /**
     * IDEMPOTENCY GUARD
     * Ensures that network retries do not result in duplicate charges.
     */
    if (isOrderProcessed($input['orderId'])) {
        http_response_code(200);
        echo json_encode([
            'success' => true, 
            'message' => 'Status Sync: Order already settled.'
        ]);
        exit;
    }

    // 4. Gateway Integration
    // Securely pull API keys from the environment configuration
    $paymentGateway = new \YourGateway\SDK\Client(getenv('GATEWAY_SECRET_KEY'));
    
    /**
     * SETTLEMENT ATTEMPT
     * Submit the client-side token for final authorization.
     */
    $charge = $paymentGateway->charges->create([
        'amount'   => 10000, // Denominated in cents (e.g., $100.00)
        'currency' => 'usd',
        'source'   => $input['token'],
        'metadata' => [
            'order_id'   => $input['orderId'],
            'risk_score' => analyzeRisk($input)
        ]
    ]);

    // 5. Post-Payment Persistence
    if ($charge->status === 'succeeded') {
        saveTransaction($input['orderId'], $charge->id, 'SUCCESS');
        echo json_encode(['success' => true]);
    }

} catch (\YourGateway\Exception\ApiErrorException $e) {
    // 402 Payment Required: Card declined or expired
    http_response_code(402);
    error_log("[Payment Service] Gateway Rejection: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'The payment was declined by the issuer.']);
} catch (\Exception $e) {
    // 500 Internal Server Error: System or connectivity issues
    http_response_code(500);
    error_log("[Payment Service] Critical Failure: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An internal server error occurred.']);
}
