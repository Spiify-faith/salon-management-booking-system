<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION["admin_id"])) {
  header("Location: login.php");
  exit;
}

// Handle add product
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_product"])) {
  $name = trim($_POST["name"]);
  $description = trim($_POST["description"]);
  $price = floatval($_POST["price"]);
  $stock = intval($_POST["stock"]);
  $imagePath = null;

  // ✅ Handle image upload
  if (isset($_FILES["product_image"]) && $_FILES["product_image"]["error"] === 0) {
    $targetDir = "../uploads/";
    $ext = pathinfo($_FILES["product_image"]["name"], PATHINFO_EXTENSION);
    $newName = uniqid() . "." . $ext;
    $targetFile = $targetDir . $newName;

    if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $targetFile)) {
      $imagePath = $newName;
    }
  }

  // ✅ Insert into database
  $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, image_url) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("ssdss", $name, $description, $price, $stock, $imagePath);
  $stmt->execute();
  header("Location: manage_products.php");
  exit;
}

// ✅ Handle delete
if (isset($_GET["delete"])) {
  $id = intval($_GET["delete"]);
  $conn->query("DELETE FROM products WHERE id = $id");
  header("Location: manage_products.php");
  exit;
}

// ✅ Fetch products
$products = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Manage Products - SalonSync Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
  <nav class="bg-white shadow-md py-4 px-6 flex justify-between items-center">
  <h1 class="text-2xl font-bold text-pink-500">SALONSYNC</h1>
  <ul class="flex space-x-4 text-sm font-medium">
    <li><a href="dashboard.php" class="hover:text-pink-600">dashboard</a></li>
    <li><a href="manage_staff.php" class="hover:text-pink-600">staff management</a></li>
    <li><a href="view_bookings.php" class="hover:text-pink-600">bookings management</a></li>
    <li><a href="issue_receipts.php" class="hover:text-pink-600">receipts</a></li>
  </ul>
</nav>


<body class="bg-gray-100 min-h-screen">
  <div class="max-w-6xl mx-auto p-6 mt-10 bg-white shadow-lg rounded-xl">
    <h2 class="text-2xl font-bold text-pink-500 mb-4">Manage Products</h2>

    <!-- ✅ Add Product Form -->
    <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end mb-6">
      <input type="text" name="name" placeholder="Product Name" required class="p-2 border rounded">
      <input type="text" name="description" placeholder="Description (optional)" class="p-2 border rounded">
      <input type="number" step="0.01" name="price" placeholder="Price" required class="p-2 border rounded">
      <input type="number" name="stock" placeholder="Stock" required class="p-2 border rounded">
      <input type="file" name="product_image" accept="image/*" class="p-2 border rounded">
      <button type="submit" name="add_product" class="bg-pink-500 text-white px-4 py-2 rounded hover:bg-pink-600 col-span-full md:col-span-1">Add</button>
    </form>

    <!-- ✅ Product Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full table-auto text-sm border">
        <thead class="bg-pink-100 text-pink-800">
          <tr>
            <th class="p-2">#</th>
            <th class="p-2">Name</th>
            <th class="p-2">Price</th>
            <th class="p-2">Stock</th>
            <th class="p-2">Description</th>
            <th class="p-2">Image</th>
            <th class="p-2">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $products->fetch_assoc()): ?>
          <tr class="border-b">
            <td class="p-2"><?= $row["id"] ?></td>
            <td class="p-2 font-medium"><?= htmlspecialchars($row["name"]) ?></td>
            <td class="p-2">ZMW <?= number_format($row["price"], 2) ?></td>
            <td class="p-2"><?= $row["stock"] ?></td>
            <td class="p-2"><?= htmlspecialchars($row["description"]) ?></td>
            <td class="p-2">
              <?php if (!empty($row["image_url"])): ?>
                <img src="../uploads/<?= htmlspecialchars($row["image_url"]) ?>" alt="Image" class="w-16 h-16 object-cover rounded">
              <?php else: ?>
                <span class="text-gray-400 italic">No image</span>
              <?php endif; ?>
            </td>
            <td class="p-2">
              <a href="?delete=<?= $row["id"] ?>" onclick="return confirm('Delete this product?')" class="text-red-500 hover:underline">Delete</a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
