<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Smooth Mansion</title>
  <link rel="stylesheet" href="/public/css/header.css" />
  <link rel="stylesheet" href="/public/css/footer.css" />

  <link rel="stylesheet" href="/public/css/variables.css" />
  <link rel="stylesheet" href="/public/css/index.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Advent+Pro:ital,wght@0,100..900;1,100..900&family=Monoton&family=Roboto+Slab:wght@100..900&family=Sacramento&family=Sonsie+One&display=swap"
    rel="stylesheet" />
</head>

<body>
  <!-- Header Component -->
  <header class="header">
    <section class="content-wrapper">
      <!-- Logo -->
      <figure class="header-logo">
        <a href="/pages/booking/booking.php" aria-label="Book your stay">
          <img
            src="/assets/svg/book-here.svg"
            alt="Book Here"
            class="paw-svg" />
        </a>
      </figure>

      <!-- Uppdaterad Title Section -->
      <article class="title-section">
        <a href="/../index.php">
          <h1>
            Smooth Mansion
            <img src="/../../assets/images/snoop-icon.svg" alt="Icon" />
          </h1>
        </a>
        <p>Smooth vibes, Snoop style.</p>
        <div class="hotel-stars">

          <?php
          require_once __DIR__ . '/../config/app.php';
          try {
            $db = getDb();
            $stmt = $db->query("SELECT setting_value FROM admin_settings WHERE setting_name = 'hotel_stars'");
            $starCount = (int)($stmt->fetchColumn() ?? 3);

            for ($i = 0; $i < $starCount; $i++):
          ?>
              <img src="/assets/svg/star.svg" alt="Hotel Star Rating" class="star-rating">
            <?php
            endfor;
          } catch (PDOException $e) {
            for ($i = 0; $i < 3; $i++):
            ?>
              <img src="/assets/svg/star.svg" alt="Hotel Star Rating" class="star-rating">
          <?php
            endfor;
          }
          ?>
        </div>

      </article>

      <!-- Hamburger Menu Button -->
      <button
        class="hamburger-menu"
        aria-label="Toggle navigation menu"
        aria-expanded="false">
        <span class="menu-trigger">
          <span class="menu-bar"></span>
          <span class="menu-bar"></span>
          <span class="menu-bar"></span>
        </span>
      </button>
    </section>

    <!-- Navigation Menu -->
  </header>

  <!-- Include header-specific JavaScript -->
  <script src="/public/js/header.js"></script>
</body>

</html>