document.addEventListener("DOMContentLoaded", () => {
  const header = document.querySelector(".header");
  const menuButton = document.querySelector(".hamburger-menu");
  const navMenu = document.querySelector(".nav-menu");
  const scrollProgress = document.querySelector(".scroll-progress-bar");

  // Hamburger menu toggle
  menuButton.addEventListener("click", () => {
    const isExpanded = menuButton.getAttribute("aria-expanded") === "true";
    menuButton.setAttribute("aria-expanded", !isExpanded);
    navMenu.classList.toggle("active");
  });

  // Scroll handling
  window.addEventListener("scroll", () => {
    // Header background opacity
    if (window.scrollY > 50) {
      header.classList.add("scrolled");
    } else {
      header.classList.remove("scrolled");
    }

    // Update scroll progress bar
    const scrollPercent =
      (window.scrollY /
        (document.documentElement.scrollHeight - window.innerHeight)) *
      100;
    scrollProgress.style.width = `${scrollPercent}%`;
  });
});
