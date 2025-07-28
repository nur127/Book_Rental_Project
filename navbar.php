<?php
// Common Bootstrap Navbar for all pages
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm fixed-top" style="backdrop-filter: blur(8px);">
  <div class="container">
    <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="index.php">
      <!-- Placeholder Logo SVG -->
      <span style="display:inline-block;width:32px;height:32px;">
        <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect width="32" height="32" rx="8" fill="#fff"/>
          <path d="M10 22V10h8a4 4 0 1 1 0 8h-8" stroke="#007bff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </span>
      BookRental
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link<?php if(basename($_SERVER['PHP_SELF'])=='index.php') echo ' active'; ?>" aria-current="page" href="index.php">Home</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle<?php if(basename($_SERVER['PHP_SELF'])=='categories.php') echo ' active'; ?>" href="categories.php" id="navbarCategoryDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Book Categories
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarCategoryDropdown">
            <li><a class="dropdown-item" href="categories.php">All</a></li>
            <?php
            // Dynamically fetch categories for the dropdown
            $conn_nav = new mysqli("localhost", "root", "", "p2p");
            $cat_result_nav = $conn_nav->query("SELECT DISTINCT category FROM books WHERE status = 'lend' ORDER BY category ASC");
            while ($row_nav = $cat_result_nav->fetch_assoc()) {
              $cat = $row_nav['category'];
              echo '<li><a class="dropdown-item" href="categories.php?category=' . urlencode($cat) . '">' . htmlspecialchars($cat) . '</a></li>';
            }
            $conn_nav->close();
            ?>
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link<?php if(basename($_SERVER['PHP_SELF'])=='contact.php') echo ' active'; ?>" href="contact.php">Contact Us</a>
        </li>
      </ul>
      <form class="d-flex me-3" role="search" method="GET" action="search_results.php">
        <input class="form-control me-2" type="search" name="search" placeholder="Search books..." aria-label="Search">
        <button class="btn btn-light" type="submit">Search</button>
      </form>
      <?php if (isset($_SESSION['user_id'])): ?>
        <div class="dropdown">
          <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="me-2">
              <svg width="32" height="32" fill="#fff" viewBox="0 0 16 16"><circle cx="8" cy="5.5" r="3.5"/><path d="M2 14s1-1.5 6-1.5S14 14 14 14v1H2v-1z"/></svg>
            </span>
            <span class="fw-semibold d-none d-md-inline">Profile</span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="profileDropdown" style="min-width:220px;">
            <li class="px-3 py-2">
              <div class="fw-bold mb-1"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></div>
              <div class="small text-muted mb-1">ID: <?php echo htmlspecialchars($_SESSION['user_id']); ?></div>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger fw-semibold" href="logout.php">Log out</a></li>
          </ul>
        </div>
      <?php else: ?>
        <a href="login.php" class="btn btn-outline-light fw-semibold">Log in</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
