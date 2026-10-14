/**
 * StudentHub - External JSON Data Renderer via Fetch API
 * Practical 6: Fetch API, Search, Filter, Sort, Pagination & LocalStorage Caching
 * Charotar University of Science and Technology (CHARUSAT) - FTE ITUE203
 */

document.addEventListener("DOMContentLoaded", () => {
    // Detect page type and render corresponding dataset
    if (document.getElementById("eventsContainer")) {
        initEventsModule();
    }
    if (document.getElementById("studentsTableBody")) {
        initStudentsModule();
    }
    if (document.getElementById("faqAccordion")) {
        initFaqModule();
    }
    if (document.getElementById("noticesContainer")) {
        initNoticesModule();
    }
});

/* ==========================================================================
   1. Events Directory Module (Practical 6)
   ========================================================================== */
function initEventsModule() {
    const container = document.getElementById("eventsContainer");
    const searchInput = document.getElementById("eventSearch");
    const categoryFilter = document.getElementById("eventCategory");
    const sortSelect = document.getElementById("eventSort");
    const paginationContainer = document.getElementById("eventsPagination");
    const resultCountEl = document.getElementById("eventResultCount");

    let allEvents = [];
    let filteredEvents = [];
    let currentPage = 1;
    const itemsPerPage = 6;

    // Load data with Fetch API and LocalStorage fallback
    async function loadEvents() {
        container.innerHTML = `<div style="grid-column: 1/-1; text-align: center; padding: 40px;">
            <div style="font-size: 2rem;">⏳</div>
            <p>Loading campus events via Fetch API...</p>
        </div>`;

        try {
            const response = await fetch("../data/events.json");
            if (!response.ok) throw new Error(`HTTP Error ${response.status}`);
            allEvents = await response.json();
            // Cache in localStorage for offline resiliency
            localStorage.setItem("studenthub_cached_events", JSON.stringify(allEvents));
        } catch (err) {
            console.warn("Fetch failed, reading from localStorage cache:", err);
            const cached = localStorage.getItem("studenthub_cached_events");
            if (cached) {
                allEvents = JSON.parse(cached);
                if (window.showToast) window.showToast("Loaded events from offline cache", "warning");
            } else {
                container.innerHTML = `<div style="grid-column: 1/-1; text-align: center; color: var(--danger); padding: 40px;">
                    ❌ Unable to load events dataset. Please check network/JSON file path.
                </div>`;
                return;
            }
        }

        applyFilters();
    }

    function applyFilters() {
        const query = searchInput ? searchInput.value.toLowerCase().trim() : "";
        const selectedCat = categoryFilter ? categoryFilter.value : "All";
        const selectedSort = sortSelect ? sortSelect.value : "date-asc";

        // Array filter method
        filteredEvents = allEvents.filter((ev) => {
            const matchesQuery = ev.title.toLowerCase().includes(query) ||
                                 ev.description.toLowerCase().includes(query) ||
                                 ev.speaker.toLowerCase().includes(query) ||
                                 ev.venue.toLowerCase().includes(query);
            const matchesCategory = (selectedCat === "All" || ev.category === selectedCat);
            return matchesQuery && matchesCategory;
        });

        // Array sort method
        filteredEvents.sort((a, b) => {
            if (selectedSort === "date-asc") return new Date(a.date) - new Date(b.date);
            if (selectedSort === "date-desc") return new Date(b.date) - new Date(a.date);
            if (selectedSort === "name-asc") return a.title.localeCompare(b.title);
            if (selectedSort === "name-desc") return b.title.localeCompare(a.title);
            return 0;
        });

        currentPage = 1;
        renderEvents();
    }

    function renderEvents() {
        if (resultCountEl) {
            resultCountEl.textContent = `Showing ${filteredEvents.length} events`;
        }

        if (filteredEvents.length === 0) {
            container.innerHTML = `<div style="grid-column: 1/-1; text-align: center; padding: 50px; background: var(--surface); border-radius: var(--radius-md); border: 1px dashed var(--border);">
                <div style="font-size: 2.5rem; margin-bottom: 12px;">🔍</div>
                <h3>No Matching Events Found</h3>
                <p style="color: var(--text-secondary);">Try clearing your search query or changing category filters.</p>
            </div>`;
            if (paginationContainer) paginationContainer.innerHTML = "";
            return;
        }

        // Array slice method for pagination
        const startIndex = (currentPage - 1) * itemsPerPage;
        const paginatedItems = filteredEvents.slice(startIndex, startIndex + itemsPerPage);

        // Array map method to generate cards HTML
        container.innerHTML = paginatedItems.map((ev) => {
            const isClosed = ev.status === "Closed" || ev.available_seats === 0;
            const statusBadge = isClosed 
                ? `<span class="badge badge-danger">Closed</span>` 
                : `<span class="badge badge-success">Open (${ev.available_seats} seats left)</span>`;

            return `
            <article class="card">
                <div class="card-media">
                    <img src="${ev.poster || '../images/event-web.svg'}" alt="${ev.title}">
                    <span class="card-badge">${ev.category}</span>
                </div>
                <div class="card-body">
                    <h3>${ev.title}</h3>
                    <div class="card-meta">
                        <span>📅 ${ev.date}</span>
                        <span>⏰ ${ev.time}</span>
                        <span>🏛️ ${ev.venue}</span>
                    </div>
                    <p>${ev.description}</p>
                    <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 12px;">
                        <strong>Speaker:</strong> ${ev.speaker}
                    </div>
                    <div class="card-footer">
                        <div>${statusBadge}</div>
                        <button class="btn btn-primary btn-sm" ${isClosed ? 'disabled' : ''} onclick="openEventRegisterModal(${ev.id}, '${escapeQuotes(ev.title)}')">
                            ${isClosed ? 'Full' : 'Register Now'}
                        </button>
                    </div>
                </div>
            </article>
            `;
        }).join("");

        renderPagination(filteredEvents.length, itemsPerPage, currentPage, (page) => {
            currentPage = page;
            renderEvents();
            window.scrollTo({ top: container.offsetTop - 100, behavior: 'smooth' });
        }, paginationContainer);
    }

    // Debounced search input
    if (searchInput) {
        let timer;
        searchInput.addEventListener("input", () => {
            clearTimeout(timer);
            timer = setTimeout(applyFilters, 250);
        });
    }

    if (categoryFilter) categoryFilter.addEventListener("change", applyFilters);
    if (sortSelect) sortSelect.addEventListener("change", applyFilters);

    loadEvents();
}

