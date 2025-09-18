<?php
session_start();
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../db.php";

// Handle theme preference
if (isset($_POST['set_theme'])) {
    $theme = $_POST['theme'];
    $_SESSION['theme'] = $theme;
    
    // Optional: Save to database for persistence across sessions
    $admin_id = $_SESSION["admin_id"];
    $stmt = $conn->prepare("UPDATE admins SET theme_preference = ? WHERE id = ?");
    $stmt->bind_param("si", $theme, $admin_id);
    $stmt->execute();
    $stmt->close();
    
    $message = "Theme preference updated successfully!";
    $message_type = "success";
}

// Get current theme or set default
$current_theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';

// Check if user has a saved preference in database
$admin_id = $_SESSION["admin_id"];
$stmt = $conn->prepare("SELECT theme_preference FROM admins WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    if (!empty($admin['theme_preference'])) {
        $current_theme = $admin['theme_preference'];
        $_SESSION['theme'] = $current_theme;
    }
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en" class="<?= $current_theme === 'dark' ? 'dark' : '' ?>">
<head>
  <meta charset="UTF-8">
  <title>Settings - SalonSync</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script>
    tailwind.config = {
      darkMode: 'class',
    }
  </script>
  <style>
    .theme-transition * {
      transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
    }
    .theme-option {
      transition: all 0.3s ease;
    }
    .theme-option:hover {
      transform: translateY(-2px);
    }
    .theme-option.selected {
      box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.5);
    }
  </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen theme-transition">
  <header class="bg-pink-500 dark:bg-pink-600 text-white py-4 shadow-md">
    <div class="max-w-6xl mx-auto px-4 flex justify-between items-center">
      <div class="flex items-center">
        <a href="dashboard.php" class="mr-4 text-white">
          <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-xl font-bold">SalonSync Admin - Settings</h1>
      </div>
      <div>
        <span class="mr-4">Welcome, <?= htmlspecialchars($_SESSION["admin_name"]) ?></span>
        <a href="logout.php" class="bg-white text-pink-500 dark:bg-gray-800 dark:text-pink-300 px-3 py-1 rounded hover:bg-pink-100 dark:hover:bg-gray-700 transition">
          Logout
        </a>
      </div>
    </div>
  </header>

  <main class="max-w-6xl mx-auto px-4 py-8">
    <?php if (!empty($message)): ?>
      <div class="mb-6 p-4 rounded-md <?= $message_type == 'success' ? 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300' : 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300' ?>">
        <?= $message ?>
      </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <!-- Appearance Settings -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-6 flex items-center">
          <i class="fas fa-palette text-pink-500 mr-3"></i> Appearance
        </h2>
        
        <form method="POST">
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Theme Preference</label>
            <div class="grid grid-cols-2 gap-4">
              <label class="theme-option cursor-pointer">
                <input type="radio" name="theme" value="light" class="hidden" <?= $current_theme === 'light' ? 'checked' : '' ?>>
                <div class="border-2 rounded-lg p-4 text-center <?= $current_theme === 'light' ? 'selected border-pink-500 bg-pink-50 dark:bg-pink-900/20' : 'border-gray-200 dark:border-gray-700' ?>">
                  <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-yellow-100 rounded-full mx-auto mb-2 flex items-center justify-center">
                    <i class="fas fa-sun text-yellow-500"></i>
                  </div>
                  <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Light Mode</span>
                </div>
              </label>
              
              <label class="theme-option cursor-pointer">
                <input type="radio" name="theme" value="dark" class="hidden" <?= $current_theme === 'dark' ? 'checked' : '' ?>>
                <div class="border-2 rounded-lg p-4 text-center <?= $current_theme === 'dark' ? 'selected border-pink-500 bg-pink-50 dark:bg-pink-900/20' : 'border-gray-200 dark:border-gray-700' ?>">
                  <div class="w-12 h-12 bg-gradient-to-br from-purple-900 to-blue-900 rounded-full mx-auto mb-2 flex items-center justify-center">
                    <i class="fas fa-moon text-blue-300"></i>
                  </div>
                  <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Dark Mode</span>
                </div>
              </label>
            </div>
          </div>
          
          <button type="submit" name="set_theme" class="w-full bg-pink-500 text-white py-2 rounded-md hover:bg-pink-600 transition">
            Save Appearance Settings
          </button>
        </form>
      </div>

      <!-- Account Settings -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-6 flex items-center">
          <i class="fas fa-user-cog text-pink-500 mr-3"></i> Account
        </h2>
        
        <div class="space-y-4">
          <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div>
              <div class="font-medium text-gray-800 dark:text-white">Admin Name</div>
              <div class="text-sm text-gray-600 dark:text-gray-400"><?= htmlspecialchars($_SESSION["admin_name"]) ?></div>
            </div>
            <span class="px-2 py-1 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded text-xs">Admin</span>
          </div>
          
          <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div class="font-medium text-gray-800 dark:text-white mb-2">Session Information</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
              <div>Logged in since: <?= date('M j, Y g:i A', $_SESSION['login_time'] ?? time()) ?></div>
              <div>Current theme: <?= ucfirst($current_theme) ?> Mode</div>
            </div>
          </div>
          
          <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="logout.php" class="w-full bg-red-500 text-white py-2 rounded-md hover:bg-red-600 transition flex items-center justify-center">
              <i class="fas fa-sign-out-alt mr-2"></i> Logout
            </a>
          </div>
        </div>
      </div>

      <!-- System Information -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 md:col-span-2">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-6 flex items-center">
          <i class="fas fa-info-circle text-pink-500 mr-3"></i> System Information
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div class="flex items-center mb-2">
              <i class="fas fa-database text-blue-500 mr-2"></i>
              <span class="font-medium text-gray-800 dark:text-white">Database</span>
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
              <div>MySQL Version: <?= $conn->get_server_info() ?></div>
              <div>Host: <?= explode(':', $conn->host_info)[0] ?></div>
            </div>
          </div>
          
          <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div class="flex items-center mb-2">
              <i class="fas fa-server text-green-500 mr-2"></i>
              <span class="font-medium text-gray-800 dark:text-white">PHP</span>
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
              <div>Version: <?= phpversion() ?></div>
              <div>Server: <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></div>
            </div>
          </div>
          
          <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div class="flex items-center mb-2">
              <i class="fas fa-calendar-alt text-purple-500 mr-2"></i>
              <span class="font-medium text-gray-800 dark:text-white">Appointments</span>
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
              <?php
              $appointment_stats = $conn->query("
                SELECT 
                  COUNT(*) as total,
                  SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                  SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
                FROM appointments
              ")->fetch_assoc();
              ?>
              <div>Total: <?= $appointment_stats['total'] ?></div>
              <div>Confirmed: <?= $appointment_stats['confirmed'] ?></div>
              <div>Pending: <?= $appointment_stats['pending'] ?></div>
            </div>
          </div>
          
          <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div class="flex items-center mb-2">
              <i class="fas fa-users text-yellow-500 mr-2"></i>
              <span class="font-medium text-gray-800 dark:text-white">Users</span>
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
              <?php
              $user_stats = $conn->query("
                SELECT 
                  COUNT(*) as total_users,
                  (SELECT COUNT(*) FROM staff) as total_staff
              ")->fetch_assoc();
              ?>
              <div>Clients: <?= $user_stats['total_users'] ?></div>
              <div>Staff: <?= $user_stats['total_staff'] ?></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script>
    // Add instant theme switching without page reload
    document.addEventListener('DOMContentLoaded', function() {
      const themeRadios = document.querySelectorAll('input[name="theme"]');
      
      themeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
          if (this.checked) {
            const theme = this.value;
            document.documentElement.classList.toggle('dark', theme === 'dark');
            
            // Update selected styling
            document.querySelectorAll('.theme-option').forEach(option => {
              option.classList.remove('selected');
            });
            this.closest('.theme-option').classList.add('selected');
          }
        });
      });
      
      // Add animation to cards
      const cards = document.querySelectorAll('.bg-white');
      cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('animate-fadeIn');
      });
    });
  </script>
</body>
</html>