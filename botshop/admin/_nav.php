<?php
/**
 * _nav.php - Top navigation bar
 */
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
    <div class="container-fluid">
        <!-- Toggle Sidebar Button (for mobile) -->
        <button class="btn btn-outline-secondary d-lg-none me-2" type="button" id="sidebar-toggle">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Brand -->
        <span class="navbar-brand d-lg-none">
            Dashboard
        </span>

        <!-- Right Side Items -->
        <div class="ms-auto d-flex align-items-center gap-2">
            <!-- Search Bar -->
            <div class="input-group" style="max-width: 300px;">
                <span class="input-group-text">
                    <i>🔍</i>
                </span>
                <input type="text" class="form-control form-control-sm" placeholder="Search..." data-search>
            </div>

            <!-- Notifications -->
            <div class="dropdown">
                <button class="btn btn-link position-relative" type="button" id="notification-btn">
                    🔔
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                          id="notification-count" style="display: none;">
                        0
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notification-btn">
                    <li><a class="dropdown-item" href="#">No new notifications</a></li>
                </ul>
            </div>

            <!-- Theme Toggle -->
            <button class="btn btn-link" type="button" id="theme-toggle" title="Toggle theme">
                🌙
            </button>

            <!-- User Menu -->
            <div class="dropdown">
                <button class="btn btn-link" type="button" id="user-menu">
                    👤
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="user-menu">
                    <li><a class="dropdown-item" href="#">Profile</a></li>
                    <li><a class="dropdown-item" href="#">Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#">Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<style>
.navbar {
    position: sticky;
    top: 0;
    z-index: 99;
}

.btn-link {
    color: var(--text-secondary);
    text-decoration: none;
    font-size: 1.25rem;
    padding: 0.5rem 0.75rem;
    transition: color 0.15s ease;
}

.btn-link:hover {
    color: var(--primary-color);
}

.dropdown-menu {
    min-width: 200px;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
}

.dropdown-item {
    color: var(--text-dark);
    transition: all 0.15s ease;
}

.dropdown-item:hover {
    background-color: var(--bg-light);
    color: var(--primary-color);
}
</style>

<script>
// Sidebar toggle for mobile
document.getElementById('sidebar-toggle')?.addEventListener('click', () => {
    const sidebar = document.querySelector('.sidebar');
    sidebar?.classList.toggle('collapsed');
});

// Theme toggle
document.getElementById('theme-toggle')?.addEventListener('click', () => {
    const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', newTheme);
    if (typeof Storage !== 'undefined') {
        Storage.set('dashboard-theme', newTheme, 'local');
    }
});

// Search functionality
document.querySelector('[data-search]')?.addEventListener('keyup', (e) => {
    const query = e.target.value.toLowerCase();
    const items = document.querySelectorAll('[data-searchable]');
    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(query) ? '' : 'none';
    });
});

// User menu dropdowns
document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
    toggle.addEventListener('click', (e) => {
        e.preventDefault();
        const menu = toggle.nextElementSibling;
        menu?.classList.toggle('show');
    });
});

// Close dropdowns when clicking outside
document.addEventListener('click', (e) => {
    if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
            menu.classList.remove('show');
        });
    }
});
</script>
