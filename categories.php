<?php
session_start();
$conn = new mysqli("localhost", "root", "", "p2p");

// Fetch all categories
$cat_result = $conn->query("SELECT DISTINCT category FROM books WHERE status = 'lend' ORDER BY category ASC");
$categories = [];
while ($row = $cat_result->fetch_assoc()) {
    $categories[] = $row['category'];
}

// Determine selected category
$selected_category = isset($_GET['category']) ? $_GET['category'] : '';

// Fetch books (all or by category)
if ($selected_category && in_array($selected_category, $categories)) {
    $stmt = $conn->prepare("SELECT * FROM books WHERE status = 'lend' AND category = ?");
    $stmt->bind_param("s", $selected_category);
    $stmt->execute();
    $books = $stmt->get_result();
    $stmt->close();
} else {
    $books = $conn->query("SELECT * FROM books WHERE status = 'lend'");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Book Categories - Book Rental Platform</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="navbar.css" rel="stylesheet" />
  <style>
    body { background-color: #f8f9fa; }
    .category-menu {
      background: #fff;
      border-radius: 0.5rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
      padding: 1rem 1.5rem;
      margin-bottom: 2rem;
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      align-items: center;
    }
    .category-link.btn {
      border-radius: 2rem;
      padding: 0.4rem 1.2rem;
      font-size: 1rem;
      transition: background 0.2s, color 0.2s;
    }
    .category-link.active,
    .category-link.btn.active {
      color: #fff !important;
      background: #007bff !important;
      border-color: #007bff !important;
      font-weight: 600;
      box-shadow: 0 2px 8px rgba(0,123,255,0.08);
    }
    .category-link.btn:hover:not(.active) {
      background: #e9ecef;
      color: #007bff !important;
      border-color: #007bff !important;
    }
    .no-image {
      color: #6c757d;
      font-size: 1.1rem;
      font-weight: 500;
    }
    .main-header {
      background: linear-gradient(90deg, #007bff 0%, #6610f2 100%);
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .main-header h1 {
      font-size: 2.5rem;
      font-weight: 700;
      letter-spacing: 1px;
      margin-bottom: 0.5rem;
    }
    .main-header p {
      font-size: 1.2rem;
      font-weight: 400;
      margin-bottom: 0;
    }
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<header class="main-header text-white text-center py-5 mb-4">
  <div class="container">
    <h1>Book Categories</h1>
    <p>Browse books by category or see all available books for lending</p>
  </div>
</header>
<div class="container mb-5">
  <div class="mb-4 d-flex align-items-center gap-3">
    <div class="dropdown">
      <button class="btn btn-primary dropdown-toggle" type="button" id="categoryDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <?php echo $selected_category ? htmlspecialchars($selected_category) : 'All Categories'; ?>
      </button>
      <ul class="dropdown-menu" aria-labelledby="categoryDropdown">
        <li><a class="dropdown-item" href="categories.php">All</a></li>
        <?php foreach ($categories as $cat): ?>
          <li><a class="dropdown-item<?php echo ($selected_category==$cat) ? ' active' : ''; ?>" href="categories.php?category=<?php echo urlencode($cat); ?>"><?php echo htmlspecialchars($cat); ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <span class="text-muted">Select a category to filter books</span>
  </div>
  <div class="row g-4">
    <?php while ($book = $books->fetch_assoc()): ?>
    <div class="col-lg-4 col-md-6">
      <div class="card h-100 shadow-sm">
        <?php if ($book['cover_image']): ?>
        <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" class="card-img-top" alt="Cover" style="height:250px; object-fit:cover; border-top-left-radius:1rem; border-top-right-radius:1rem;">
        <?php else: ?>
        <div class="d-flex align-items-center justify-content-center" style="height:250px; background:#e9ecef; border-top-left-radius:1rem; border-top-right-radius:1rem;">
          <span class="no-image">No Image Available</span>
        </div>
        <?php endif; ?>
        <div class="card-body d-flex flex-column">
          <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
          <p class="mb-1"><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
          <p class="mb-1"><strong>Category:</strong> <?php echo htmlspecialchars($book['category']); ?></p>
          <p class="mb-1"><strong>Rent:</strong> $<?php echo number_format($book['rent_cost'], 2); ?></p>
          <p class="mb-3"><strong>Status:</strong> <?php echo htmlspecialchars($book['status']); ?></p>
          <a href="book_details.php?id=<?php echo $book['id']; ?>" class="btn btn-outline-primary mt-auto fw-semibold">View Details</a>
        </div>
      </div>
    </div>
    <?php endwhile; ?>
    <?php if ($books->num_rows == 0): ?>
      <div class="col-12 text-center text-muted">No books found in this category.</div>
    <?php endif; ?>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
