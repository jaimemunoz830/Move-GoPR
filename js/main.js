/* ===========================================
  Aqui ha trabajado:
    Jaime Muñoz
============================================== */

document.querySelectorAll('a.nav-link').forEach(link => {
    link.addEventListener('click', function (e) {
        const targetId = this.getAttribute('href');
        if (targetId.startsWith('#')) {
            e.preventDefault();
            const target = document.querySelector(targetId);
            if (target) {
                window.scrollTo({
                    top: target.offsetTop - 70,
                    behavior: 'smooth'
                });
            }
        }
    });
});