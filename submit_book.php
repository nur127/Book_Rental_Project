<?php
session_start();
$conn = new mysqli("localhost", "root", "", "p2p");

// Check if user is admin
// if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
//     header("Location: index.php");
//     exit;
// }

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $address = $_POST['address'];
    $rent_cost = $_POST['rent_cost'];
    $cover_image = '';
    $status = 'lend';
    if (empty($category)) {
        echo "<p style='color:red;text-align:center;'>Please select a category.</p>";
    } else {
        // Handle file upload if exists
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == UPLOAD_ERR_OK) {
            $target_dir = "uploads/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $filename = basename($_FILES["cover_image"]["name"]);
            $target_file = $target_dir . time() . "_" . $filename;
            if (move_uploaded_file($_FILES["cover_image"]["tmp_name"], $target_file)) {
                $cover_image = $target_file;
            }
        }
        // Insert into database (with explicit column order)
        $stmt = $conn->prepare("INSERT INTO books (title, author, category, description, address, rent_cost, cover_image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssdss", $title, $author, $category, $description, $address, $rent_cost, $cover_image, $status);
        if ($stmt->execute()) {
            echo "<p style='color:green;text-align:center;'>Book submitted successfully!</p>";
        } else {
            echo "<p style='color:red;text-align:center;'>Error: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
</head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Submit a Book - Book Rental Platform</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="navbar.css" rel="stylesheet" />
<style>
  body {
    background-color: #f4f4f9;
  }
  .custom-container {
    max-width: 600px;
    margin: 30px auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  }
</style>
</head>
<body>
<?php include 'navbar.php'; ?>
<header class="bg-primary text-white text-center py-4 mb-4">
  <h1 class="mb-0">Submit a Book for Rent</h1>
</header>
<div class="custom-container">
  <form method="POST" enctype="multipart/form-data" action="submit_book.php">
    <div class="mb-3">
      <label class="form-label">Title:</label>
      <input type="text" name="title" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Author:</label>
      <input type="text" name="author" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Category:</label>
      <select name="category" class="form-control" required>
        <option value="">Select a category</option>
        <option value="Science">Science</option>
        <option value="Fiction">Fiction</option>
        <option value="History">History</option>
        <option value="Biography">Biography</option>
        <option value="Technology">Technology</option>
        <option value="Children">Children</option>
        <option value="Comics">Comics</option>
        <option value="Other">Other</option>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Description:</label>
      <textarea name="description" rows="4" class="form-control" required></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Lender Address:</label>
      <input type="text" name="address" class="form-control" required placeholder="Enter your address (will show only in book details)">
    </div>
    <div class="mb-3">
      <label class="form-label">Rent Cost ($):</label>
      <input type="number" step="0.01" name="rent_cost" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Cover Image:</label>
      <input type="file" name="cover_image" accept="image/*" class="form-control">
    </div>
    <button type="submit" class="btn btn-primary w-100">Submit</button>
  </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>