/**
 * StudentHub - AJAX / Fetch API CRUD Operations
 * Practical 13: Asynchronous CRUD without Page Reload, Debounced Search & Centralized Error Handling
 * Charotar University of Science and Technology (CHARUSAT) - FTE ITUE203
 */

class StudentHubAPI {
    constructor(basePath = "../api/") {
        this.basePath = basePath;
    }

    async request(endpoint, options = {}) {
        const url = `${this.basePath}${endpoint}`;
        const defaultHeaders = {
            "Accept": "application/json"
        };

        if (options.body && !(options.body instanceof FormData)) {
            defaultHeaders["Content-Type"] = "application/json";
            options.body = JSON.stringify(options.body);
        }

        const config = {
            ...options,
            headers: {
                ...defaultHeaders,
                ...options.headers
            }
        };

        try {
            const response = await fetch(url, config);
            const contentType = response.headers.get("content-type");
            let data = null;

            if (contentType && contentType.includes("application/json")) {
                data = await response.json();
            } else {
                data = { message: await response.text() };
            }

            if (!response.ok) {
                const error = new Error(data.message || `HTTP ${response.status} Error`);
                error.status = response.status;
                error.data = data;
                throw error;
            }

            return data;
        } catch (err) {
            console.error(`API Error on ${endpoint}:`, err);
            throw err;
        }
    }

    // Students CRUD
    getStudents(params = {}) {
        const query = new URLSearchParams(params).toString();
        return this.request(`students.php${query ? '?' + query : ''}`);
    }

    addStudent(studentData) {
        return this.request("students.php", {
            method: "POST",
            body: studentData
        });
    }

    updateStudent(studentData) {
        return this.request("students.php", {
            method: "PUT",
            body: studentData
        });
    }

    deleteStudent(id) {
        return this.request(`students.php?id=${id}`, {
            method: "DELETE"
        });
    }

    // Events CRUD
    getEvents(params = {}) {
        const query = new URLSearchParams(params).toString();
        return this.request(`events.php${query ? '?' + query : ''}`);
    }

    addEvent(eventData) {
        return this.request("events.php", {
            method: "POST",
            body: eventData
        });
    }

    updateEvent(eventData) {
        return this.request("events.php", {
            method: "PUT",
            body: eventData
        });
    }

    deleteEvent(id) {
        return this.request(`events.php?id=${id}`, {
            method: "DELETE"
        });
    }
}

// Global API client instance
window.apiClient = new StudentHubAPI();

/* ==========================================================================
   Interactive AJAX CRUD Controller for Admin Pages
   ========================================================================== */
document.addEventListener("DOMContentLoaded", () => {
    initAjaxCrudManager();
});

