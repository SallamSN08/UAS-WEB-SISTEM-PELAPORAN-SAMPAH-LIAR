// SiPAL - Main JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Flash messages
    showFlashMessages();

    // Mobile menu toggle
    const mobileToggle = document.querySelector('.mobile-toggle');
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function() {
            document.querySelector('.user-nav-menu').classList.toggle('active');
        });
    }

    // Loading overlay
    const loadingOverlay = document.getElementById('loadingOverlay');
    if (loadingOverlay) {
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                loadingOverlay.style.display = 'flex';
            });
        });
    }

    // Fade in animation
    const fadeElements = document.querySelectorAll('.animate-fade-in');
    fadeElements.forEach((el, i) => {
        el.style.animationDelay = (i * 0.1) + 's';
    });
});

// Flash Messages
function showFlashMessages() {
    const flashContainer = document.getElementById('flashContainer');
    if (!flashContainer) return;

    const flashes = flashContainer.querySelectorAll('.flash-message');
    flashes.forEach(flash => {
        const type = flash.dataset.type;
        const message = flash.dataset.message;
        showToast(type, message);
        flash.remove();
    });
}

function showToast(type, message) {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const icons = {
        success: '✅',
        error: '❌',
        warning: '⚠️',
        info: 'ℹ️'
    };

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <span style="font-size: 20px;">${icons[type] || 'ℹ️'}</span>
        <span style="font-size: 14px; font-weight: 500;">${message}</span>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 400);
    }, 4000);
}

// Preview foto sebelum upload
function previewFoto(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Confirm action
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('success', 'Teks berhasil disalin!');
    });
}

// Format number
function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

// Export table to CSV
function exportTableToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;

    let csv = [];
    const rows = table.querySelectorAll('tr');

    rows.forEach(row => {
        let cols = row.querySelectorAll('td, th');
        let rowData = [];
        cols.forEach(col => {
            rowData.push('"' + col.innerText.replace(/"/g, '""') + '"');
        });
        csv.push(rowData.join(','));
    });

    const csvContent = '﻿' + csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
}
