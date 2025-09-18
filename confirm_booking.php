<?php
session_start();
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../db.php";

$message = "";
$message_type = "";

// Handle payment status update
if (isset($_POST['update_payment'])) {
    $booking_id = (int)$_POST['booking_id'];
    $payment_status = $_POST['payment_status'];
    $payment_amount = !empty($_POST['payment_amount']) ? (float)$_POST['payment_amount'] : 0;
    
    if ($payment_status == 'paid') {
        // Update payment status to paid and set payment date
        $stmt = $conn->prepare("UPDATE appointments SET payment_status = ?, payment_amount = ?, paid_at = NOW() WHERE id = ?");
        $stmt->bind_param("sdi", $payment_status, $payment_amount, $booking_id);
    } else {
        // Update payment status to unpaid and clear payment date
        $stmt = $conn->prepare("UPDATE appointments SET payment_status = ?, paid_at = NULL WHERE id = ?");
        $stmt->bind_param("si", $payment_status, $booking_id);
    }
    
    if ($stmt->execute()) {
        $message = "Payment status updated successfully!";
        $message_type = "success";
    } else {
        $message = "Error updating payment status: " . $conn->error;
        $message_type = "error";
    }
    $stmt->close();
}

// Handle booking confirmation
if (isset($_GET['confirm'])) {
    $booking_id = (int)$_GET['confirm'];
    
    // Get booking details first
    $stmt = $conn->prepare("
        SELECT a.*, u.name as client_name, u.email, s.name as staff_name 
        FROM appointments a 
        JOIN users u ON a.user_id = u.id 
        JOIN staff s ON a.staff_id = s.id 
        WHERE a.id = ?
    ");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($booking) {
        // Check if the slot is already taken by a confirmed booking
        $check_stmt = $conn->prepare("
            SELECT COUNT(*) as count FROM appointments 
            WHERE staff_id = ? 
            AND DATE(appointment_date) = DATE(?) 
            AND slot = ? 
            AND status = 'confirmed' 
            AND id != ?
        ");
        $check_stmt->bind_param("issi", $booking['staff_id'], $booking['appointment_date'], $booking['slot'], $booking_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result()->fetch_assoc();
        $check_stmt->close();
        
        if ($result['count'] > 0) {
            $message = "This time slot is already booked by another confirmed appointment!";
            $message_type = "error";
        } else {
            // Update the booking status to confirmed
            $update_stmt = $conn->prepare("UPDATE appointments SET status = 'confirmed' WHERE id = ?");
            $update_stmt->bind_param("i", $booking_id);
            
            if ($update_stmt->execute()) {
                $message = "Booking confirmed successfully! This time slot is now locked.";
                $message_type = "success";
            } else {
                $message = "Error confirming booking: " . $conn->error;
                $message_type = "error";
            }
            $update_stmt->close();
        }
    } else {
        $message = "Booking not found!";
        $message_type = "error";
    }
}

// Handle booking cancellation
if (isset($_GET['cancel'])) {
    $booking_id = (int)$_GET['cancel'];
    
    $stmt = $conn->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    
    if ($stmt->execute()) {
        $message = "Booking cancelled successfully!";
        $message_type = "success";
    } else {
        $message = "Error cancelling booking: " . $conn->error;
        $message_type = "error";
    }
    $stmt->close();
}

// Handle marking as completed
if (isset($_GET['complete'])) {
    $booking_id = (int)$_GET['complete'];
    
    $stmt = $conn->prepare("UPDATE appointments SET status = 'completed' WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    
    if ($stmt->execute()) {
        $message = "Booking marked as completed!";
        $message_type = "success";
    } else {
        $message = "Error completing booking: " . $conn->error;
        $message_type = "error";
    }
    $stmt->close();
}

// Fetch bookings that need confirmation (pending and pending_payment)
$pending_bookings = $conn->query("
    SELECT a.*, u.name as client_name, u.email, s.name as staff_name 
    FROM appointments a 
    JOIN users u ON a.user_id = u.id 
    JOIN staff s ON a.staff_id = s.id 
    WHERE a.status IN ('pending', 'pending_payment') 
    ORDER BY a.appointment_date, a.created_at DESC
");

// Fetch confirmed bookings for today and future dates
$confirmed_bookings = $conn->query("
    SELECT a.*, u.name as client_name, u.email, s.name as staff_name 
    FROM appointments a 
    JOIN users u ON a.user_id = u.id 
    JOIN staff s ON a.staff_id = s.id 
    WHERE a.status = 'confirmed' AND DATE(a.appointment_date) >= CURDATE()
    ORDER BY a.appointment_date, a.slot
");

// Fetch completed bookings
$completed_bookings = $conn->query("
    SELECT a.*, u.name as client_name, u.email, s.name as staff_name 
    FROM appointments a 
    JOIN users u ON a.user_id = u.id 
    JOIN staff s ON a.staff_id = s.id 
    WHERE a.status = 'completed' 
    ORDER BY a.appointment_date DESC 
    LIMIT 10
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Confirm Bookings - SalonSync</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .fade-in {
      animation: fadeIn 0.5s;
    }
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    .slide-in {
      animation: slideIn 0.3s ease-out;
    }
    @keyframes slideIn {
      from { transform: translateY(-10px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }
    .booking-card {
      transition: all 0.3s ease;
    }
    .booking-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    .tab-button {
      transition: all 0.3s ease;
    }
    .tab-button.active {
      background-color: #ec4899;
      color: white;
    }
    .payment-modal {
      transition: all 0.3s ease;
      transform: translateY(-20px);
      opacity: 0;
    }
    .payment-modal.open {
      transform: translateY(0);
      opacity: 1;
    }
  </style>
</head>
<body class="bg-gray-100 min-h-screen">
  <header class="bg-pink-500 text-white py-4 shadow-md">
    <div class="max-w-7xl mx-auto px-4 flex justify-between items-center">
      <div class="flex items-center">
        <a href="dashboard.php" class="mr-4 text-white">
          <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-xl font-bold">SalonSync Admin - Booking Management</h1>
      </div>
      <div>
        <span class="mr-4">Welcome, <?= htmlspecialchars($_SESSION["admin_name"]) ?></span>
        <a href="logout.php" class="bg-white text-pink-500 px-3 py-1 rounded hover:bg-pink-100 transition">Logout</a>
      </div>
    </div>
  </header>

  <main class="max-w-7xl mx-auto px-4 py-8">
    <?php if (!empty($message)): ?>
      <div class="mb-6 p-4 rounded-md <?= $message_type == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?> slide-in">
        <?= $message ?>
      </div>
    <?php endif; ?>

    <!-- Payment Update Modal -->
    <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
      <div class="bg-white rounded-xl shadow-lg w-full max-w-md mx-4 payment-modal">
        <div class="bg-pink-500 text-white px-6 py-4 rounded-t-xl">
          <h2 class="text-xl font-semibold">Update Payment Status</h2>
        </div>
        <div class="p-6">
          <form method="POST" id="paymentForm">
            <input type="hidden" name="booking_id" id="payment_booking_id">
            <input type="hidden" name="update_payment" value="1">
            
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Payment Status</label>
              <select name="payment_status" id="payment_status" class="w-full px-4 py-2 border rounded-md focus:ring-pink-500 focus:border-pink-500" onchange="togglePaymentAmount()">
                <option value="unpaid">Unpaid</option>
                <option value="paid">Paid</option>
              </select>
            </div>
            
            <div class="mb-4" id="amount_field">
              <label class="block text-sm font-medium text-gray-700 mb-2">Payment Amount (K)</label>
              <input type="number" name="payment_amount" id="payment_amount" step="0.01" min="0" class="w-full px-4 py-2 border rounded-md focus:ring-pink-500 focus:border-pink-500">
            </div>
            
            <div class="flex justify-end space-x-3">
              <button type="button" onclick="closePaymentModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-100">
                Cancel
              </button>
              <button type="submit" class="bg-pink-500 text-white px-4 py-2 rounded-md hover:bg-pink-600">
                Update Payment
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Tab Navigation -->
    <div class="mb-8 bg-white rounded-xl shadow-md p-2 flex">
      <button id="tab-pending" class="tab-button active flex-1 py-2 px-4 rounded-md font-medium" onclick="showTab('pending')">
        <i class="fas fa-clock mr-2"></i>Pending Approval
      </button>
      <button id="tab-confirmed" class="tab-button flex-1 py-2 px-4 rounded-md font-medium" onclick="showTab('confirmed')">
        <i class="fas fa-calendar-check mr-2"></i>Confirmed
      </button>
      <button id="tab-completed" class="tab-button flex-1 py-2 px-4 rounded-md font-medium" onclick="showTab('completed')">
        <i class="fas fa-check-circle mr-2"></i>Completed
      </button>
    </div>

    <!-- Pending Bookings Tab -->
    <div id="tab-content-pending" class="tab-content fade-in">
      <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
        <i class="fas fa-clock text-pink-500 mr-3"></i> Bookings Pending Approval
      </h2>
      
      <?php if ($pending_bookings->num_rows > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
          <?php while ($booking = $pending_bookings->fetch_assoc()): 
            $appointment_date = date('D, M j, Y', strtotime($booking['appointment_date']));
            $appointment_time = date('g:i A', strtotime($booking['appointment_date']));
            $slot_display = $booking['slot'] == 'morning' ? 'Morning' : 'Afternoon';
            $is_payment_pending = $booking['status'] == 'pending_payment';
          ?>
            <div class="booking-card bg-white rounded-xl shadow-md p-6 border-l-4 <?= $is_payment_pending ? 'border-orange-400' : 'border-yellow-400' ?>">
              <div class="flex justify-between items-start mb-4">
                <div>
                  <h3 class="font-semibold text-lg"><?= htmlspecialchars($booking['client_name']) ?></h3>
                  <p class="text-sm text-gray-500"><?= htmlspecialchars($booking['email']) ?></p>
                </div>
                <span class="px-2 py-1 <?= $is_payment_pending ? 'bg-orange-100 text-orange-700' : 'bg-yellow-100 text-yellow-700' ?> rounded-full text-xs">
                  <?= $is_payment_pending ? 'Payment Pending' : 'Pending Approval' ?>
                </span>
              </div>
              
              <div class="space-y-2 mb-4">
                <div class="flex items-center">
                  <i class="fas fa-calendar-day text-pink-500 w-5"></i>
                  <span class="ml-2"><?= $appointment_date ?></span>
                </div>
                <div class="flex items-center">
                  <i class="fas fa-clock text-pink-500 w-5"></i>
                  <span class="ml-2"><?= $appointment_time ?> (<?= $slot_display ?>)</span>
                </div>
                <div class="flex items-center">
                  <i class="fas fa-cut text-pink-500 w-5"></i>
                  <span class="ml-2"><?= htmlspecialchars($booking['service_type']) ?></span>
                </div>
                <div class="flex items-center">
                  <i class="fas fa-user-tie text-pink-500 w-5"></i>
                  <span class="ml-2"><?= htmlspecialchars($booking['staff_name']) ?></span>
                </div>
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <i class="fas fa-money-bill-wave text-pink-500 w-5"></i>
                    <span class="ml-2">$<?= number_format($booking['payment_amount'], 2) ?></span>
                    <span class="ml-2 text-xs <?= $booking['payment_status'] == 'paid' ? 'text-green-600' : 'text-red-600' ?>">
                      (<?= ucfirst($booking['payment_status']) ?>)
                    </span>
                  </div>
                  <button onclick="openPaymentModal(<?= $booking['id'] ?>, '<?= $booking['payment_status'] ?>', <?= $booking['payment_amount'] ?>)" 
                          class="text-xs text-blue-500 hover:text-blue-700">
                    <i class="fas fa-edit"></i> Change
                  </button>
                </div>
                <?php if (!empty($booking['notes'])): ?>
                  <div class="flex items-start mt-2">
                    <i class="fas fa-sticky-note text-pink-500 w-5 mt-1"></i>
                    <span class="ml-2 text-sm text-gray-600"><?= htmlspecialchars($booking['notes']) ?></span>
                  </div>
                <?php endif; ?>
              </div>
              
              <div class="flex space-x-2">
                <?php if ($booking['payment_status'] == 'paid' || $booking['payment_amount'] == 0): ?>
                  <a href="?confirm=<?= $booking['id'] ?>" 
                     class="flex-1 bg-pink-500 text-white text-center py-2 rounded-md hover:bg-pink-600 transition">
                    Confirm Booking
                  </a>
                <?php else: ?>
                  <span class="flex-1 bg-gray-300 text-gray-500 text-center py-2 rounded-md cursor-not-allowed">
                    Awaiting Payment
                  </span>
                <?php endif; ?>
                <a href="?cancel=<?= $booking['id'] ?>" 
                   onclick="return confirm('Are you sure you want to cancel this booking?')"
                   class="flex-1 bg-gray-200 text-gray-700 text-center py-2 rounded-md hover:bg-gray-300 transition">
                  Cancel
                </a>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      <?php else: ?>
        <div class="bg-white rounded-xl shadow-md p-8 text-center">
          <i class="fas fa-check-circle text-green-400 text-5xl mb-4"></i>
          <h3 class="text-xl font-semibold text-gray-700 mb-2">No Pending Bookings</h3>
          <p class="text-gray-500">All bookings are confirmed or there are no new bookings.</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Confirmed Bookings Tab -->
    <div id="tab-content-confirmed" class="tab-content hidden fade-in">
      <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
        <i class="fas fa-calendar-check text-pink-500 mr-3"></i> Confirmed Bookings
      </h2>
      
      <?php if ($confirmed_bookings->num_rows > 0): ?>
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="p-3 text-left text-sm font-medium text-gray-700">Client</th>
                  <th class="p-3 text-left text-sm font-medium text-gray-700">Service</th>
                  <th class="p-3 text-left text-sm font-medium text-gray-700">Staff</th>
                  <th class="p-3 text-left text-sm font-medium text-gray-700">Date & Time</th>
                  <th class="p-3 text-left text-sm font-medium text-gray-700">Payment</th>
                  <th class="p-3 text-left text-sm font-medium text-gray-700">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <?php while ($booking = $confirmed_bookings->fetch_assoc()): 
                  $appointment_date = date('M j, Y', strtotime($booking['appointment_date']));
                  $appointment_time = date('g:i A', strtotime($booking['appointment_date']));
                  $is_today = date('Y-m-d') == date('Y-m-d', strtotime($booking['appointment_date']));
                  $is_past = strtotime($booking['appointment_date']) < time();
                ?>
                  <tr class="<?= $is_today ? 'bg-blue-50' : '' ?> <?= $is_past ? 'bg-gray-50' : 'hover:bg-gray-50' ?>">
                    <td class="p-3">
                      <div class="font-medium"><?= htmlspecialchars($booking['client_name']) ?></div>
                      <div class="text-sm text-gray-500"><?= htmlspecialchars($booking['email']) ?></div>
                    </td>
                    <td class="p-3"><?= htmlspecialchars($booking['service_type']) ?></td>
                    <td class="p-3"><?= htmlspecialchars($booking['staff_name']) ?></td>
                    <td class="p-3">
                      <div><?= $appointment_date ?></div>
                      <div class="text-sm text-gray-500"><?= $appointment_time ?></div>
                      <?php if ($is_today): ?>
                        <span class="mt-1 inline-block px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs">Today</span>
                      <?php elseif ($is_past): ?>
                        <span class="mt-1 inline-block px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs">Past</span>
                      <?php endif; ?>
                    </td>
                    <td class="p-3">
                      <div class="flex items-center justify-between">
                        <span class="px-2 py-1 rounded-full text-xs <?= $booking['payment_status'] == 'paid' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                          <?= ucfirst($booking['payment_status']) ?>
                        </span>
                        <button onclick="openPaymentModal(<?= $booking['id'] ?>, '<?= $booking['payment_status'] ?>', <?= $booking['payment_amount'] ?>)" 
                                class="text-xs text-blue-500 hover:text-blue-700 ml-2">
                          <i class="fas fa-edit"></i>
                        </button>
                      </div>
                      <?php if ($booking['payment_amount'] > 0): ?>
                        <div class="text-sm mt-1">K<?= number_format($booking['payment_amount'], 2) ?></div>
                      <?php endif; ?>
                    </td>
                    <td class="p-3">
                      <div class="flex space-x-2">
                        <a href="?complete=<?= $booking['id'] ?>" 
                           class="px-3 py-1 bg-green-500 text-white rounded text-sm hover:bg-green-600">
                          Complete
                        </a>
                        <a href="?cancel=<?= $booking['id'] ?>" 
                           onclick="return confirm('Are you sure you want to cancel this booking?')"
                           class="px-3 py-1 bg-red-500 text-white rounded text-sm hover:bg-red-600">
                          Cancel
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php else: ?>
        <div class="bg-white rounded-xl shadow-md p-8 text-center">
          <i class="fas fa-calendar-times text-gray-400 text-5xl mb-4"></i>
          <h3 class="text-xl font-semibold text-gray-700 mb-2">No Confirmed Bookings</h3>
          <p class="text-gray-500">There are no upcoming confirmed bookings.</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Completed Bookings Tab -->
    <div id="tab-content-completed" class="tab-content hidden fade-in">
      <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
        <i class="fas fa-check-circle text-pink-500 mr-3"></i> Recently Completed Bookings
      </h2>
      
      <?php if ($completed_bookings->num_rows > 0): ?>
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="p-3 text-left text-sm font-medium text-gray-700">Client</th>
                  <th class="p-3 text-left text-sm font-medium text-gray-700">Service</th>
                  <th class="p-3 text-left text-sm font-medium text-gray-700">Staff</th>
                  <th class="p-3 text-left text-sm font-medium text-gray-700">Date</th>
                  <th class="p-3 text-left text-sm font-medium text-gray-700">Payment</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <?php while ($booking = $completed_bookings->fetch_assoc()): 
                  $appointment_date = date('M j, Y', strtotime($booking['appointment_date']));
                ?>
                  <tr class="hover:bg-gray-50">
                    <td class="p-3">
                      <div class="font-medium"><?= htmlspecialchars($booking['client_name']) ?></div>
                      <div class="text-sm text-gray-500"><?= htmlspecialchars($booking['email']) ?></div>
                    </td>
                    <td class="p-3"><?= htmlspecialchars($booking['service_type']) ?></td>
                    <td class="p-3"><?= htmlspecialchars($booking['staff_name']) ?></td>
                    <td class="p-3"><?= $appointment_date ?></td>
                    <td class="p-3">
                      <span class="px-2 py-1 rounded-full text-xs <?= $booking['payment_status'] == 'paid' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                        <?= ucfirst($booking['payment_status']) ?>
                      </span>
                      <?php if ($booking['payment_amount'] > 0): ?>
                        <div class="text-sm mt-1">K<?= number_format($booking['payment_amount'], 2) ?></div>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php else: ?>
        <div class="bg-white rounded-xl shadow-md p-8 text-center">
          <i class="fas fa-history text-gray-400 text-5xl mb-4"></i>
          <h3 class="text-xl font-semibold text-gray-700 mb-2">No Completed Bookings</h3>
          <p class="text-gray-500">There are no completed bookings yet.</p>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <script>
    // Payment modal functions
    function openPaymentModal(bookingId, currentStatus, currentAmount) {
      document.getElementById('payment_booking_id').value = bookingId;
      document.getElementById('payment_status').value = currentStatus;
      document.getElementById('payment_amount').value = currentAmount;
      
      togglePaymentAmount();
      
      document.getElementById('paymentModal').classList.remove('hidden');
      setTimeout(() => {
        document.querySelector('.payment-modal').classList.add('open');
      }, 10);
    }
    
    function closePaymentModal() {
      document.querySelector('.payment-modal').classList.remove('open');
      setTimeout(() => {
        document.getElementById('paymentModal').classList.add('hidden');
      }, 300);
    }
    
    function togglePaymentAmount() {
      const status = document.getElementById('payment_status').value;
      const amountField = document.getElementById('amount_field');
      
      if (status === 'paid') {
        amountField.classList.remove('hidden');
      } else {
        amountField.classList.add('hidden');
      }
    }
    
    // Close modal when clicking outside
    document.getElementById('paymentModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closePaymentModal();
      }
    });

    // Tab functionality
    function showTab(tabName) {
      // Hide all tab contents
      document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
      });
      
      // Show selected tab content
      document.getElementById(`tab-content-${tabName}`).classList.remove('hidden');
      
      // Update active tab button
      document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
      });
      document.getElementById(`tab-${tabName}`).classList.add('active');
    }

    // Add animations to page elements
    document.addEventListener('DOMContentLoaded', function() {
      const cards = document.querySelectorAll('.booking-card');
      cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('slide-in');
      });
      
      const rows = document.querySelectorAll('tbody tr');
      rows.forEach((row, index) => {
        row.style.animationDelay = `${index * 0.05}s`;
        row.classList.add('fade-in');
      });
    });
  </script>
</body>
</html>