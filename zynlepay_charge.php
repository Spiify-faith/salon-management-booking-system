<?php
// salonsync/payments/zynlepay_charge.php
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config/zynlepay.php';

// 1) Validate input
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['appointment_id'])) {
  http_response_code(400);
  exit('Bad request');
}
$appointment_id = (int)$_POST['appointment_id'];

// 2) Load appointment + user for payer details and amount
$stmt = $conn->prepare("
  SELECT a.id, a.user_id, a.payment_amount, a.slot, a.service_type, a.appointment_date,
         u.name AS customer_name, u.email AS customer_email
  FROM appointments a
  JOIN users u ON u.id = a.user_id
  WHERE a.id = ?
");
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$res = $stmt->get_result();
$ap = $res->fetch_assoc();
$stmt->close();

if (!$ap) { exit('Appointment not found'); }

// Optionally, compute price here if you don’t store it; but you said you do.
// $price = computePrice($ap['service_type'], $ap['slot']); // else use stored:
$amount = (float)$ap['payment_amount'];
if ($amount <= 0) { exit('Invalid amount'); }

// 3) Build ZynlePay request parameters
$request_id = (string)time() . '_' . $appointment_id;  // unique
$key        = zynle_compute_key($request_id);

// Minimal payer fields (use real user info if you have)
// Split name for first/last (crude split)
$first = $last = $ap['customer_name'];
if (strpos($ap['customer_name'], ' ') !== false) {
  [$first, $last] = explode(' ', $ap['customer_name'], 2);
}

// ⚠️ For sandbox/class use only: direct card fields (test cards). In production, DO NOT collect card numbers.
// Here, for demo, we’ll use a test VISA from the PDF sample (change to form inputs if you want to type it):
$cardnumber  = '4111111111111111';
$expirymonth = '01';
$expiryyear  = '2043';
$cvv         = '123';
$cardtype    = 'visa';

// 4) Construct URL to runTranAuthCapture (authorize + capture)
$qs = http_build_query([
  'api_id'       => ZYNLE_API_ID,
  'merchant_id'  => ZYNLE_MERCHANT_ID,
  'key'          => $key,
  'request_id'   => $request_id,
  'amount'       => number_format($amount, 2, '.', ''),
  'product'      => $ap['service_type'] . ' (' . strtoupper($ap['slot']) . ')',
  'comment'      => 'SalonSync booking #' . $appointment_id,
  // 'reference'  => optional unique reference of yours
  'cardnumber'   => $cardnumber,
  'expirymonth'  => $expirymonth,
  'expiryyear'   => $expiryyear,
  'cvv'          => $cvv,
  'cardtype'     => $cardtype,
  'currency'     => 'ZMW',
  'nameoncard'   => $ap['customer_name'],
  'firstname'    => $first,
  'lastname'     => $last,
  'address'      => 'Test Address',
  'city'         => 'Lusaka',
  'state'        => 'Lusaka',
  'email'        => $ap['customer_email'],
  'phone'        => '260955000679', // sample from doc
  'country'      => 'ZM'
]);

$url = rtrim(ZYNLE_BASE_URL, '/') . '/runTranAuthCapture?' . $qs;

// 5) cURL call
$ch = curl_init($url);
$headers = [
  'Content-type: application/xml',   // matches the PDF sample
  zynle_auth_header(),               // if your sandbox requires it
];
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);

$responseBody = curl_exec($ch);
$curlErr      = curl_error($ch);
$httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($curlErr) {
  exit('Payment gateway error: ' . htmlspecialchars($curlErr));
}

// 6) Parse response (doc shows JSON decode in sample)
$resp = json_decode($responseBody, true);

// Guard if not JSON
if (!$resp) {
  // try to dump for debugging
  // file_put_contents(__DIR__.'/zynlepay_last_response.txt', $responseBody);
  exit('Unexpected gateway response');
}

// The PDF lists response codes (100 = success)
$code = $resp['code'] ?? null; // adjust if their field name differs
$msg  = $resp['message'] ?? ($resp['desc'] ?? '');

// 7) Update DB based on outcome
if ((string)$code === '100') {
  // Success
  $paymentRef = $resp['reference'] ?? $request_id;

  $u = $conn->prepare("
    UPDATE appointments
       SET payment_status = 'paid',
           status = 'pending',            -- admin will confirm later
           payment_reference = ?,
           gateway_request_id = ?
     WHERE id = ?
  ");
  $u->bind_param("ssi", $paymentRef, $request_id, $appointment_id);
  $u->execute();
  $u->close();

  header('Location: /salonsync/payment_result.php?ok=1&appt=' . $appointment_id);
  exit;
} else {
  // Failed
  $u = $conn->prepare("
    UPDATE appointments
       SET payment_status = 'failed'
     WHERE id = ?
  ");
  $u->bind_param("i", $appointment_id);
  $u->execute();
  $u->close();

  header('Location: /salonsync/payment_result.php?ok=0&msg=' . urlencode($msg));
  exit;
}
