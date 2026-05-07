<!-- =========================================== 
  Aquí ha trabajado:
    Jaime A. Muñoz, Juan D. Torres
=========================================== -->

<a href="dashboard.php" style="position: fixed; top: 15px; right: 20px; color: rgba(0,0,0,0.5); font-size: 1.1rem; text-decoration: none; z-index: 9999;">
    <i class="fa-solid fa-lock"></i>
</a>

<footer class="footer text-center">
    <div class="container">
        <h5 class="fw-bold mb-2">Move & Go PR</h5>
        <p class="mb-3">Mudanzas &bull; Transporte &bull; Bienes Ra&iacute;ces &bull; Mantenimiento</p>

        <div class="footer-contact">
            <a href="tel:7875438201" class="footer-btn">
                <i class="fa-solid fa-phone me-2"></i>Llamar Ahora
            </a>
            <a href="mailto:moveandgo24@gmail.com" class="footer-btn">
                <i class="fa-solid fa-envelope me-2"></i>Enviar Email
            </a>
            <a href="https://wa.me/17875438201" target="_blank" class="footer-btn whatsapp-footer">
                <i class="fa-brands fa-whatsapp me-2"></i>WhatsApp
            </a>
            <a href="https://www.instagram.com/moveandgopr" target="_blank" class="footer-btn instagram-footer">
                <i class="fa-brands fa-instagram me-2"></i>Instagram
            </a>
            <a href="https://www.facebook.com" target="_blank" class="footer-btn facebook-footer">
                <i class="fa-brands fa-facebook-f me-2"></i>Facebook
            </a>
        </div>

        <small class="d-block mt-4">&copy; <?php echo date("Y"); ?> MoveAndGoPR &mdash; Todos los derechos reservados</small>
    </div>
</footer>

<!-- FLOATING BUTTONS -->
<div class="floating-buttons">
    <a href="https://wa.me/17875438201" class="floating-btn whatsapp" target="_blank">
        <i class="fa-brands fa-whatsapp"></i>
    </a>
    <a href="tel:7875438201" class="floating-btn call">
        <i class="fa-solid fa-phone"></i>
    </a>
    <a href="mailto:moveandgo24@gmail.com" class="floating-btn mail">
        <i class="fa-solid fa-envelope"></i>
    </a>
</div>

<!-- PHOTO MODAL -->
<div id="modalPhoto" class="modal-photo">
    <span class="close-modal">&times;</span>
    <img class="modal-img" id="modalImg" src="" alt="Foto ampliada">
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
<script>
const modal = document.getElementById('modalPhoto');
const modalImg = document.getElementById('modalImg');
const closeModal = document.querySelector('.close-modal');

function openModal(src) {
    if (modal && modalImg) {
        modalImg.src = src;
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModalFn() {
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
        setTimeout(() => { if (modalImg) modalImg.src = ''; }, 300);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.realtor-photo-container').forEach(function (container) {
        container.addEventListener('click', function (e) {
            e.stopPropagation();
            const img = this.querySelector('.realtor-photo');
            if (img && img.src) openModal(img.src);
        });
    });

    document.querySelectorAll('.realtor-photo').forEach(function (photo) {
        photo.addEventListener('click', function (e) {
            e.stopPropagation();
            if (this.src) openModal(this.src);
        });
    });
});

if (closeModal) closeModal.addEventListener('click', closeModalFn);
if (modal) modal.addEventListener('click', function (e) { if (e.target === modal) closeModalFn(); });
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && modal && modal.classList.contains('active')) closeModalFn();
});
if (modalImg) modalImg.addEventListener('click', function (e) { e.stopPropagation(); });
</script>
