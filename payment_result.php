<?php
$ok  = isset($_GET['ok']) ? (int)$_GET['ok'] : 0;
$msg = $_GET['msg'] ?? '';
$appt= isset($_GET['appt']) ? (int)$_GET['appt'] : 0;
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Payment Result</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-pink-50 min-h-screen flex items-center justify-center">
  <div class="bg-white p-8 rounded-2xl shadow w-full max-w-md text-center">
    <?php if ($ok): ?>
      <h2 class="text-2xl font-bold text-green-600 mb-2">Payment Successful</h2>
      <p class="mb-4">Your payment was received. Your booking is now <b>pending admin confirmation</b>.</p>
      <a class="inline-block bg-pink-500 text-white px-4 py-2 rounded-xl" href="/salonsync/index.html">Back to Home</a>
    <?php else: ?>
      <h2 class="text-2xl font-bold text-red-600 mb-2">Payment Failed</h2>
      <p class="mb-2">Reason: <?php echo htmlspecialchars($msg); ?></p>
      <a class="inline-block bg-gray-700 text-white px-4 py-2 rounded-xl" href="/salonsync/booking_form.php?id=<?php echo $appt; ?>">Try Again</a>
    <?php endif; ?>
  </div>
</body>
</html>
