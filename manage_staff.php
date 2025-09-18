<?php
session_start();
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../db.php";

// Handle form submissions
$message = "";
$message_type = "";

// Add new staff member
if (isset($_POST['add_staff'])) {
    $name = trim($_POST['name']);
    $role = trim($_POST['role']);
    $available = isset($_POST['available']) ? 1 : 0;
    $available_days = isset($_POST['available_days']) ? implode(',', $_POST['available_days']) : '';
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    
    if (!empty($name) && !empty($role)) {
        $stmt = $conn->prepare("INSERT INTO staff (name, role, available, available_days, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisss", $name, $role, $available, $available_days, $start_time, $end_time);
        
        if ($stmt->execute()) {
            $message = "Staff member added successfully!";
            $message_type = "success";
        } else {
            $message = "Error adding staff member: " . $conn->error;
            $message_type = "error";
        }
        $stmt->close();
    } else {
        $message = "Please fill in all required fields!";
        $message_type = "error";
    }
}

// Update staff member
if (isset($_POST['update_staff'])) {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $role = trim($_POST['role']);
    $available = isset($_POST['available']) ? 1 : 0;
    $available_days = isset($_POST['available_days']) ? implode(',', $_POST['available_days']) : '';
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    
    if (!empty($name) && !empty($role)) {
        $stmt = $conn->prepare("UPDATE staff SET name=?, role=?, available=?, available_days=?, start_time=?, end_time=? WHERE id=?");
        $stmt->bind_param("ssisssi", $name, $role, $available, $available_days, $start_time, $end_time, $id);
        
        if ($stmt->execute()) {
            $message = "Staff member updated successfully!";
            $message_type = "success";
        } else {
            $message = "Error updating staff member: " . $conn->error;
            $message_type = "error";
        }
        $stmt->close();
    } else {
        $message = "Please fill in all required fields!";
        $message_type = "error";
    }
}

// Delete staff member
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Check if staff member has appointments before deleting
    $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE staff_id = ?");
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $row = $result->fetch_assoc();
    $check_stmt->close();
    
    if ($row['count'] > 0) {
        $message = "Cannot delete staff member with existing appointments!";
        $message_type = "error";
    } else {
        $stmt = $conn->prepare("DELETE FROM staff WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $message = "Staff member deleted successfully!";
            $message_type = "success";
        } else {
            $message = "Error deleting staff member: " . $conn->error;
            $message_type = "error";
        }
        $stmt->close();
    }
}