function initAjaxCrudManager() {
    const ajaxTable = document.getElementById("ajaxCrudTableBody");
    const entityType = document.body.getAttribute("data-entity-type"); // "students" or "events"
    if (!ajaxTable || !entityType) return;

    loadData();

    // Debounced search
    const searchInput = document.getElementById("ajaxSearch");
    if (searchInput) {
        let timer;
        searchInput.addEventListener("input", () => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                loadData({ search: searchInput.value });
            }, 300);
        });
    }

    async function loadData(params = {}) {
        ajaxTable.innerHTML = `<tr><td colspan="8" style="text-align: center; padding: 25px;">Loading via PHP JSON API...</td></tr>`;
        try {
            const result = entityType === "students" 
                ? await apiClient.getStudents(params) 
                : await apiClient.getEvents(params);

            if (result.success && result.data) {
                renderRows(result.data);
            } else {
                ajaxTable.innerHTML = `<tr><td colspan="8" style="text-align: center; color: var(--danger); padding: 20px;">${result.message || 'Error loading records.'}</td></tr>`;
            }
        } catch (err) {
            // Fallback to static JSON file if PHP server not active
            console.warn("Backend API not reachable, falling back to local dataset:", err);
            loadFallbackLocalData();
        }
    }

    async function loadFallbackLocalData() {
        try {
            const fallbackUrl = entityType === "students" ? "../data/students.json" : "../data/events.json";
            const res = await fetch(fallbackUrl);
            const data = await res.json();
            renderRows(data);
            if (window.showToast) window.showToast("Loaded from local static store (PHP backend idle)", "info");
        } catch (e) {
            ajaxTable.innerHTML = `<tr><td colspan="8" style="text-align: center; color: var(--danger);">Failed to load data.</td></tr>`;
        }
    }

    function renderRows(items) {
        if (!items || items.length === 0) {
            ajaxTable.innerHTML = `<tr><td colspan="8" style="text-align: center; padding: 30px; color: var(--text-muted);">No records found.</td></tr>`;
            return;
        }

        if (entityType === "students") {
            ajaxTable.innerHTML = items.map(s => `
                <tr id="row-student-${s.id}">
                    <td><strong>${s.enrollment}</strong></td>
                    <td>${s.name}</td>
                    <td>${s.email}</td>
                    <td>${s.course}</td>
                    <td>${s.year}</td>
                    <td><span class="badge ${s.status === 'Active' ? 'badge-success' : 'badge-warning'}">${s.status || 'Active'}</span></td>
                    <td>
                        <button class="btn btn-secondary btn-sm" onclick="editStudentAjax(${s.id})">✏️ Edit</button>
                        <button class="btn btn-danger btn-sm" onclick="confirmDeleteStudent(${s.id}, '${escapeQuotes(s.name)}')">🗑️ Delete</button>
                    </td>
                </tr>
            `).join("");
        } else {
            ajaxTable.innerHTML = items.map(ev => `
                <tr id="row-event-${ev.id}">
                    <td><strong>#${ev.id}</strong></td>
                    <td>${ev.title}</td>
                    <td><span class="badge badge-primary">${ev.category}</span></td>
                    <td>${ev.date}</td>
                    <td>${ev.venue}</td>
                    <td><span class="badge ${ev.status === 'Open' ? 'badge-success' : 'badge-danger'}">${ev.status}</span></td>
                    <td>
                        <button class="btn btn-secondary btn-sm" onclick="editEventAjax(${ev.id})">✏️ Edit</button>
                        <button class="btn btn-danger btn-sm" onclick="confirmDeleteEvent(${ev.id}, '${escapeQuotes(ev.title)}')">🗑️ Delete</button>
                    </td>
                </tr>
            `).join("");
        }
    }

    // Delete actions with instant DOM removal
    window.confirmDeleteStudent = async function(id, name) {
        if (!confirm(`Are you sure you want to delete student: "${name}"?`)) return;
        try {
            await apiClient.deleteStudent(id);
            const row = document.getElementById(`row-student-${id}`);
            if (row) {
                row.style.transition = "opacity 0.3s";
                row.style.opacity = "0";
                setTimeout(() => row.remove(), 300);
            }
            if (window.showToast) window.showToast(`Deleted student "${name}" successfully`, "success");
        } catch (err) {
            // Local DOM removal simulation if backend idle
            const row = document.getElementById(`row-student-${id}`);
            if (row) row.remove();
            if (window.showToast) window.showToast(`Removed "${name}" from view (client mode)`, "warning");
        }
    };

    window.confirmDeleteEvent = async function(id, title) {
        if (!confirm(`Are you sure you want to delete event: "${title}"?`)) return;
        try {
            await apiClient.deleteEvent(id);
            const row = document.getElementById(`row-event-${id}`);
            if (row) {
                row.style.transition = "opacity 0.3s";
                row.style.opacity = "0";
                setTimeout(() => row.remove(), 300);
            }
            if (window.showToast) window.showToast(`Event deleted successfully`, "success");
        } catch (err) {
            const row = document.getElementById(`row-event-${id}`);
            if (row) row.remove();
            if (window.showToast) window.showToast(`Event removed from view (client mode)`, "warning");
        }
    };
}

function escapeQuotes(str) {
    return (str || "").replace(/'/g, "\\'").replace(/"/g, "&quot;");
}
