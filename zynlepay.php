<?php
// config/zynlepay.php

// ZynlePay Sandbox Configuration
define('ZYNLE_SANDBOX_MODE', true);

// Your existing credentials
const ZYNLE_API_ID      = '15fff16e-8a52-4905-b798-90f573e345cd'; //api id
const ZYNLE_MERCHANT_ID = 'MEC01012'; //merchant id
const ZYNLE_SECRET      = 'f4917aa7-8508-4e46-bc57-d703fad5ca78'; //secret for key generation

// Some samples show an Authorization header with base64(email:password):
const ZYNLE_AUTH_EMAIL    = 'sandbox@example.com';
const ZYNLE_AUTH_PASSWORD = 'password123';

// Use TEST base URL while you develop (from the PDF)
const ZYNLE_BASE_URL_SANDBOX = 'http://api.zynlemoney.com:8070/zynlepaytest/zpay/api';
const ZYNLE_BASE_URL_LIVE = 'https://api.zynlemoney.com:8060/zynlepay/zpay/api';

// Use sandbox URL in sandbox mode
define('ZYNLE_BASE_URL', ZYNLE_SANDBOX_MODE ? ZYNLE_BASE_URL_SANDBOX : ZYNLE_BASE_URL_LIVE);

// Test card details for sandbox simulation
define('ZYNLE_TEST_CARD_VISA', '4111111111111111');
define('ZYNLE_TEST_CARD_MASTERCARD', '5555555555554444');
define('ZYNLE_TEST_CARD_DISCOVER', '6011111111111117');
define('ZYNLE_TEST_CARD_AMEX', '378282246310005');

// Helper to build the `key` using secret + request_id (per doc)
function zynle_compute_key(string $request_id): string {
    return base64_encode(sha1(ZYNLE_SECRET . $request_id));
}

function zynle_auth_header(): string {
    return 'Authorization: Basic ' . base64_encode(ZYNLE_AUTH_EMAIL . ':' . ZYNLE_AUTH_PASSWORD);
}

// Test card validation function for sandbox simulation
function zynle_validate_test_card($cardnumber, $expirymonth, $expiryyear, $cvv) {
    $test_cards = [
        ZYNLE_TEST_CARD_VISA => ['type' => 'visa', 'valid_cvv' => ['123', '999']],
        ZYNLE_TEST_CARD_MASTERCARD => ['type' => 'mastercard', 'valid_cvv' => ['123', '999']],
        ZYNLE_TEST_CARD_DISCOVER => ['type' => 'discover', 'valid_cvv' => ['123', '999']],
        ZYNLE_TEST_CARD_AMEX => ['type' => 'amex', 'valid_cvv' => ['1234', '9999']],
    ];
    
    $cardnumber = preg_replace('/\s+/', '', $cardnumber);
    
    foreach ($test_cards as $test_card => $details) {
        if (strpos($cardnumber, substr($test_card, 0, 6)) === 0) {
            // Check if CVV is valid for this card type
            $valid_cvv = in_array($cvv, $details['valid_cvv']);
            
            // Check if expiry date is in the future
            $current_year = date('Y');
            $current_month = date('m');
            $expiry_valid = ($expiryyear > $current_year) || 
                           ($expiryyear == $current_year && $expirymonth >= $current_month);
            
            return [
                'valid' => $valid_cvv && $expiry_valid,
                'type' => $details['type'],
                'message' => $valid_cvv ? 'Card valid' : 'Invalid CVV for test card'
            ];
        }
    }
    
    return ['valid' => false, 'type' => 'unknown', 'message' => 'Not a recognized test card'];
}

// Simulate payment response for sandbox
function zynle_simulate_payment($amount, $cardnumber, $expirymonth, $expiryyear, $cvv) {
    if (!ZYNLE_SANDBOX_MODE) {
        return null; // Only simulate in sandbox mode
    }
    
    $validation = zynle_validate_test_card($cardnumber, $expirymonth, $expiryyear, $cvv);
    
    if (!$validation['valid']) {
        return [
            'code' => '200',
            'message' => 'Payment failed: ' . $validation['message'],
            'success' => false
        ];
    }
    
    // Simulate different scenarios based on card number
    $last_four = substr($cardnumber, -4);
    $scenario = intval($last_four) % 5;
    
    switch ($scenario) {
        case 0: // Success
            return [
                'code' => '100',
                'message' => 'Transaction approved',
                'reference' => 'ZP' . time() . rand(1000, 9999),
                'transaction_id' => 'TXN' . time(),
                'success' => true,
                'amount' => $amount,
                'currency' => 'ZMW'
            ];
            
        case 1: // Insufficient funds
            return [
                'code' => '201',
                'message' => 'Insufficient funds',
                'success' => false
            ];
            
        case 2: // Invalid card
            return [
                'code' => '202',
                'message' => 'Invalid card details',
                'success' => false
            ];
            
        case 3: // Network error
            return [
                'code' => '300',
                'message' => 'Network timeout, please try again',
                'success' => false
            ];
            
        default: // General decline
            return [
                'code' => '200',
                'message' => 'Transaction declined',
                'success' => false
            ];
    }
}

// Real API call function for production
function zynle_process_real_payment($request_id, $amount, $appointment_data, $card_details) {
    $key = zynle_compute_key($request_id);
    
    // Split name for first/last
    $first = $last = $appointment_data['customer_name'];
    if (strpos($appointment_data['customer_name'], ' ') !== false) {
        [$first, $last] = explode(' ', $appointment_data['customer_name'], 2);
    }

    $qs = http_build_query([
        'api_id'       => ZYNLE_API_ID,
        'merchant_id'  => ZYNLE_MERCHANT_ID,
        'key'          => $key,
        'request_id'   => $request_id,
        'amount'       => number_format($amount, 2, '.', ''),
        'product'      => $appointment_data['service_type'] . ' (' . strtoupper($appointment_data['slot']) . ')',
        'comment'      => 'SalonSync booking #' . $appointment_data['id'],
        'cardnumber'   => $card_details['cardnumber'],
        'expirymonth'  => $card_details['expirymonth'],
        'expiryyear'   => $card_details['expiryyear'],
        'cvv'          => $card_details['cvv'],
        'cardtype'     => $card_details['cardtype'],
        'currency'     => 'ZMW',
        'nameoncard'   => $appointment_data['customer_name'],
        'firstname'    => $first,
        'lastname'     => $last,
        'email'        => $appointment_data['customer_email'],
        'phone'        => '260955000679', // sample from doc
        'country'      => 'ZM'
    ]);

    $url = rtrim(ZYNLE_BASE_URL, '/') . '/runTranAuthCapture?' . $qs;

    $ch = curl_init($url);
    $headers = [
        'Content-type: application/xml',
        zynle_auth_header(),
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);

    $responseBody = curl_exec($ch);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        return ['success' => false, 'message' => 'Payment gateway error: ' . $curlErr];
    }

    $resp = json_decode($responseBody, true);
    
    if (!$resp) {
        return ['success' => false, 'message' => 'Invalid gateway response'];
    }

    return [
        'success' => ($resp['code'] ?? '') === '100',
        'message' => $resp['message'] ?? 'Payment processing error',
        'reference' => $resp['reference'] ?? $request_id,
        'code' => $resp['code'] ?? '000'
    ];
}
?>