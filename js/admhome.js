document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".card, .table-container").forEach((el, i) => {
        el.style.opacity = 0;
        el.style.transform = "translateY(20px)";
        setTimeout(() => {
            el.style.transition = "all 0.6s ease";
            el.style.opacity = 1;
            el.style.transform = "translateY(0)";
        }, 200 * i);
    });
});

document.querySelector(".toggle-btn").addEventListener("click", () => {
    document.querySelector(".sidebar").classList.toggle("collapsed");
});

document.addEventListener('click', function(e) {
  const toggle = document.getElementById('menu-toggle-desktop');
  const menu = document.querySelector('.menu-content-desktop');
  if (!e.target.closest('.hamburger-menu-desktop')) {
    toggle.checked = false;
  }
});
