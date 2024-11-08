<!-- navbar.php -->
<nav>
    <div class="logo" data-aos="fade-down" data-aos-duration="1000">
        RailwayYatri
    </div>
    <div class="links">
        <a href="index.html" data-aos="fade-down" data-aos-duration="1000" data-aos-delay="100">Home</a>
        <a href="booktickets.php" data-aos="fade-down" data-aos-duration="1000" data-aos-delay="200">Book Tickets</a>
        <a href="findtrains.php" data-aos="fade-down" data-aos-duration="1000" data-aos-delay="300">Find Trains</a>
        <a href="pnrstatus.php" data-aos="fade-down" data-aos-duration="1000" data-aos-delay="400">PNR Status</a>
        <a href="feedback.php" data-aos="fade-down" data-aos-duration="1000" data-aos-delay="500">Feedback</a>
        <a href="contactus.php" data-aos="fade-down" data-aos-duration="1000" data-aos-delay="600">Contact Us</a>
    </div>
    <div class="buttons">
        <button data-aos="fade-down" data-aos-duration="1000" data-aos-delay="700">Login</button>
        <button data-aos="fade-down" data-aos-duration="1000" data-aos-delay="800">Sign up</button>
    </div>
</nav>

<script>
    // Add scroll effect to navbar
    window.addEventListener('scroll', function () {
        const nav = document.querySelector('nav');
        if (window.scrollY > 50) {
            nav.classList.add('scrolled');
        } else {
            nav.classList.remove('scrolled');
        }
    });
</script>