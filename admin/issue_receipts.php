<?php
session_start();
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../db.php";

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../lib/PHPMailer-6.8.0/src/Exception.php';
require '../lib/PHPMailer-6.8.0/src/PHPMailer.php';
require '../lib/PHPMailer-6.8.0/src/SMTP.php';

// Function to generate and send receipt
function generateAndSendReceipt($conn, $booking_id) {
    // Get booking details - REMOVED phone column reference
    $stmt = $conn->prepare("
        SELECT a.*, u.name as client_name, u.email, s.name as staff_name, s.role as staff_role
        FROM appointments a 
        JOIN users u ON a.user_id = u.id 
        JOIN staff s ON a.staff_id = s.id 
        WHERE a.id = ?
    ");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$booking) {
        return false;
    }
    
    // Generate receipt HTML
    $receipt_html = generateReceiptHTML($booking);
    
    // Save receipt to database using your existing structure
    $receipt_number = 'SS' . date('Ymd') . str_pad($booking_id, 4, '0', STR_PAD_LEFT);
    
    $stmt = $conn->prepare("INSERT INTO receipts (user_id, appointment_id, total, issued_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iid", $booking['user_id'], $booking_id, $booking['payment_amount']);
    $stmt->execute();
    $receipt_id = $conn->insert_id;
    $stmt->close();
    
    // Send email to client
    $email_sent = sendReceiptEmail($booking['email'], $booking['client_name'], $receipt_html, $receipt_number);
    
    return [
        'receipt_id' => $receipt_id,
        'receipt_number' => $receipt_number,
        'email_sent' => $email_sent
    ];
}

