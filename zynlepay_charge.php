<?php
// salonsync/payments/zynlepay_charge.php
session_start();
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config/zynlepay.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

// 1) Validate input - handle both GET and POST
if (($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') || !isset($_REQUEST['appointment_id'])) {
    http_response_code(400);
    exit('Bad request');
}

$appointment_id = (int)$_REQUEST['appointment_id'];
$user_id = $_SESSION['user_id'];

// 2) Load appointment + user for payer details and amount
$stmt = $conn->prepare("
    SELECT a.id, a.user_id, a.payment_amount, a.slot, a.service_type, a.appointment_date,
           u.name AS customer_name, u.email AS customer_email,
           a.status, a.payment_status
    FROM appointments a
    JOIN users u ON u.id = a.user_id
    WHERE a.id = ? AND a.user_id = ?
");
$stmt->bind_param("ii", $appointment_id, $user_id);
$stmt->execute();
$res = $stmt->get_result();
$ap = $res->fetch_assoc();
$stmt->close();

if (!$ap) { 
    exit('Appointment not found or access denied');
}

// Check if already paid
if ($ap['payment_status'] === 'paid') {
    header('Location: ../booking_form.php?success=1&id=' . $appointment_id);
    exit;
}

$amount = (float)$ap['payment_amount'];
if ($amount <= 0) { 
    // Set default amount if not set
    $amount = 100.00; // Default service fee
}

// Handle payment form display (GET request)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    displayPaymentForm($ap, $amount, $appointment_id);
    exit;
}

// Handle payment processing (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    processPayment($conn, $ap, $amount, $appointment_id);
    exit;
}

