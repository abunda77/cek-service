/**
 * Dashboard JavaScript functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    // Add fade-in animation to all cards
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('fade-in');
        }, 100 * index); // Stagger the animations
    });

    // Handle refresh status button
    const refreshButton = document.getElementById('refresh-status');
    if (refreshButton) {
        refreshButton.addEventListener('click', function() {
            this.classList.add('animate-spin');
            
            // Simulate refreshing data
            setTimeout(() => {
                this.classList.remove('animate-spin');
                showNotification('Status berhasil diperbarui!', 'success');
            }, 1500);
        });
    }

    // Handle download report button
    const downloadButton = document.getElementById('download-report');
    if (downloadButton) {
        downloadButton.addEventListener('click', function() {
            showNotification('Laporan sedang diunduh...', 'info');
            
            // Simulate download delay
            setTimeout(() => {
                showNotification('Laporan berhasil diunduh!', 'success');
            }, 2000);
        });
    }

    // Handle settings button
    const settingsButton = document.getElementById('settings-btn');
    if (settingsButton) {
        settingsButton.addEventListener('click', function() {
            showNotification('Fitur pengaturan sedang dalam pengembangan', 'warning');
        });
    }

    // Handle notifications button
    const notificationButton = document.getElementById('notification-btn');
    if (notificationButton) {
        notificationButton.addEventListener('click', function() {
            showNotification('Tidak ada notifikasi baru', 'info');
        });
    }
});

/**
 * Shows a notification toast message
 * @param {string} message - The message to display
 * @param {string} type - The type of notification (success, info, warning, error)
 */
function showNotification(message, type = 'info') {
    // Create notification container if it doesn't exist
    let container = document.getElementById('notification-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'notification-container';
        container.style.position = 'fixed';
        container.style.top = '20px';
        container.style.right = '20px';
        container.style.zIndex = '1000';
        document.body.appendChild(container);
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'notification fade-in';
    notification.style.backgroundColor = getNotificationColor(type);
    notification.style.color = 'white';
    notification.style.padding = '12px 20px';
    notification.style.borderRadius = '8px';
    notification.style.marginBottom = '10px';
    notification.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
    notification.style.display = 'flex';
    notification.style.alignItems = 'center';
    notification.style.justifyContent = 'space-between';
    notification.style.minWidth = '300px';
    
    // Add icon based on notification type
    let icon = '';
    switch (type) {
        case 'success':
            icon = '<i class="fas fa-check-circle"></i>';
            break;
        case 'warning':
            icon = '<i class="fas fa-exclamation-triangle"></i>';
            break;
        case 'error':
            icon = '<i class="fas fa-times-circle"></i>';
            break;
        default:
            icon = '<i class="fas fa-info-circle"></i>';
    }
    
    notification.innerHTML = `
        <div style="display: flex; align-items: center;">
            <span style="margin-right: 10px; font-size: 1.2em;">${icon}</span>
            <span>${message}</span>
        </div>
        <button style="background: none; border: none; color: white; cursor: pointer; margin-left: 10px;">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Add to container
    container.appendChild(notification);
    
    // Add close functionality
    const closeButton = notification.querySelector('button');
    closeButton.addEventListener('click', function() {
        notification.classList.add('fade-out');
        setTimeout(() => {
            container.removeChild(notification);
        }, 300);
    });
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode === container) {
            notification.classList.add('fade-out');
            setTimeout(() => {
                if (notification.parentNode === container) {
                    container.removeChild(notification);
                }
            }, 300);
        }
    }, 5000);
}

/**
 * Get notification background color based on type
 * @param {string} type - Notification type
 * @returns {string} - CSS color value
 */
function getNotificationColor(type) {
    switch (type) {
        case 'success':
            return '#10b981'; // green
        case 'warning':
            return '#f59e0b'; // yellow
        case 'error':
            return '#dc2626'; // red
        default:
            return '#3b82f6'; // blue
    }
}

/**
 * Add keyframes for fade out animation
 */
(function() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes fadeOut {
            from { opacity: 1; transform: translateX(0); }
            to { opacity: 0; transform: translateX(20px); }
        }
        .fade-in {
            animation: fadeIn 0.3s ease-out forwards;
        }
        .fade-out {
            animation: fadeOut 0.3s ease-out forwards;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .animate-spin {
            animation: spin 1s linear infinite;
        }
    `;
    document.head.appendChild(style);
})(); 