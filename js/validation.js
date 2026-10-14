/**
 * StudentHub - Form Validation, Password Strength & Canvas CAPTCHA
 * Practical 5: Form Validation with Regex, Error Handling, Strength Meter & Canvas
 * Charotar University of Science and Technology (CHARUSAT) - FTE ITUE203
 */

document.addEventListener("DOMContentLoaded", () => {
    initRegistrationValidation();
    initCanvasCaptcha();
    initPasswordStrengthMeter();
});

/* ==========================================================================
   1. Canvas CAPTCHA Generator (Practical 5 Advanced)
   ========================================================================== */
let generatedCaptcha = "";

function initCanvasCaptcha() {
    const canvas = document.getElementById("captchaCanvas");
    const refreshBtn = document.getElementById("refreshCaptchaBtn");

    if (!canvas) return;

    function generateCode() {
        const chars = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789";
        let code = "";
        for (let i = 0; i < 6; i++) {
            code += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return code;
    }

    function renderCaptcha() {
        const ctx = canvas.getContext("2d");
        generatedCaptcha = generateCode();

        // Clear canvas
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Background noise
        ctx.fillStyle = "#f1f5f9";
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // Draw decorative lines
        for (let i = 0; i < 5; i++) {
            ctx.beginPath();
            ctx.moveTo(Math.random() * canvas.width, Math.random() * canvas.height);
            ctx.lineTo(Math.random() * canvas.width, Math.random() * canvas.height);
            ctx.strokeStyle = `rgba(13, 110, 253, ${0.15 + Math.random() * 0.25})`;
            ctx.lineWidth = 1 + Math.random();
            ctx.stroke();
        }

        // Draw random dots
        for (let i = 0; i < 35; i++) {
            ctx.beginPath();
            ctx.arc(Math.random() * canvas.width, Math.random() * canvas.height, 1, 0, Math.PI * 2);
            ctx.fillStyle = "#94a3b8";
            ctx.fill();
        }

        // Draw characters with random rotation & colors
        const colors = ["#0d6efd", "#0f172a", "#16a34a", "#dc2626", "#7c3aed"];
        for (let i = 0; i < generatedCaptcha.length; i++) {
            ctx.save();
            const char = generatedCaptcha[i];
            const x = 20 + i * 22;
            const y = 30 + (Math.random() * 6 - 3);
            const angle = (Math.random() - 0.5) * 0.35;

            ctx.translate(x, y);
            ctx.rotate(angle);
            ctx.font = "bold 24px 'Courier New', monospace";
            ctx.fillStyle = colors[i % colors.length];
            ctx.fillText(char, 0, 0);
            ctx.restore();
        }
    }

    renderCaptcha();

    if (refreshBtn) {
        refreshBtn.addEventListener("click", (e) => {
            e.preventDefault();
            renderCaptcha();
            const captchaInput = document.getElementById("captchaInput");
            if (captchaInput) {
                captchaInput.value = "";
                captchaInput.classList.remove("is-valid", "is-invalid");
            }
        });
    }
}

/* ==========================================================================
   2. Password Strength Meter (Practical 5)
   ========================================================================== */
function initPasswordStrengthMeter() {
    const passwordInput = document.getElementById("password");
    const bar = document.getElementById("strengthBar");
    const text = document.getElementById("strengthText");

    if (!passwordInput || !bar || !text) return;

    passwordInput.addEventListener("input", () => {
        const val = passwordInput.value;
        const evaluation = checkPasswordStrength(val);

        bar.style.width = evaluation.percentage + "%";
        bar.style.backgroundColor = evaluation.color;
        text.textContent = evaluation.message;
        text.style.color = evaluation.color;
    });
}

function checkPasswordStrength(password) {
    if (!password) {
        return { percentage: 0, color: "#cbd5e1", message: "Strength: None" };
    }

    let score = 0;
    if (password.length >= 8) score++;
    if (password.length >= 12) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[a-z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;

    if (score <= 2) {
        return { percentage: 25, color: "#dc3545", message: "Strength: Weak (Needs 8+ chars, mix of case & digits)" };
    } else if (score <= 4) {
        return { percentage: 60, color: "#f59e0b", message: "Strength: Moderate (Add symbols & uppercase)" };
    } else if (score === 5) {
        return { percentage: 85, color: "#0d6efd", message: "Strength: Good (Strong security)" };
    } else {
        return { percentage: 100, color: "#198754", message: "Strength: Excellent (Robust defense)" };
    }
}

/* ==========================================================================
   3. Real-Time Form Validation (Practical 5)
   ========================================================================== */
function initRegistrationValidation() {
    const form = document.getElementById("registrationForm");
    if (!form) return;

    const fields = {
        fullName: {
            el: document.getElementById("fullName"),
            validator: (val) => /^[a-zA-Z\s]{3,50}$/.test(val.trim()),
            errorMsg: "Name must contain only letters (at least 3 characters)."
        },
        email: {
            el: document.getElementById("email"),
            validator: (val) => /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(val.trim()),
            errorMsg: "Please enter a valid email address (e.g., student@charusat.edu.in)."
        },
        mobile: {
            el: document.getElementById("mobile"),
            validator: (val) => /^[6-9]\d{9}$/.test(val.trim()),
            errorMsg: "Enter a valid 10-digit mobile number starting with 6, 7, 8, or 9."
        },
        course: {
            el: document.getElementById("course"),
            validator: (val) => val !== "",
            errorMsg: "Please select your academic program/course."
        },
        year: {
            el: document.getElementById("year"),
            validator: (val) => val !== "",
            errorMsg: "Please select your current year of study."
        },
        password: {
            el: document.getElementById("password"),
            validator: (val) => /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/.test(val),
            errorMsg: "Password must be 8+ chars with uppercase, lowercase, number & symbol."
        },
        confirmPassword: {
            el: document.getElementById("confirmPassword"),
            validator: (val) => {
                const p = document.getElementById("password").value;
                return val.length > 0 && val === p;
            },
            errorMsg: "Passwords do not match."
        },
        captchaInput: {
            el: document.getElementById("captchaInput"),
            validator: (val) => val.trim().toLowerCase() === generatedCaptcha.toLowerCase(),
            errorMsg: "Incorrect CAPTCHA characters. Please try again."
        }
    };

    // Attach real-time input and blur validation
    Object.keys(fields).forEach((key) => {
        const item = fields[key];
        if (!item.el) return;

        ["input", "change", "blur"].forEach((evt) => {
            item.el.addEventListener(evt, () => validateField(item));
        });
    });

    function validateField(item) {
        if (!item.el) return true;
        const val = item.el.value;
        const isValid = item.validator(val);
        const feedbackEl = item.el.parentElement.querySelector(".invalid-feedback");

        if (isValid) {
            item.el.classList.remove("is-invalid");
            item.el.classList.add("is-valid");
            if (feedbackEl) feedbackEl.style.display = "none";
            return true;
        } else {
            item.el.classList.remove("is-valid");
            item.el.classList.add("is-invalid");
            if (feedbackEl) {
                feedbackEl.textContent = item.errorMsg;
                feedbackEl.style.display = "block";
            }
            return false;
        }
    }

    // Gender check
    function validateGender() {
        const genderRadios = document.querySelectorAll('input[name="gender"]');
        const errorEl = document.getElementById("genderFeedback");
        let checked = false;
        genderRadios.forEach(r => { if (r.checked) checked = true; });

        if (errorEl) {
            errorEl.style.display = checked ? "none" : "block";
            errorEl.textContent = "Please select your gender.";
        }
        return checked;
    }

    // Terms check
    function validateTerms() {
        const terms = document.getElementById("terms");
        const errorEl = document.getElementById("termsFeedback");
        if (!terms) return true;

        const checked = terms.checked;
        if (errorEl) {
            errorEl.style.display = checked ? "none" : "block";
            errorEl.textContent = "You must accept the terms and code of conduct to proceed.";
        }
        return checked;
    }

    // Form submission validation
    form.addEventListener("submit", (e) => {
        let isFormValid = true;

        Object.keys(fields).forEach((key) => {
            const valid = validateField(fields[key]);
            if (!valid) isFormValid = false;
        });

        if (!validateGender()) isFormValid = false;
        if (!validateTerms()) isFormValid = false;

        if (!isFormValid) {
            e.preventDefault();
            if (window.showToast) {
                window.showToast("Please correct highlighted errors in the form.", "danger");
            }
            // Scroll to the first invalid field
            const firstInvalid = form.querySelector(".is-invalid");
            if (firstInvalid) {
                firstInvalid.focus();
            }
        } else {
            // Check if backend script is targeted
            if (form.getAttribute("action") === "#") {
                e.preventDefault();
                if (window.showToast) {
                    window.showToast("Registration validated successfully! Submitting details...", "success");
                }
                setTimeout(() => {
                    alert("Registration successful! Redirecting to login...");
                    window.location.href = "login.html";
                }, 1000);
            }
        }
    });
}
