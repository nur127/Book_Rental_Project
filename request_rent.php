<?php  
session_start();  
$conn = new mysqli("localhost", "root", "", "p2p");  

// Check connection  
if ($conn->connect_error) {  
    die("Connection failed: " . $conn->connect_error);  
}  

// Verify user is logged in  
if (!isset($_SESSION['user_id'])) {  
    header("Location: login.php");  
    exit;  
}  

// Validate book ID  
if (!isset($_GET['id'])) {  
    header("Location: index.php");  
    exit;  
}  

$book_id = intval($_GET['id']);  

// Check if book exists  
$result = $conn->query("SELECT * FROM books WHERE id = $book_id");  
$book = $result->fetch_assoc();  

if (!$book) {  
    echo "<p>Book not found.</p>";  
    exit;  
}  

// Handle the request form submission  
if ($_SERVER['REQUEST_METHOD'] == 'POST') {  
    $user_id = $_SESSION['user_id'];  
    $book_id = $book['id'];  

    // Check if there's already a pending request  
    $check_stmt = $conn->prepare("SELECT * FROM rent_requests WHERE user_id = ? AND book_id = ? AND status='pending'");  
    $check_stmt->bind_param("ii", $user_id, $book_id);  
    $check_stmt->execute();  
    $existing = $check_stmt->get_result()->fetch_assoc();  
    $check_stmt->close();  

    if ($existing) {  
        $msg = "You already have a pending request for this book.";  
    } else {  
        // Insert new request  
        $stmt = $conn->prepare("INSERT INTO rent_requests (user_id, book_id) VALUES (?, ?)");  
        $stmt->bind_param("ii", $user_id, $book_id);  
        if ($stmt->execute()) {  
            $msg = "Your request has been submitted successfully!";  
        } else {  
            $msg = "Error submitting request.";  
        }  
        $stmt->close();  
    }  
}  
?>  

<!DOCTYPE html>  
<html lang="en">  
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Request to Rent - <?php echo htmlspecialchars($book['title']); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="navbar.css" rel="stylesheet" />
</head>
<body style="background:#f4f4f9;">
<?php include 'navbar.php'; ?>
<header class="bg-primary text-white text-center py-4 mb-4">
<h1 class="mb-0">Request to Rent: <?php echo htmlspecialchars($book['title']); ?></h1>
</header>
<div class="container" style="max-width:600px; background:#fff; border-radius:8px; padding:20px; margin:30px auto;">

<?php if (isset($msg)) : ?>  
<div class="message"><?php echo htmlspecialchars($msg); ?></div>  
<?php endif; ?>  

<p><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></p>  
<p><strong