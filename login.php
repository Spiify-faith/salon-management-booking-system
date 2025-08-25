<?php
session_start();
require_once '../db.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = isset($_POST["email"]) ? trim($_POST["email"]) : '';
  $password = isset($_POST["password"]) ? $_POST["password"] : '';

  if (empty($email) || empty($password)) {
    $errors[] = "Both email and password are required.";
  } else {
    $stmt = $conn->prepare("SELECT id, name, password_hash FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($admin = $result->fetch_assoc()) {
      if (password_verify($password, $admin["password_hash"])) {
        $_SESSION["admin_id"] = $admin["id"];
        $_SESSION["admin_name"] = $admin["name"];
        header("Location: dashboard.php");
        exit;
      } else {
        $errors[] = "❌ Incorrect password. Try again.";
      }
    } else {
      $errors[] = "❌ No admin found with that email.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Admin Login - SalonSync</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-pink-100 min-h-screen flex items-center justify-center">
  <div class="bg-white p-8 rounded-2xl shadow-md w-full max-w-md">
    <h2 class="text-3xl font-bold text-pink-500 text-center mb-6">Admin Login</h2>

    <?php if (!empty($errors)): ?>
      <div class="bg-red-100 text-red-700 p-3 mb-4 rounded">
        <?php foreach ($errors as $error) echo "<p>$error</p>"; ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input
          type="email"
          id="email"
          name="email"
          required
          class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-400"
        />
      </div>

      <div>
        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
        <input
          type="password"
          id="password"
          name="password"
          required
          class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-400"
        />
      </div>

      <button
        type="submit"
        class="w-full bg-pink-500 text-white py-2 px-4 rounded-xl hover:bg-pink-600 transition duration-300"
      >
        Login
      </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-600">
      Don’t have an account?
      <a href="signup.php" class="text-pink-500 hover:underline">Sign up</a>
    </p>
  </div>
</body>
</html>
