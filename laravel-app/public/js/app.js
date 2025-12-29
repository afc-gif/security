// ARTSCI POS System - Main JavaScript

document.addEventListener('DOMContentLoaded', () => {
    initNavigation();
    initAlerts();
    initAdminSidebar();
});

function initNavigation() {
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', () => {
            navToggle.classList.toggle('active');
            navMenu.classList.toggle('active');
        });

        // Close menu when a link is clicked
        navMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                navToggle.classList.remove('active');
                navMenu.classList.remove('active');
            });
        });
    }
}

function initAlerts() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
}

function initAdminSidebar() {
    const sidebar = document.querySelector('.admin-sidebar');
    const backdrop = document.querySelector('.admin-sidebar-backdrop');
    const toggles = document.querySelectorAll('.admin-menu-toggle');

    if (!sidebar) return;

    const closeSidebar = () => {
        sidebar.classList.remove('open');
        if (backdrop) {
            backdrop.classList.remove('active');
        }
    };

    toggles.forEach(toggle => {
        toggle.addEventListener('click', () => {
            const isOpen = sidebar.classList.toggle('open');
            if (backdrop) {
                backdrop.classList.toggle('active', isOpen);
            }
        });
    });

    sidebar.querySelectorAll('.nav-item').forEach(link => {
        link.addEventListener('click', closeSidebar);
    });

    if (backdrop) {
        backdrop.addEventListener('click', closeSidebar);
    }
}
