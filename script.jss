// Explore Button

const exploreButton = document.getElementById("explore-btn");
const featuresSection = document.getElementById("features");
const cards = document.querySelectorAll(".feature-card");

exploreButton.addEventListener("click", function () {
    featuresSection.scrollIntoView({
        behavior: "smooth",
        block: "start"
    });
});

const cardObserver = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
        if (entry.isIntersecting) {
            cards.forEach(function (card) {
                card.classList.add("show");
            });

            cardObserver.disconnect();
        }
    });
}, {
    threshold: 0.25
});

cardObserver.observe(featuresSection);


// Navbar Scroll Effect

window.addEventListener("scroll", function () {

    const navbar = document.querySelector(".navbar");

    if (window.scrollY > 50) {

        navbar.classList.add("scrolled");

    }

    else {

        navbar.classList.remove("scrolled");

    }

});

// Mobile Menu

const menuToggle = document.querySelector(".menu-toggle");

const navLinks = document.querySelector(".nav-links");

menuToggle.addEventListener("click", function(){

    navLinks.classList.toggle("active");

});