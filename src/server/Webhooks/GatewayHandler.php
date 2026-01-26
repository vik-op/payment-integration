<?php
declare(strict_types=1);

/**
 * GatewayHandler
 * * Listener for asynchronous event notifications from the payment gateway.
 * Used for out-of-band updates such as chargebacks, captures, or delayed successes.
 */

$secret = getenv('WEBHOOK_SIGNING_SECRET');
$payload = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_X_GATEWAY_SIGNATURE'] ?? '';

try {
    /**
     * CRYPTOGRAPHIC VERIFICATION
     * Ensure the request originated from the trusted payment provider.
     * Prevents malicious "Success" injection attacks.
     */
    $event = \YourGateway\Webhook::constructEvent($payload, $sigHeader, $secret);

    // Process event based on lifecycle type
    switch ($event->type) {
        case 'payment_intent.succeeded':
            // Finalize the order in the database
            updateOrderStatus($event->data->object->metadata->order_id, 'COMPLETED');
            break;
            
        case 'payment_intent.payment_failed':
            // Log failure and notify the customer via email service
            handleFailure($event->data->object);
            break;
            
        default:
            // Log unhandled events for future audit trails
            error_log("Received unhandled webhook event type: " . $event->type);
    }

    // Always respond with 200 OK to acknowledge receipt to the gateway
    http_response_code(200);

} catch (\Exception $e) {
    /**
     * Security monitoring: Webhook failures should be investigated 
     * as they may indicate expired secrets or spoofing attempts.
     */
    error_log("Webhook Signature Validation Failed: " . $e->getMessage());
    http_response_code(401); // Unauthorized
}
