// Shared Notification Utility
// Provides a consistent notification system across all modules

/**
 * Calculate top offset for stacking multiple notifications
 * @returns {string} Tailwind class for top position
 */
function getNotificationTopOffset() {
    // Count existing notifications to calculate stacking
    const existingNotifications = document.querySelectorAll('[role="alert"][data-notification="true"]');
    const count = existingNotifications.length;

    // Each notification is approximately 80px tall (including margin)
    // Start at top-4 (16px), then add 80px per notification
    // Using Tailwind spacing: top-4 (16px), top-24 (96px), top-44 (176px), etc.
    const topClasses = ['top-4', 'top-24', 'top-44', 'top-64', 'top-80'];
    return topClasses[Math.min(count, topClasses.length - 1)] || 'top-4';
}

/**
 * Cleanup function to remove notification and clear timeouts
 * @param {HTMLElement} notification - Notification element to remove
 */
function cleanupNotification(notification) {
    if (!notification) return;

    // Clear all timeouts
    if (notification._notificationTimeouts && Array.isArray(notification._notificationTimeouts)) {
        notification._notificationTimeouts.forEach(timeoutId => {
            clearTimeout(timeoutId);
        });
        notification._notificationTimeouts = [];
    }

    // Remove from DOM
    if (notification.parentElement) {
        notification.remove();
    }
}

/**
 * Show a notification message
 * @param {string} message - The message to display
 * @param {string} type - Notification type: 'success', 'error', 'info', 'danger' (maps to 'error'), or 'warning' (maps to 'info') (default: 'success')
 * @param {object} options - Optional configuration
 * @param {boolean} options.animated - If true, use slide-in/out animation (default: false)
 */
export function showNotification(message, type = 'success', options = {}) {
    // Validate inputs
    if (!message || typeof message !== 'string') {
        console.warn('showNotification: message must be a non-empty string');
        return;
    }

    // Type normalization: map legacy types to standard types
    const typeMap = {
        'danger': 'error',
        'warning': 'info',
        'success': 'success',
        'error': 'error',
        'info': 'info'
    };
    const normalizedType = typeMap[type] || 'success';

    // Determine colors and icon based on normalized type (all Tailwind classes are static literals for JIT safety)
    const colors = {
        success: 'bg-green-500 text-white',
        error: 'bg-red-500 text-white',
        info: 'bg-blue-500 text-white'
    };

    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        info: 'info-circle'
    };

    const colorClass = colors[normalizedType] || colors.success;
    const icon = icons[normalizedType] || icons.success;

    // Calculate top offset for stacking
    const topOffset = getNotificationTopOffset();

    // Build base classes with accessibility and UX improvements
    const baseClasses = `fixed ${topOffset} right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 max-w-sm break-words`;
    const animatedClasses = options.animated ? 'transform translate-x-full' : '';
    const finalClasses = `${baseClasses} ${colorClass} ${animatedClasses}`.trim();

    // Create notification element with accessibility attributes
    const notification = document.createElement('div');
    notification.className = finalClasses;
    notification.setAttribute('role', 'alert');
    notification.setAttribute('aria-live', 'assertive');
    notification.setAttribute('aria-atomic', 'true');
    notification.setAttribute('data-notification', 'true');

    // Close handler function
    const handleClose = () => {
        if (options.animated) {
            notification.classList.add('translate-x-full');
            setTimeout(() => cleanupNotification(notification), 300);
        } else {
            cleanupNotification(notification);
        }
    };

    // Keyboard handler for close button
    const handleKeyDown = (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            handleClose();
        }
    };

    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${icon} mr-2" aria-hidden="true"></i>
            <span>${message}</span>
            <button
                type="button"
                class="ml-4 text-white hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-white rounded"
                aria-label="Bildirimi kapat"
                tabindex="0"
            >
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </div>
    `;

    // Attach event listeners
    const closeButton = notification.querySelector('button');
    if (closeButton) {
        closeButton.addEventListener('click', handleClose);
        closeButton.addEventListener('keydown', handleKeyDown);
    }

    // Append to body
    document.body.appendChild(notification);

    // Store timeout IDs for cleanup
    const timeouts = [];

    // Handle animation if enabled
    if (options.animated) {
        // Slide in animation
        const slideInTimeout = setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        timeouts.push(slideInTimeout);

        // Slide out and remove
        const slideOutTimeout = setTimeout(() => {
            notification.classList.add('translate-x-full');
            const removeTimeout = setTimeout(() => {
                cleanupNotification(notification);
            }, 300);
            timeouts.push(removeTimeout);
        }, 5000);
        timeouts.push(slideOutTimeout);
    } else {
        // Standard auto-remove after 5 seconds
        const removeTimeout = setTimeout(() => {
            cleanupNotification(notification);
        }, 5000);
        timeouts.push(removeTimeout);
    }

    // Store timeout IDs and event handlers on notification element for cleanup
    notification._notificationTimeouts = timeouts;
    notification._handleClose = handleClose;
    notification._handleKeyDown = handleKeyDown;
}

// Backward compatibility: expose to window if not already defined
if (typeof window !== 'undefined') {
    if (!window.showNotification) {
        window.showNotification = showNotification;
    }

    // Global helper kısayolları - Alpine ifadelerinde kullanım için
    // Zaten var olanı ezme, sadece eksikse tanımla
    if (!window.showSuccess) {
        window.showSuccess = function (message = 'İşlem başarıyla tamamlandı') {
            showNotification(message, 'success');
        };
    }

    if (!window.showError) {
        window.showError = function (message = 'Bir hata oluştu') {
            showNotification(message, 'error');
        };
    }

    if (!window.showInfo) {
        window.showInfo = function (message = '') {
            const text = message || 'Bilgilendirme';
            showNotification(text, 'info');
        };
    }
}

