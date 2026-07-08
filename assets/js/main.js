// assets/js/main.js

document.addEventListener('DOMContentLoaded', function() {
    // Efek scroll untuk navbar
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.08)';
                navbar.style.backgroundColor = 'rgba(255, 255, 255, 0.95)';
            } else {
                navbar.style.boxShadow = '0 4px 30px rgba(0, 0, 0, 0.03)';
                navbar.style.backgroundColor = 'rgba(255, 255, 255, 0.85)';
            }
        });
    }

    // Animasi muncul pelan-pelan (fade-in) untuk kartu-kartu
    const cards = document.querySelectorAll('.card-premium');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100 * index);
    });

    // Toggle hamburger menu (navbar collapse) secara murni Vanilla JS
    const toggler = document.querySelector('.navbar-toggler');
    const collapseMenu = document.querySelector('#navbarNav');
    if (toggler && collapseMenu) {
        toggler.addEventListener('click', function() {
            collapseMenu.classList.toggle('show');
            const isExpanded = collapseMenu.classList.contains('show');
            toggler.setAttribute('aria-expanded', isExpanded);
        });
    }
});

// Helper konfirmasi aksi berbahaya
function konfirmasiHapus(pesan) {
    return confirm(pesan || 'Apakah Anda yakin ingin menghapus data ini?');
}
