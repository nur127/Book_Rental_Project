<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$conn = new mysqli("localhost", "root", "", "p2p");
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search !== '') {
    $stmt = $conn->prepare("SELECT * FROM books WHERE status = 'lend' AND (title LIKE ? OR author LIKE ?)");
    $like = "%" . $search . "%";
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    $result = $conn->query("SELECT * FROM books WHERE status = 'lend'");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Book Rental Platform | Peer-to-Peer Book Sharing</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: #f8f9fa;
    }
    .main-header {
      background: linear-gradient(90deg, #007bff 0%, #6610f2 100%);
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .main-header h1 {
      font-size: 2.8rem;
      font-weight: 700;
      letter-spacing: 1px;
      margin-bottom: 0.5rem;
    }
    .main-header p {
      font-size: 1.2rem;
      font-weight: 400;
      margin-bottom: 0;
    }
    .card {
      border: none;
      border-radius: 1rem;
      transition: box-shadow 0.2s;
    }
    .card:hover {
      box-shadow: 0 8px 24px rgba(0,0,0,0.12);
      transform: translateY(-2px);
    }
    .card-title {
      font-size: 1.25rem;
      font-weight: 600;
      color: #343a40;
    }
    .btn-outline-primary {
      border-radius: 0.5rem;
    }
    .no-image {
      color: #6c757d;
      font-size: 1.1rem;
      font-weight: 500;
    }
  </style>
</head>

<body>
<!-- Navigation Bar -->
<?php include 'navbar.php'; ?>

<header class="main-header text-white text-center py-5 mb-4">
  <div class="container">
    <h1>Peer-to-Peer Book Rental Platform</h1>
    <p>Discover, rent, and share books with your community</p>
  </div>
</header>

<div class="container mb-5">
  <div class="d-flex justify-content-end mb-4">
    <a href="submit_book.php" class="btn btn-success px-4 py-2 fw-bold shadow-sm">Lend a Book</a>
  </div>
  <div class="row g-4">
    <?php while ($book = $result->fetch_assoc()) : ?>
    <div class="col-lg-4 col-md-6">
      <div class="card h-100 shadow-sm">
        <?php if ($book['cover_image']) : ?>
        <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" class="card-img-top" alt="Cover" style="height:250px; object-fit:cover; border-top-left-radius:1rem; border-top-right-radius:1rem;">
        <?php else: ?>
        <div class="d-flex align-items-center justify-content-center" style="height:250px; background:#e9ecef; border-top-left-radius:1rem; border-top-right-radius:1rem;">
          <span class="no-image">No Image Available</span>
        </div>
        <?php endif; ?>
        <div class="card-body d-flex flex-column">
          <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
          <p class="mb-1"><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
          <p class="mb-1"><strong>Rent:</strong> $<?php echo number_format($book['rent_cost'], 2); ?></p>
          <p class="mb-3"><strong>Status:</strong> <?php echo htmlspecialchars($book['status']); ?></p>
          <a href="book_details.php?id=<?php echo $book['id']; ?>" class="btn btn-outline-primary mt-auto fw-semibold">View Details</a>
        </div>
      </div>
    </div>
    <?php endwhile; ?>
    <?php if ($result->num_rows == 0): ?>
      <div class="col-12 text-center text-muted">No books found.</div>
    <?php endif; ?>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>