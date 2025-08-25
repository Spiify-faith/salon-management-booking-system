<?php
require_once 'db.php';
$products = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Products - SalonSync</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-pink-50 text-gray-800">

<!-- Navbar -->
<nav class="bg-white shadow-md py-4 px-6 flex justify-between items-center">
  <h1 class="text-2xl font-bold text-pink-500">SALONSYNC</h1>
  <ul class="flex space-x-4 text-sm font-medium">
    <li><a href="index.html" class="hover:text-pink-600">Home</a></li>
    <li><a href="booking.html" class="hover:text-pink-600">Booking</a></li>
    <li><a href="products.php" class="text-pink-600 underline">Products</a></li>
    <li><a href="services.html" class="hover:text-pink-600">Services</a></li>
    <li><a href="terms.html" class="hover:text-pink-600">Terms</a></li>
    <li><a href="login.html" class="hover:text-pink-600">Login</a></li>
  </ul>
</nav>

<!-- Products Section -->
<section class="px-6 py-12 max-w-6xl mx-auto">
  <h2 class="text-3xl font-bold text-center text-pink-500 mb-8"> Look Good. Feel Good. Add to cart.</h2>
  <div class="grid gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
    <?php while ($row = $products->fetch_assoc()): ?>
      <div class="bg-white rounded-2xl shadow-md overflow-hidden">
        <?php if (!empty($row["image_url"])): ?>
          <img src="uploads/<?= htmlspecialchars($row["image_url"]) ?>" alt="<?= htmlspecialchars($row["name"]) ?>" class="w-full h-48 object-cover">
        <?php else: ?>
          <div class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-400 italic">No image</div>
        <?php endif; ?>

        <div class="p-4">
          <h3 class="text-lg font-semibold text-pink-600"><?= htmlspecialchars($row["name"]) ?></h3>
          <p class="text-sm text-gray-600 mb-2">K<?= number_format($row["price"], 2) ?></p>
          <?php if ($row["stock"] > 0): ?>
            <span class="inline-block bg-green-100 text-green-600 text-xs px-2 py-1 rounded">In Stock</span>

           <form method="post" action="add_to_cart.php" class="mt-4 space-y-2">
      <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
      <input type="hidden" name="name" value="<?= htmlspecialchars($row['name']) ?>">
      <input type="hidden" name="price" value="<?= $row['price'] ?>">
      <input type="hidden" name="stock" value="<?= $row['stock'] ?>">

      <label class="block text-sm text-gray-600">Quantity:</label>
      <input type="number" name="quantity" min="1" max="<?= $row['stock'] ?>" value="1"
        class="w-20 p-1 border border-gray-300 rounded" required>

      <button type="submit"
        class="block bg-pink-500 text-white px-4 py-2 rounded-xl hover:bg-pink-600">
        Add to Cart
      </button>
    </form>

          <?php else: ?>
            <span class="inline-block bg-red-100 text-red-600 text-xs px-2 py-1 rounded">Sold Out</span>
          <?php endif; ?>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</section>

</body>
</html>
