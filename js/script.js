/**
 * StudentHub - Core UI Interactivity Script
 * Practical 4: DOM Manipulation, Event Handling, LocalStorage & Interactivity
 * Charotar University of Science and Technology (CHARUSAT) - FTE ITUE203
 */

document.addEventListener("DOMContentLoaded", () => {
    initClockAndGreeting();
    initThemeSwitcher();
    initNotificationBanner();
    initMobileNav();
    initCarousel();
    initModals();
});

/* ==========================================================================
   1. Live Clock, Dynamic Date & Greeting
   ========================================================================== */
function initClockAndGreeting() {
    const clockEl = document.getElementById("clock");
    const dateEl = document.getElementById("date");
    const greetingEl = document.getElementById("greeting");

    function updateTime() {
        const now = new Date();

        // 12-hour formatted time with AM/PM
        if (clockEl) {
            clockEl.textContent = "⏰ " + now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }

        // Formatted date: e.g. Wednesday, 14 October 2026
        if (dateEl) {
            dateEl.textContent = "📅 " + now.toLocaleDateString('en-GB', {
                weekday: 'short',
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }

        // Dynamic greeting based on current hour
        if (greetingEl) {
            const hour = now.getHours();
            let greeting = "Welcome";
            let icon = "👋";

            if (hour < 12) {
                greeting = "Good Morning";
                icon = "🌅";
            } else if (hour < 17) {
                greeting = "Good Afternoon";
                icon = "☀️";
            } else {
                greeting = "Good Evening";
                icon = "🌙";
            }
            greetingEl.textContent = `${icon} ${greeting}, Student!`;
        }
    }

    updateTime();
    setInterval(updateTime, 1000);
}

/* ==========================================================================
   2. Dark/Light Theme Switcher with LocalStorage Persistence (Practical 4)
   ========================================================================== */
function initThemeSwitcher() {
    const themeBtn = document.getElementById("themeBtn");
    const savedTheme = localStorage.getItem("studenthub_theme") || "light";

    if (savedTheme === "dark") {
        document.body.classList.add("dark-theme");
        updateThemeButtonText(true);
    }

    if (themeBtn) {
        themeBtn.addEventListener("click", () => {
            const isDark = document.body.classList.toggle("dark-theme");
            localStorage.setItem("studenthub_theme", isDark ? "dark" : "light");
            updateThemeButtonText(isDark);
            showToast(isDark ? "Switched to Dark Mode" : "Switched to Light Mode", "info");
        });
    }

    function updateThemeButtonText(isDark) {
        if (!themeBtn) return;
        themeBtn.innerHTML = isDark ? "☀️ Light Theme" : "🌙 Dark Theme";
        themeBtn.setAttribute("aria-label", isDark ? "Switch to Light Theme" : "Switch to Dark Theme");
    }
}

/* ==========================================================================
   3. Notification Banner with Dismissal
   ========================================================================== */
function initNotificationBanner() {
    const banner = document.getElementById("notification");
    const closeBtn = document.getElementById("closeNotification");

    if (!banner || !closeBtn) return;

    // Check if dismissed in this browser session
    if (sessionStorage.getItem("studenthub_announcement_dismissed") === "true") {
        banner.classList.add("hidden");
    }

    closeBtn.addEventListener("click", () => {
        banner.style.opacity = "0";
        setTimeout(() => {
            banner.classList.add("hidden");
            sessionStorage.setItem("studenthub_announcement_dismissed", "true");
        }, 200);
    });
}

/* ==========================================================================
   4. Mobile Navigation Hamburger Menu
   ========================================================================== */
function initMobileNav() {
    const hamburger = document.getElementById("hamburgerBtn");
    const navLinks = document.getElementById("navLinks");

    if (!hamburger || !navLinks) return;

    hamburger.addEventListener("click", () => {
        const isOpen = navLinks.classList.toggle("open");
        hamburger.setAttribute("aria-expanded", isOpen);
        hamburger.innerHTML = isOpen ? "✕" : "☰";
    });
}

/* ==========================================================================
   5. Interactive Featured Events Carousel / Slider (Practical 4)
   ========================================================================== */
function initCarousel() {
    const track = document.getElementById("carouselTrack");
    const prevBtn = document.getElementById("carouselPrev");
    const nextBtn = document.getElementById("carouselNext");
    const indicatorsContainer = document.getElementById("carouselIndicators");

    if (!track) return;

    const slides = Array.from(track.children);
    if (slides.length === 0) return;

    let currentIndex = 0;
    let autoPlayInterval = null;

    // Build indicator dots dynamically
    if (indicatorsContainer) {
        indicatorsContainer.innerHTML = "";
        slides.forEach((_, idx) => {
            const dot = document.createElement("button");
            dot.className = `carousel-dot ${idx === 0 ? "active" : ""}`;
            dot.setAttribute("aria-label", `Slide ${idx + 1}`);
            dot.addEventListener("click", () => goToSlide(idx));
            indicatorsContainer.appendChild(dot);
        });
    }

    function updateCarousel() {
        track.style.transform = `translateX(-${currentIndex * 100}%)`;
        if (indicatorsContainer) {
            const dots = indicatorsContainer.querySelectorAll(".carousel-dot");
            dots.forEach((dot, idx) => {
                dot.classList.toggle("active", idx === currentIndex);
            });
        }
    }

    function goToSlide(index) {
        currentIndex = (index + slides.length) % slides.length;
        updateCarousel();
    }

    function nextSlide() {
        goToSlide(currentIndex + 1);
    }

    function prevSlide() {
        goToSlide(currentIndex - 1);
    }

    if (nextBtn) nextBtn.addEventListener("click", () => { nextSlide(); resetAutoplay(); });
    if (prevBtn) prevBtn.addEventListener("click", () => { prevSlide(); resetAutoplay(); });

    function startAutoplay() {
        autoPlayInterval = setInterval(nextSlide, 5000);
    }

    function stopAutoplay() {
        if (autoPlayInterval) clearInterval(autoPlayInterval);
    }

    function resetAutoplay() {
        stopAutoplay();
        startAutoplay();
    }

    track.parentElement.addEventListener("mouseenter", stopAutoplay);
    track.parentElement.addEventListener("mouseleave", startAutoplay);

    startAutoplay();
}

/* ==========================================================================
   6. Modal Popups Helper (Practical 4 & 11)
   ========================================================================== */
function initModals() {
    document.querySelectorAll("[data-close-modal]").forEach(btn => {
        btn.addEventListener("click", () => {
            const modal = btn.closest(".modal-backdrop");
            if (modal) modal.classList.remove("show");
        });
    });

    // Close on background backdrop click
    document.querySelectorAll(".modal-backdrop").forEach(backdrop => {
        backdrop.addEventListener("click", (e) => {
            if (e.target === backdrop) {
                backdrop.classList.remove("show");
            }
        });
    });

    // Close on Escape key press
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            document.querySelectorAll(".modal-backdrop.show").forEach(modal => {
                modal.classList.remove("show");
            });
        }
    });
}

window.openModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add("show");
    }
};

window.closeModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove("show");
    }
};

/* ==========================================================================
   7. Toast Notification Utility
   ========================================================================== */
window.showToast = function(message, type = "info") {
    let container = document.getElementById("toastContainer");
    if (!container) {
        container = document.createElement("div");
        container.id = "toastContainer";
        container.className = "toast-container";
        document.body.appendChild(container);
    }

    const toast = document.createElement("div");
    toast.className = `toast toast-${type}`;
    const icon = type === "success" ? "✅" : type === "danger" ? "❌" : type === "warning" ? "⚠️" : "ℹ️";
    toast.innerHTML = `<span>${icon}</span> <div>${message}</div>`;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = "0";
        setTimeout(() => toast.remove(), 300);
    }, 3500);
};