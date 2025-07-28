<?php
session_start();
$conn = new mysqli("localhost", "root", "", "p2p");

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$book_id = intval($_GET['id']);
$result = $conn->query("SELECT * FROM books WHERE id = $book_id");
$book = $result->fetch_assoc();
if (!$book) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Book not found.</div></div>";
    exit;
}
// Fetch owner info
$owner = null;
if (isset($book['user_id'])) {
    $owner_res = $conn->query("SELECT name, username, email FROM users WHERE id = " . intval($book['user_id']));
    if ($owner_res && $owner_res->num_rows > 0) {
        $owner = $owner_res->fetch_assoc();
    }
}
// Fetch category name
$category_name = '';
if (isset($book['category_id']) && !empty($book['category_id'])) {
    $cat_res = $conn->query("SELECT name FROM categories WHERE id = " . intval($book['category_id']));
    if ($cat_res && $cat_res->num_rows > 0) {
        $cat_row = $cat_res->fetch_assoc();
        $category_name = $cat_row['name'];
    }
}
if (empty($category_name) && !empty($book['category'])) {
    $category_name = $book['category'];
}
// Determine if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($book['title']); ?> - Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="navbar.css" rel="stylesheet" />
  <style>
    body {
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      background-color: #f4f4f9;
      margin: 0;
      padding: 0;
    }
    .book-card {
      max-width: 800px;
      margin: 32px auto 0 auto;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.10);
      background: #fff;
      padding: 0;
      overflow: hidden;
    }
    .book-cover {
      width: 100%;
      max-width: 320px;
      height: 420px;
      object-fit: cover;
      border-radius: 0.5rem;
      background: #e9ecef;
      display: block;
      margin: 0 auto;
    }
    .badge-status {
      font-size: 1em;
      padding: 0.5em 1em;
      border-radius: 1em;
    }
    .owner-info {
      font-size: 0.98em;
      color: #555;
      margin-top: 8px;
    }
    .book-title {
      font-size: 2rem;
      font-weight: 700;
      color: #222;
      margin-bottom: 0.5rem;
    }
    .book-author {
      font-size: 1.1rem;
      color: #007bff;
      margin-bottom: 0.5rem;
    }
    .book-desc {
      font-size: 1.08rem;
      color: #444;
      margin-bottom: 1rem;
    }
    .rent-cost {
      font-size: 1.1rem;
      color: #28a745;
      font-weight: 600;
    }
    .request-btn {
      margin-top: 1.2rem;
      width: 100%;
      font-size: 1.1rem;
    }
    @media (max-width: 767px) {
      .book-card { max-width: 98vw; }
      .book-cover { height: 220px; }
    }
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="book-card card shadow-lg">
  <div class="row g-0">
    <div class="col-md-5 d-flex align-items-center justify-content-center p-3">
      <?php if ($book['cover_image']) : ?>
        <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" alt="Cover" class="book-cover shadow-sm" />
      <?php else: ?>
        <div class="d-flex align-items-center justify-content-center book-cover" style="background:#e9ecef;">
          <span class="text-secondary">No Image</span>
        </div>
      <?php endif; ?>
    </div>
    <div class="col-md-7 p-4">
      <div class="d-flex align-items-center mb-2">
        <span class="badge badge-status bg-<?php echo ($book['status']==='available'||$book['status']==='lend') ? 'success' : 'secondary'; ?> me-2">
          <?php echo ($book['status']==='available'||$book['status']==='lend') ? 'Available' : 'Not Available'; ?>
        </span>
        <span class="rent-cost">$<?php echo number_format($book['rent_cost'], 2); ?> / day</span>
      </div>
      <div class="book-title mb-1"><?php echo htmlspecialchars($book['title']); ?></div>
      <div class="book-author mb-2">by <?php echo htmlspecialchars($book['author']); ?></div>
      <div class="mb-2"><span class="fw-semibold">Category:</span> <?php echo htmlspecialchars($category_name); ?></div>
      <?php if (!empty($book['address'])): ?>
        <div class="mb-2"><span class="fw-semibold">Address:</span> <?php echo htmlspecialchars($book['address']); ?></div>
      <?php endif; ?>
      <!-- Description moved below the card -->
      <?php if ($owner): ?>
        <div class="owner-info mb-2">
          <span class="fw-semibold">Owner:</span> <?php echo htmlspecialchars($owner['name']); ?>
          <span class="text-muted">(@<?php echo htmlspecialchars($owner['username']); ?>)</span>
          <?php if ($is_logged_in): ?>
            <br><span class="fw-semibold">Contact:</span> <a href="mailto:<?php echo htmlspecialchars($owner['email']); ?>">Email Owner</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
      <!-- Days input and total calculation section -->
      <?php if ($book['status']==='available'||$book['status']==='lend'): ?>
        <form method="POST" action="request_rent.php" class="mt-3" id="rentForm">
          <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
          <div class="mb-3 row align-items-center">
            <label for="daysInput" class="col-sm-5 col-form-label">How many days to borrow?</label>
            <div class="col-sm-4">
              <input type="number" min="1" max="30" value="1" name="days" id="daysInput" class="form-control" required />
            </div>
          </div>
          <div class="mb-3">
            <div class="alert alert-info p-2" id="totalMoneyDiv" style="font-size:1.1em;">
              Total: $<span id="totalMoneySpan"><?php echo number_format($book['rent_cost'],2); ?></span>
            </div>
          </div>
          <?php if ($is_logged_in): ?>
            <button type="submit" class="btn btn-primary request-btn">Request to Rent</button>
          <?php else: ?>
            <a href="login.php" class="btn btn-outline-primary request-btn">Log in to Request</a>
          <?php endif; ?>
        </form>
      <?php else: ?>
        <button class="btn btn-secondary request-btn mt-3" disabled>Not Available for Rent</button>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Book Description below the card, separated by a horizontal line -->
<div class="container" style="max-width:800px;">
  <hr class="my-4">
  <div class="book-desc mb-4">
    <span class="fw-semibold">Description:</span><br>
    <?php
      // Remove all extra internal whitespace (multiple spaces, tabs, newlines)
      $desc_clean = preg_replace('/\s+/u', ' ', trim($book['description']));
      echo nl2br(htmlspecialchars($desc_clean));
    ?>
  </div>
</div>
<!-- JS for total calculation -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var daysInput = document.getElementById('daysInput');
    var totalSpan = document.getElementById('totalMoneySpan');
    var pricePerDay = <?php echo floatval($book['rent_cost']); ?>;
    function updateTotal() {
      var days = parseInt(daysInput.value) || 1;
      if (days < 1) days = 1;
      if (days > 30) days = 30;
      var total = days * pricePerDay;
      totalSpan.textContent = total.toFixed(2);
    }
    daysInput.addEventListener('input', updateTotal);
    updateTotal();
  });
</script>
<!-- Optionally, add related books carousel here in the future -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>