// Function to generate receipt HTML
function generateReceiptHTML($booking) {
    $appointment_date = date('F j, Y', strtotime($booking['appointment_date']));
    $appointment_time = date('g:i A', strtotime($booking['appointment_date']));
    $issued_date = date('F j, Y');
    
    ob_start();
    ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt - SalonSync</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 800px; margin: 0 auto; border: 1px solid #ddd; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #ec4899; padding-bottom: 10px; }
        .logo { font-size: 24px; font-weight: bold; color: #ec4899; }
        .receipt-title { font-size: 20px; margin: 10px 0; }
        .receipt-info { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .section { margin-bottom: 15px; }
        .section-title { font-weight: bold; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #eee; }
        th { background-color: #f9f9f9; }
        .total { font-weight: bold; font-size: 18px; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #777; }
        .thank-you { text-align: center; margin: 20px 0; font-style: italic; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">SalonSync</div>
            <div class="receipt-title">Booking Receipt</div>
        </div>
        
        <div class="receipt-info">
            <div>
                <strong>Receipt Number:</strong> SS<?= date('Ymd') . str_pad($booking['id'], 4, '0', STR_PAD_LEFT) ?><br>
                <strong>Issue Date:</strong> <?= $issued_date ?>
            </div>
            <div>
                <strong>Appointment Date:</strong> <?= $appointment_date ?><br>
                <strong>Appointment Time:</strong> <?= $appointment_time ?>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">Client Information</div>
            <strong>Name:</strong> <?= htmlspecialchars($booking['client_name']) ?><br>
            <strong>Email:</strong> <?= htmlspecialchars($booking['email']) ?><br>
        </div>
        
        <div class="section">
            <div class="section-title">Service Details</div>
            <table>
                <tr>
                    <th>Description</th>
                    <th>Staff</th>
                    <th>Amount</th>
                </tr>
                <tr>
                    <td><?= htmlspecialchars($booking['service_type']) ?></td>
                    <td><?= htmlspecialchars($booking['staff_name']) ?> (<?= htmlspecialchars($booking['staff_role']) ?>)</td>
                    <td>K<?= number_format($booking['payment_amount'], 2) ?></td>
                </tr>
                <?php if ($booking['payment_amount'] > 0): ?>
                <tr>
                    <td colspan="2" style="text-align: right;"><strong>Total:</strong></td>
                    <td class="total">k<?= number_format($booking['payment_amount'], 2) ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        
        <div class="section">
            <div class="section-title">Payment Information</div>
            <strong>Status:</strong> 
            <span style="color: <?= $booking['payment_status'] == 'paid' ? 'green' : 'red' ?>;">
                <?= ucfirst($booking['payment_status']) ?>
            </span><br>
            <?php if ($booking['payment_status'] == 'paid' && !empty($booking['paid_at'])): ?>
            <strong>Paid On:</strong> <?= date('F j, Y g:i A', strtotime($booking['paid_at'])) ?><br>
            <?php if (!empty($booking['transaction_id'])): ?>
            <strong>Transaction ID:</strong> <?= htmlspecialchars($booking['transaction_id']) ?>
            <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <div class="thank-you">
            Thank you for choosing SalonSync! We look forward to serving you.
        </div>
        
        <div class="footer">
            <p>SalonSync • kalundu , lusaka ,zambia</p>
            <p>Phone: +260 9612 34567 • Email: info@salonsync.com</p>
            <p>This is an automated receipt. Please bring this with you to your appointment.</p>
        </div>
    </div>
</body>
</html>
    <?php
    return ob_get_clean();
}

// Function to send receipt email
function sendReceiptEmail($to_email, $client_name, $receipt_html, $receipt_number) {
    $subject = "Your SalonSync Booking Receipt #$receipt_number";

    $message = "
    <html>
    <body>
        <p>Dear $client_name,</p>
        <p>Thank you for booking with SalonSync! Your appointment has been confirmed.</p>
        <p>Please find your receipt attached below. You can also view it online at any time.</p>
        <p>If you have any questions or need to reschedule, please contact us at info@salonsync.com or (+260) 96123-4567.</p>
        <p>We look forward to seeing you!</p>
        <br>
        <p>Best regards,<br>The SalonSync Team</p>
        <hr>
        $receipt_html
    </body>
    </html>
    ";

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'landernerd4@gmail.com';
        $mail->Password = 'ojduiztpldjszuwy';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('landernerd4@gmail.com', 'SalonSync');
        $mail->addAddress($to_email, $client_name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

// Handle receipt generation
$message = "";
$message_type = "";

if (isset($_GET['generate_receipt'])) {
    $booking_id = (int)$_GET['generate_receipt'];

    $result = generateAndSendReceipt($conn, $booking_id);

    if ($result) {
        if ($result['email_sent']) {
            $message = "Receipt generated and emailed to client successfully!";
        } else {
            $message = "Receipt generated but email sending failed. Please check email configuration.";
        }
        $message_type = "success";
    } else {
        $message = "Error generating receipt. Booking not found.";
        $message_type = "error";
    }
}

// Fetch confirmed bookings that might need receipts
$confirmed_bookings = $conn->query("
    SELECT a.*, u.name as client_name, u.email, s.name as staff_name,
           (SELECT COUNT(*) FROM receipts WHERE appointment_id = a.id) as has_receipt
    FROM appointments a 
    JOIN users u ON a.user_id = u.id 
    JOIN staff s ON a.staff_id = s.id 
    WHERE a.status = 'confirmed'
    ORDER BY a.appointment_date DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Issue Receipts - SalonSync</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
  <header class="bg-pink-500 text-white py-4 shadow-md">
    <div class="max-w-7xl mx-auto px-4 flex justify-between items-center">
      <div class="flex items-center">
        <a href="dashboard.php" class="mr-4 text-white">
          <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-xl font-bold">SalonSync Admin - Issue Receipts</h1>
      </div>
      <div>
        <span class="mr-4">Welcome, <?= htmlspecialchars($_SESSION["admin_name"]) ?></span>
        <a href="logout.php" class="bg-white text-pink-500 px-3 py-1 rounded hover:bg-pink-100 transition">Logout</a>
      </div>
    </div>
  </header>

  <main class="max-w-7xl mx-auto px-4 py-8">
    <?php if (!empty($message)): ?>
      <div class="mb-6 p-4 rounded-md <?= $message_type == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
        <?= $message ?>
      </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
      <div class="bg-pink-500 text-white px-6 py-4">
        <h2 class="text-xl font-semibold">Issue Receipts for Confirmed Bookings</h2>
      </div>
      <div class="p-6">
        <p class="text-gray-600 mb-4">
          Generate and send digital receipts to clients for their confirmed bookings. Receipts will be emailed to clients
          and stored in the system for future reference.
        </p>
        
        <?php if ($confirmed_bookings->num_rows > 0): ?>
          <div class="overflow-x-auto">
            <table class="w-full border-collapse">
              <thead>
                <tr class="bg-gray-50">
                  <th class="p-3 text-left text-sm font-medium text-gray-700">Client</th>
                  <th class="p-3 text-left text-sm font-medium text-gray-700">Service</th>
                  <th class="p-3 text-left text-sm font-medium text-gray-700">Date & Time</th>
                  <th class="p-3 text-left text-sm font-medium text-gray-700">Amount</th>
                  <th class="p-3 text-left text-sm font-medium text-gray-700">Payment Status</th>
                  <th class="p-3 text-left text-sm font-medium text-gray-700">Receipt</th>
                  <th class="p-3 text-left text-sm font-medium text-gray-700">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($booking = $confirmed_bookings->fetch_assoc()): 
                  $appointment_date = date('M j, Y', strtotime($booking['appointment_date']));
                  $appointment_time = date('g:i A', strtotime($booking['appointment_date']));
                ?>
                  <tr class="border-t hover:bg-gray-50">
                    <td class="p-3">
                      <div class="font-medium"><?= htmlspecialchars($booking['client_name']) ?></div>
                      <div class="text-sm text-gray-500"><?= htmlspecialchars($booking['email']) ?></div>
                    </td>
                    <td class="p-3"><?= htmlspecialchars($booking['service_type']) ?></td>
                    <td class="p-3">
                      <div><?= $appointment_date ?></div>
                      <div class="text-sm text-gray-500"><?= $appointment_time ?></div>
                    </td>
                    <td class="p-3">k<?= number_format($booking['payment_amount'], 2) ?></td>
                    <td class="p-3">
                      <span class="px-2 py-1 rounded-full text-xs <?= $booking['payment_status'] == 'paid' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                        <?= ucfirst($booking['payment_status']) ?>
                      </span>
                    </td>
                    <td class="p-3">
                      <?php if ($booking['has_receipt'] > 0): ?>
                        <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-700">
                          <i class="fas fa-check mr-1"></i> Issued
                        </span>
                      <?php else: ?>
                        <span class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-700">
                          Not Issued
                        </span>
                      <?php endif; ?>
                    </td>
                    <td class="p-3">
                      <a href="?generate_receipt=<?= $booking['id'] ?>" 
                         class="bg-pink-500 text-white px-3 py-1 rounded text-sm hover:bg-pink-600 inline-flex items-center">
                        <i class="fas fa-receipt mr-1"></i> Issue Receipt
                      </a>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="text-center py-8">
            <i class="fas fa-receipt text-4xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-700">No Confirmed Bookings</h3>
            <p class="text-gray-500">There are no confirmed bookings to generate receipts for.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-md overflow-hidden">
      <div class="bg-pink-500 text-white px-6 py-4">
        <h2 class="text-xl font-semibold">Recent Receipts</h2>
      </div>
      <div class="p-6">
        <?php
        // Fetch recent receipts
        $recent_receipts = $conn->query("
          SELECT r.*, a.service_type, u.name as client_name 
          FROM receipts r
          JOIN appointments a ON r.appointment_id = a.id
          JOIN users u ON r.user_id = u.id
          ORDER BY r.issued_at DESC 
          LIMIT 5
        ");
        ?>
        
        <?php if ($recent_receipts->num_rows > 0): ?>
          <div class="space-y-4">
            <?php while ($receipt = $recent_receipts->fetch_assoc()): 
              $issued_date = date('M j, Y g:i A', strtotime($receipt['issued_at']));
              $receipt_number = 'SS' . date('Ymd', strtotime($receipt['issued_at'])) . str_pad($receipt['appointment_id'], 4, '0', STR_PAD_LEFT);
            ?>
              <div class="flex justify-between items-center border-b pb-3">
                <div>
                  <div class="font-medium"><?= htmlspecialchars($receipt['client_name']) ?></div>
                  <div class="text-sm text-gray-500"><?= htmlspecialchars($receipt['service_type']) ?></div>
                  <div class="text-xs text-gray-400">Issued: <?= $issued_date ?></div>
                </div>
                <div class="text-right">
                  <div class="text-sm font-mono text-pink-600"><?= $receipt_number ?></div>
                  <div class="text-sm">k<?= number_format($receipt['total'], 2) ?></div>
                </div>
              </div>
            <?php endwhile; ?>
          </div>
        <?php else: ?>
          <div class="text-center py-8">
            <i class="fas fa-file-invoice text-4xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-700">No Receipts Issued Yet</h3>
            <p class="text-gray-500">Receipts you generate will appear here.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </main>
</body>
</html>