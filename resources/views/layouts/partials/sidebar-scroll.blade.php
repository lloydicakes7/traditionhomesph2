<script>
document.addEventListener("DOMContentLoaded", () => {
    const activeLink = document.querySelector('.main-sidebar .nav-link.active');

    if (activeLink) {
        setTimeout(() => {
            activeLink.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }, 100);
    }
});
</script>