/* ==========================================================================
   2. Students Directory Module (Practical 6 & 11)
   ========================================================================== */
function initStudentsModule() {
    const tableBody = document.getElementById("studentsTableBody");
    const searchInput = document.getElementById("studentSearch");
    const departmentFilter = document.getElementById("studentDepartment");
    const yearFilter = document.getElementById("studentYear");
    const paginationContainer = document.getElementById("studentsPagination");
    const countEl = document.getElementById("studentCountDisplay");

    let allStudents = [];
    let filteredStudents = [];
    let currentPage = 1;
    const itemsPerPage = 8;

    async function loadStudents() {
        tableBody.innerHTML = `<tr><td colspan="7" style="text-align: center; padding: 30px;">Loading student records...</td></tr>`;

        try {
            const res = await fetch("../data/students.json");
            if (!res.ok) throw new Error("HTTP " + res.status);
            allStudents = await res.json();
            localStorage.setItem("studenthub_cached_students", JSON.stringify(allStudents));
        } catch (e) {
            console.warn("Loading cached students:", e);
            const cached = localStorage.getItem("studenthub_cached_students");
            if (cached) allStudents = JSON.parse(cached);
        }

        applyStudentFilters();
    }

    function applyStudentFilters() {
        const query = searchInput ? searchInput.value.toLowerCase().trim() : "";
        const dept = departmentFilter ? departmentFilter.value : "All";
        const yr = yearFilter ? yearFilter.value : "All";

        filteredStudents = allStudents.filter((s) => {
            const matchesQuery = s.name.toLowerCase().includes(query) ||
                                 s.enrollment.toLowerCase().includes(query) ||
                                 s.email.toLowerCase().includes(query);
            const matchesDept = (dept === "All" || s.department.includes(dept));
            const matchesYr = (yr === "All" || s.year === yr);
            return matchesQuery && matchesDept && matchesYr;
        });

        currentPage = 1;
        renderStudentsTable();
    }

    function renderStudentsTable() {
        if (countEl) countEl.textContent = `Total Students: ${filteredStudents.length}`;

        if (filteredStudents.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="7" style="text-align: center; padding: 30px; color: var(--text-muted);">No student records found.</td></tr>`;
            if (paginationContainer) paginationContainer.innerHTML = "";
            return;
        }

        const startIndex = (currentPage - 1) * itemsPerPage;
        const pageItems = filteredStudents.slice(startIndex, startIndex + itemsPerPage);

        tableBody.innerHTML = pageItems.map((s) => `
            <tr>
                <td><strong>${s.enrollment}</strong></td>
                <td>
                    <div style="font-weight: 600; color: var(--text-primary);">${s.name}</div>
                    <small style="color: var(--text-muted);">${s.email}</small>
                </td>
                <td>${s.course}</td>
                <td><span class="badge badge-primary">${s.department}</span></td>
                <td>${s.year} (Sem ${s.semester})</td>
                <td><span style="font-family: var(--font-mono); font-weight: 700; color: var(--success);">${s.gpa}</span></td>
                <td>
                    <button class="btn btn-secondary btn-sm" onclick="viewStudentDetails('${s.enrollment}')">👁️ View</button>
                </td>
            </tr>
        `).join("");

        renderPagination(filteredStudents.length, itemsPerPage, currentPage, (page) => {
            currentPage = page;
            renderStudentsTable();
        }, paginationContainer);
    }

    if (searchInput) {
        let t;
        searchInput.addEventListener("input", () => {
            clearTimeout(t);
            t = setTimeout(applyStudentFilters, 200);
        });
    }

    if (departmentFilter) departmentFilter.addEventListener("change", applyStudentFilters);
    if (yearFilter) yearFilter.addEventListener("change", applyStudentFilters);

    loadStudents();
}

/* ==========================================================================
   3. Collapsible FAQ Accordion Module (Practical 4 & 6)
   ========================================================================== */
function initFaqModule() {
    const container = document.getElementById("faqAccordion");
    const searchInput = document.getElementById("faqSearch");
    const categoryButtons = document.querySelectorAll(".faq-cat-btn");

    let allFaqs = [];
    let selectedCat = "All";

    async function loadFaqs() {
        container.innerHTML = `<p style="text-align: center; padding: 20px;">Loading Frequently Asked Questions...</p>`;
        try {
            const res = await fetch("../data/faqs.json");
            allFaqs = await res.json();
            localStorage.setItem("studenthub_cached_faqs", JSON.stringify(allFaqs));
        } catch (e) {
            const cached = localStorage.getItem("studenthub_cached_faqs");
            if (cached) allFaqs = JSON.parse(cached);
        }
        renderFaqs();
    }

    function renderFaqs() {
        const query = searchInput ? searchInput.value.toLowerCase().trim() : "";
        const filtered = allFaqs.filter((f) => {
            const matchesQuery = f.question.toLowerCase().includes(query) || f.answer.toLowerCase().includes(query);
            const matchesCat = (selectedCat === "All" || f.category === selectedCat);
            return matchesQuery && matchesCat;
        });

        if (filtered.length === 0) {
            container.innerHTML = `<div style="text-align: center; padding: 30px; color: var(--text-muted);">No matching FAQs found.</div>`;
            return;
        }

        container.innerHTML = filtered.map((f, idx) => `
            <div class="accordion-item" id="faq-item-${f.id}">
                <button class="accordion-header" onclick="toggleAccordion('faq-item-${f.id}')">
                    <span><strong>Q${idx + 1}.</strong> ${f.question}</span>
                    <span class="badge badge-primary" style="font-size: 0.72rem; margin-right: 8px;">${f.category}</span>
                    <span class="accordion-icon">▼</span>
                </button>
                <div class="accordion-body">
                    <p>${f.answer}</p>
                </div>
            </div>
        `).join("");
    }

    window.toggleAccordion = function(itemId) {
        const item = document.getElementById(itemId);
        if (!item) return;
        const isActive = item.classList.contains("active");

        // Optional accordion behavior: close siblings
        document.querySelectorAll(".accordion-item").forEach(el => {
            if (el !== item) el.classList.remove("active");
        });

        item.classList.toggle("active", !isActive);
    };

    if (searchInput) {
        let timer;
        searchInput.addEventListener("input", () => {
            clearTimeout(timer);
            timer = setTimeout(renderFaqs, 200);
        });
    }

    categoryButtons.forEach(btn => {
        btn.addEventListener("click", () => {
            categoryButtons.forEach(b => b.classList.remove("active"));
            btn.classList.add("active");
            selectedCat = btn.getAttribute("data-category") || "All";
            renderFaqs();
        });
    });

    loadFaqs();
}

/* ==========================================================================
   4. Notices Feed Module
   ========================================================================== */
function initNoticesModule() {
    const container = document.getElementById("noticesContainer");
    if (!container) return;

    fetch("../data/notices.json")
        .then(r => r.json())
        .then(notices => {
            container.innerHTML = notices.map(n => `
                <li style="margin-bottom: 14px; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <span class="badge ${n.urgent ? 'badge-danger' : 'badge-primary'}">${n.category}</span>
                        <small style="color: var(--text-muted); font-size: 0.75rem;">📅 ${n.date}</small>
                    </div>
                    <h5 style="margin-bottom: 4px; color: var(--text-primary); font-size: 0.92rem;">${n.title}</h5>
                    <p style="font-size: 0.85rem; color: var(--text-secondary);">${n.content}</p>
                </li>
            `).join("");
        })
        .catch(err => console.warn("Notice fetch error:", err));
}

/* ==========================================================================
   5. Reusable Pagination Generator (Practical 6)
   ========================================================================== */
function renderPagination(totalItems, itemsPerPage, currentPage, onPageChange, targetContainer) {
    if (!targetContainer) return;
    const totalPages = Math.ceil(totalItems / itemsPerPage);

    if (totalPages <= 1) {
        targetContainer.innerHTML = "";
        return;
    }

    let html = `
        <button class="pagination-btn" ${currentPage === 1 ? 'disabled' : ''} id="pagPrev">‹ Prev</button>
    `;

    for (let i = 1; i <= totalPages; i++) {
        html += `<button class="pagination-btn ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
    }

    html += `
        <button class="pagination-btn" ${currentPage === totalPages ? 'disabled' : ''} id="pagNext">Next ›</button>
    `;

    targetContainer.innerHTML = html;

    const prevBtn = targetContainer.querySelector("#pagPrev");
    const nextBtn = targetContainer.querySelector("#pagNext");

    if (prevBtn) prevBtn.addEventListener("click", () => onPageChange(currentPage - 1));
    if (nextBtn) nextBtn.addEventListener("click", () => onPageChange(currentPage + 1));

    targetContainer.querySelectorAll("[data-page]").forEach(btn => {
        btn.addEventListener("click", () => {
            const p = parseInt(btn.getAttribute("data-page"));
            onPageChange(p);
        });
    });
}

/* Helper to escape quotes in string literals inside HTML attributes */
function escapeQuotes(str) {
    return str.replace(/'/g, "\\'").replace(/"/g, "&quot;");
}

/* Event modal trigger */
window.openEventRegisterModal = function(id, title) {
    const modalTitle = document.getElementById("registerModalEventTitle");
    const modalInput = document.getElementById("registerModalEventId");
    if (modalTitle) modalTitle.textContent = title;
    if (modalInput) modalInput.value = id;
    window.openModal("eventRegisterModal");
};
