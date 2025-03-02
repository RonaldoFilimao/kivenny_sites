window.onload = function () {
    const images = document.querySelectorAll('.carousel-image');
    let currentImageIndex = 0;

    function showNextImage() {
        images[currentImageIndex].classList.remove('active');
        currentImageIndex = (currentImageIndex + 1) % images.length;
        images[currentImageIndex].classList.add('active');
    }

    // Muda as imagens a cada 2 segundos
    setInterval(showNextImage, 5000);
};