// Fetch all staff members
$staff_result = $conn->query("SELECT * FROM staff ORDER BY name");
$days_of_week = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Staff - SalonSync</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
  <header class="bg-pink-500 text-white py-4 shadow-md">
    <div class="max-w-6xl mx-auto px-4 flex justify-between items-center">
      <div class="flex items-center">
        <a href="dashboard.php" class="mr-4 text-white">
          <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-xl font-bold">SalonSync Admin - Staff Management</h1>
      </div>
      <div>
        <span class="mr-4">Welcome, <?= htmlspecialchars($_SESSION["admin_name"]) ?></span>
        <a href="logout.php" class="bg-white text-pink-500 px-3 py-1 rounded hover:bg-pink-100 transition">Logout</a>
      </div>
    </div>
  </header>

  <main class="max-w-6xl mx-auto px-4 py-8">
    <?php if (!empty($message)): ?>
      <div class="mb-6 p-4 rounded-md <?= $message_type == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
        <?= $message ?>
      </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
      <div class="bg-pink-500 text-white px-6 py-4">
        <h2 class="text-xl font-semibold">Add New Staff Member</h2>
      </div>
      <div class="p-6">
        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
            <input type="text" name="name" required class="w-full px-4 py-2 border rounded-md focus:ring-pink-500 focus:border-pink-500">
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
            <input type="text" name="role" required class="w-full px-4 py-2 border rounded-md focus:ring-pink-500 focus:border-pink-500">
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Available Days</label>
            <div class="grid grid-cols-2 gap-2">
              <?php foreach ($days_of_week as $day): ?>
                <label class="flex items-center">
                  <input type="checkbox" name="available_days[]" value="<?= $day ?>" class="mr-2">
                  <span class="text-sm"><?= $day ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Working Hours</label>
            <div class="flex items-center space-x-2">
              <input type="time" name="start_time" class="px-4 py-2 border rounded-md focus:ring-pink-500 focus:border-pink-500">
              <span class="text-gray-500">to</span>
              <input type="time" name="end_time" class="px-4 py-2 border rounded-md focus:ring-pink-500 focus:border-pink-500">
            </div>
          </div>
          
          <div class="flex items-center">
            <label class="flex items-center">
              <input type="checkbox" name="available" checked class="mr-2">
              <span class="text-sm font-medium text-gray-700">Currently Available</span>
            </label>
          </div>
          
          <div class="md:col-span-2">
            <button type="submit" name="add_staff" class="bg-pink-500 text-white px-6 py-2 rounded-md hover:bg-pink-600 transition">
              Add Staff Member
            </button>
          </div>
        </form>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-md overflow-hidden">
      <div class="bg-pink-500 text-white px-6 py-4">
        <h2 class="text-xl font-semibold">Current Staff Members</h2>
      </div>
      <div class="p-6">
        <?php if ($staff_result->num_rows > 0): ?>
          <div class="overflow-x-auto">
            <table class="w-full border-collapse">
              <thead>
                <tr class="bg-gray-50">
                  <th class="border-b p-3 text-left text-sm font-medium text-gray-700">Name</th>
                  <th class="border-b p-3 text-left text-sm font-medium text-gray-700">Role</th>
                  <th class="border-b p-3 text-left text-sm font-medium text-gray-700">Availability</th>
                  <th class="border-b p-3 text-left text-sm font-medium text-gray-700">Working Days</th>
                  <th class="border-b p-3 text-left text-sm font-medium text-gray-700">Hours</th>
                  <th class="border-b p-3 text-left text-sm font-medium text-gray-700">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($staff = $staff_result->fetch_assoc()): 
                  $available_days = $staff['available_days'] ? explode(',', $staff['available_days']) : [];
                  $start_time = $staff['start_time'] ? date('g:i A', strtotime($staff['start_time'])) : '';
                  $end_time = $staff['end_time'] ? date('g:i A', strtotime($staff['end_time'])) : '';
                ?>
                  <tr class="hover:bg-gray-50">
                    <td class="border-b p-3"><?= htmlspecialchars($staff['name']) ?></td>
                    <td class="border-b p-3"><?= htmlspecialchars($staff['role']) ?></td>
                    <td class="border-b p-3">
                      <span class="px-2 py-1 rounded-full text-xs <?= $staff['available'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                        <?= $staff['available'] ? 'Available' : 'Not Available' ?>
                      </span>
                    </td>
                    <td class="border-b p-3 text-sm">
                      <?php if (!empty($available_days)): ?>
                        <?= implode(', ', $available_days) ?>
                      <?php else: ?>
                        <span class="text-gray-400">Not set</span>
                      <?php endif; ?>
                    </td>
                    <td class="border-b p-3 text-sm">
                      <?php if ($start_time && $end_time): ?>
                        <?= $start_time ?> - <?= $end_time ?>
                      <?php else: ?>
                        <span class="text-gray-400">Not set</span>
                      <?php endif; ?>
                    </td>
                    <td class="border-b p-3">
                      <div class="flex space-x-2">
                        <button onclick="openEditModal(<?= htmlspecialchars(json_encode($staff), ENT_QUOTES, 'UTF-8') ?>)" 
                                class="text-blue-500 hover:text-blue-700">
                          <i class="fas fa-edit"></i>
                        </button>
                        <a href="?delete=<?= $staff['id'] ?>" 
                           onclick="return confirm('Are you sure you want to delete this staff member?')"
                           class="text-red-500 hover:text-red-700">
                          <i class="fas fa-trash"></i>
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <p class="text-center text-gray-500 py-6">No staff members found. Add your first staff member above.</p>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <!-- Edit Staff Modal -->
  <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md mx-4">
      <div class="bg-pink-500 text-white px-6 py-4 rounded-t-xl">
        <h2 class="text-xl font-semibold">Edit Staff Member</h2>
      </div>
      <div class="p-6">
        <form method="POST" id="editForm">
          <input type="hidden" name="id" id="edit_id">
          <input type="hidden" name="update_staff" value="1">
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
            <input type="text" name="name" id="edit_name" required class="w-full px-4 py-2 border rounded-md focus:ring-pink-500 focus:border-pink-500">
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
            <input type="text" name="role" id="edit_role" required class="w-full px-4 py-2 border rounded-md focus:ring-pink-500 focus:border-pink-500">
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Available Days</label>
            <div class="grid grid-cols-2 gap-2" id="days_container">
              <?php foreach ($days_of_week as $day): ?>
                <label class="flex items-center">
                  <input type="checkbox" name="available_days[]" value="<?= $day ?>" class="mr-2 day-checkbox">
                  <span class="text-sm"><?= $day ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Working Hours</label>
            <div class="flex items-center space-x-2">
              <input type="time" name="start_time" id="edit_start_time" class="px-4 py-2 border rounded-md focus:ring-pink-500 focus:border-pink-500">
              <span class="text-gray-500">to</span>
              <input type="time" name="end_time" id="edit_end_time" class="px-4 py-2 border rounded-md focus:ring-pink-500 focus:border-pink-500">
            </div>
          </div>
          
          <div class="mb-6">
            <label class="flex items-center">
              <input type="checkbox" name="available" id="edit_available" class="mr-2">
              <span class="text-sm font-medium text-gray-700">Currently Available</span>
            </label>
          </div>
          
          <div class="flex justify-end space-x-3">
            <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-100">
              Cancel
            </button>
            <button type="submit" class="bg-pink-500 text-white px-4 py-2 rounded-md hover:bg-pink-600">
              Update Staff
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    function openEditModal(staff) {
      // Populate form fields
      document.getElementById('edit_id').value = staff.id;
      document.getElementById('edit_name').value = staff.name;
      document.getElementById('edit_role').value = staff.role;
      document.getElementById('edit_available').checked = staff.available == 1;
      document.getElementById('edit_start_time').value = staff.start_time;
      document.getElementById('edit_end_time').value = staff.end_time;
      
      // Clear all checkboxes first
      document.querySelectorAll('.day-checkbox').forEach(checkbox => {
        checkbox.checked = false;
      });
      
      // Check the appropriate days
      if (staff.available_days) {
        const days = staff.available_days.split(',');
        days.forEach(day => {
          const checkbox = document.querySelector(`.day-checkbox[value="${day.trim()}"]`);
          if (checkbox) checkbox.checked = true;
        });
      }
      
      // Show modal
      document.getElementById('editModal').classList.remove('hidden');
    }
    
    function closeEditModal() {
      document.getElementById('editModal').classList.add('hidden');
    }
    
    // Close modal when clicking outside
    document.getElementById('editModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeEditModal();
      }
    });
  </script>
</body>
</html>