function displayPaymentForm($ap, $amount, $appointment_id) {
    $error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Complete Payment - SalonSync</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    </head>
    <body class="bg-light">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0"><i class="bi bi-credit-card"></i> Complete Payment</h4>
                        </div>
                        <div class="card-body">
                            <?php if ($error): ?>
                                <div class="alert alert-danger">
                                    <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Appointment Summary -->
                            <div class="alert alert-info">
                                <h5>Appointment Summary</h5>
                                <p class="mb-1"><strong>Service:</strong> <?php echo htmlspecialchars($ap['service_type']); ?></p>
                                <p class="mb-1"><strong>Date:</strong> <?php echo htmlspecialchars($ap['appointment_date']); ?></p>
                                <p class="mb-1"><strong>Time:</strong> <?php echo htmlspecialchars($ap['slot']); ?></p>
                                <p class="mb-0"><strong>Amount:</strong> <span class="text-success">ZMW <?php echo number_format($amount, 2); ?></span></p>
                            </div>

                            <!-- Test Card Information -->
                            <?php if (ZYNLE_SANDBOX_MODE): ?>
                            <div class="alert alert-warning">
                                <h6><i class="bi bi-info-circle"></i> Sandbox Test Cards</h6>
                                <small>
                                    <strong>Visa:</strong> 4111111111111111<br>
                                    <strong>MasterCard:</strong> 5555555555554444<br>
                                    <strong>AMEX:</strong> 378282246310005<br>
                                    <strong>CVV:</strong> 123 (1234 for AMEX)<br>
                                    <strong>Expiry:</strong> Any future date<br>
                                    <em>Different scenarios simulated based on card number</em>
                                </small>
                            </div>
                            <?php endif; ?>

                            <form method="POST" id="paymentForm">
                                <input type="hidden" name="appointment_id" value="<?php echo $appointment_id; ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label">Card Number</label>
                                    <input type="text" name="cardnumber" class="form-control" 
                                           placeholder="4111111111111111" required maxlength="19"
                                           pattern="[0-9\s]{13,19}">
                                    <small class="text-muted">Enter your 16-digit card number</small>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">Expiry Month</label>
                                        <select name="expirymonth" class="form-control" required>
                                            <option value="">MM</option>
                                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                                <option value="<?php echo sprintf('%02d', $i); ?>"><?php echo sprintf('%02d', $i); ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Expiry Year</label>
                                        <select name="expiryyear" class="form-control" required>
                                            <option value="">YYYY</option>
                                            <?php 
                                            $current_year = date('Y');
                                            for ($i = $current_year; $i <= $current_year + 10; $i++): ?>
                                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">CVV</label>
                                        <input type="text" name="cvv" class="form-control" 
                                               placeholder="123" required maxlength="4"
                                               pattern="[0-9]{3,4}">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Card Type</label>
                                    <select name="cardtype" class="form-control" required>
                                        <option value="">Select Card Type</option>
                                        <option value="visa">Visa</option>
                                        <option value="mastercard">MasterCard</option>
                                        <option value="amex">American Express</option>
                                        <option value="discover">Discover</option>
                                    </select>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="bi bi-lock-fill"></i> Pay ZMW <?php echo number_format($amount, 2); ?>
                                    </button>
                                    <a href="../booking_form.php" class="btn btn-secondary">Cancel Payment</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Auto-detect card type and format card number
            document.querySelector('input[name="cardnumber"]').addEventListener('input', function(e) {
                let cardNumber = e.target.value.replace(/\s/g, '');
                const cardTypeSelect = document.querySelector('select[name="cardtype"]');
                
                // Format card number with spaces
                if (cardNumber.length > 0) {
                    cardNumber = cardNumber.match(/.{1,4}/g).join(' ');
                    e.target.value = cardNumber;
                }
                
                // Auto-detect card type
                const cleanNumber = cardNumber.replace(/\s/g, '');
                
                if (/^4/.test(cleanNumber)) {
                    cardTypeSelect.value = 'visa';
                } else if (/^5[1-5]/.test(cleanNumber)) {
                    cardTypeSelect.value = 'mastercard';
                } else if (/^3[47]/.test(cleanNumber)) {
                    cardTypeSelect.value = 'amex';
                } else if (/^6(?:011|5)/.test(cleanNumber)) {
                    cardTypeSelect.value = 'discover';
                }
                
                // Adjust CVV maxlength for AMEX
                const cvvInput = document.querySelector('input[name="cvv"]');
                if (cardTypeSelect.value === 'amex') {
                    cvvInput.setAttribute('maxlength', '4');
                    cvvInput.setAttribute('pattern', '[0-9]{4}');
                    cvvInput.setAttribute('placeholder', '1234');
                } else {
                    cvvInput.setAttribute('maxlength', '3');
                    cvvInput.setAttribute('pattern', '[0-9]{3}');
                    cvvInput.setAttribute('placeholder', '123');
                }
            });
            
            // Pre-fill test values in sandbox mode
            document.addEventListener('DOMContentLoaded', function() {
                <?php if (ZYNLE_SANDBOX_MODE): ?>
                // Auto-fill test values for demo
                setTimeout(() => {
                    document.querySelector('input[name="cardnumber"]').value = '4111111111111111';
                    document.querySelector('select[name="expirymonth"]').value = '12';
                    document.querySelector('select[name="expiryyear"]').value = '2026';
                    document.querySelector('input[name="cvv"]').value = '123';
                    document.querySelector('select[name="cardtype"]').value = 'visa';
                }, 100);
                <?php endif; ?>
            });
        </script>

        <style>
            body { background-color: #f8f9fa; }
            .card { border: none; border-radius: 15px; }
            .card-header { border-radius: 15px 15px 0 0 !important; }
            .btn-success { 
                background: linear-gradient(45deg, #28a745, #20c997); 
                border: none; 
                font-weight: bold;
            }
            .btn-success:hover {
                background: linear-gradient(45deg, #218838, #1e9e8a);
            }
            .form-control:focus {
                border-color: #28a745;
                box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
            }
        </style>
    </body>
    </html>
    <?php
}

function processPayment($conn, $ap, $amount, $appointment_id) {
    // Get form data
    $cardnumber = preg_replace('/\s+/', '', $_POST['cardnumber'] ?? '');
    $expirymonth = $_POST['expirymonth'] ?? '';
    $expiryyear = $_POST['expiryyear'] ?? '';
    $cvv = $_POST['cvv'] ?? '';
    $cardtype = $_POST['cardtype'] ?? '';

    // Validate input
    if (empty($cardnumber) || empty($expirymonth) || empty($expiryyear) || empty($cvv) || empty($cardtype)) {
        header('Location: zynlepay_charge.php?appointment_id=' . $appointment_id . '&error=Please fill all card details');
        exit;
    }

    // Generate unique request ID
    $request_id = 'ZP' . time() . '_' . $appointment_id;
    
    if (ZYNLE_SANDBOX_MODE) {
        // Use simulation for sandbox
        $payment_result = zynle_simulate_payment($amount, $cardnumber, $expirymonth, $expiryyear, $cvv);
    } else {
        // Real API call for production
        $card_details = [
            'cardnumber' => $cardnumber,
            'expirymonth' => $expirymonth,
            'expiryyear' => $expiryyear,
            'cvv' => $cvv,
            'cardtype' => $cardtype
        ];
        $payment_result = zynle_process_real_payment($request_id, $amount, $ap, $card_details);
    }

    // Update database based on result
    if ($payment_result['success']) {
        // Payment successful
        $paymentRef = $payment_result['reference'] ?? $request_id;
        
        $stmt = $conn->prepare("
            UPDATE appointments 
            SET payment_status = 'paid', 
                status = 'confirmed',
                payment_reference = ?,
                gateway_request_id = ?,
                paid_at = NOW(),
                payment_amount = ?
            WHERE id = ?
        ");
        $stmt->bind_param("ssdi", $paymentRef, $request_id, $amount, $appointment_id);
        
        if ($stmt->execute()) {
            header('Location: ../booking_form.php?success=1&id=' . $appointment_id);
        } else {
            header('Location: zynlepay_charge.php?appointment_id=' . $appointment_id . '&error=Database update failed');
        }
        $stmt->close();
    } else {
        // Payment failed
        $error_msg = $payment_result['message'] ?? 'Payment failed';
        
        $stmt = $conn->prepare("
            UPDATE appointments 
            SET payment_status = 'failed',
                gateway_request_id = ?,
                payment_note = ?
            WHERE id = ?
        ");
        $stmt->bind_param("ssi", $request_id, $error_msg, $appointment_id);
        $stmt->execute();
        $stmt->close();
        
        header('Location: zynlepay_charge.php?appointment_id=' . $appointment_id . '&error=' . urlencode($error_msg));
    }
    exit;
}

$conn->close();
?>