<?php

/**
 * Header Component
 * 
 * The header contains a book-here paw to the left with a link to the booking site.
 * In the middle is the title and logo, also the dynamical star rating. 
 * To the right is a non-working hamburger-menu. 
 */
require_once __DIR__ . '/../config/paths.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Smooth Mansion</title>
  <link rel="stylesheet" href="<?= BASE_PATH ?>/public/css/header.css" />
  <link rel="stylesheet" href="<?= BASE_PATH ?>/public/css/footer.css" />
  <link rel="stylesheet" href="<?= BASE_PATH ?>/public/css/admin_panel.css" />
  <link rel="stylesheet" href="<?= BASE_PATH ?>/public/css/variables.css" />
  <link rel="stylesheet" href="<?= BASE_PATH ?>/public/css/index.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Advent+Pro:ital,wght@0,100..900;1,100..900&family=Monoton&family=Roboto+Slab:wght@100..900&family=Sacramento&family=Sonsie+One&display=swap" rel="stylesheet" />
</head>

<body>
  <!-- Header Component -->
  <header class="header">
    <section class="content-wrapper">
      <!-- Logo -->
      <figure class="header-logo">
        <a href="<?= BASE_PATH ?>/pages/booking/booking.php" aria-label="Book your stay">
          <img src="<?= BASE_PATH ?>/assets/header-icons/book-here.svg" alt="Book Here" class="paw-svg" />
        </a>
      </figure>

      <!-- Title Section -->
      <article class="title-section">
        <a href="<?= BASE_PATH ?>/index.php">
          <h1>
            Smooth Mansion
            <img src="<?= BASE_PATH ?>/assets/snoop-icon.svg" alt="Icon" />
          </h1>
        </a>
        <p>Smooth vibes, Snoop style.</p>
      </article>

      <!-- Hamburger Menu Button (Not working) -->
      <button class="hamburger-menu" aria-label="Toggle navigation menu" aria-expanded="false">
        <span class="menu-trigger">
          <span class="menu-bar"></span>
          <span class="menu-bar"></span>
          <span class="menu-bar"></span>
        </span>
      </button>
    </section>
  </header>
  <script src="<?= BASE_PATH ?>/public/js/header.js"></script>
</body>

</html>