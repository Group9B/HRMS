/**
 * Notifications Handler
 * 
 * Handles fetching, displaying, and managing notifications in the header.
 */

(function () {
    'use strict';

    const NOTIFICATION_API = '/hrms/api/api_notifications.php';
    const REFRESH_INTERVAL = 60000; // 60 seconds

    let notificationDropdown = null;
    let notificationBadge = null;
    let notificationList = null;

    /**
     * Initialize notifications
     */
    function init() {
        notificationDropdown = document.getElementById('notificationButton');
        notificationBadge = document.getElementById('notificationBadge');
        notificationList = document.getElementById('notificationList');

        if (!notificationDropdown) {
            return; // Notifications not enabled for this user
        }

        // Fetch notifications on load
        fetchNotifications();

        // Set up periodic refresh
        setInterval(fetchNotifications, REFRESH_INTERVAL);

        // Mark all as read when clicking the "Mark all read" button
        const markAllBtn = document.getElementById('markAllReadBtn');
        if (markAllBtn) {
            markAllBtn.addEventListener('click', markAllAsRead);
        }
    }

    /**
     * Fetch notifications from the API
     */
    async function fetchNotifications() {
        try {
            const response = await fetch(`${NOTIFICATION_API}?action=get_notifications&limit=10`);
            const data = await response.json();

            if (data.success) {
                updateBadge(data.unread_count);
                renderNotifications(data.data);
            }
        } catch (error) {
            console.error('Failed to fetch notifications:', error);
        }
    }

    /**
     * Update the notification badge count
     */
    function updateBadge(count) {
        if (!notificationBadge) return;

        if (count > 0) {
            notificationBadge.textContent = count > 99 ? '99+' : count;
            notificationBadge.classList.remove('d-none');
        } else {
            notificationBadge.classList.add('d-none');
        }
    }

    /**
     * Render notifications in the dropdown
     */
    function renderNotifications(notifications) {
        if (!notificationList) return;

        // Clear existing notifications (keep header and footer)
        const existingItems = notificationList.querySelectorAll('.notification-item');
        existingItems.forEach(item => item.remove());

        const emptyState = notificationList.querySelector('.notification-empty');
        const divider = notificationList.querySelector('.notification-divider');

        if (notifications.length === 0) {
            if (emptyState) emptyState.classList.remove('d-none');
            if (divider) divider.classList.add('d-none');
            return;
        }

        if (emptyState) emptyState.classList.add('d-none');
        if (divider) divider.classList.remove('d-none');

        // Insert notifications before the divider
        const insertPoint = divider || notificationList.lastElementChild;

        notifications.forEach(notification => {
            const li = document.createElement('li');
            li.className = 'notification-item';
            const unreadDot = notification.is_read ? '' : '<span class="position-absolute top-50 start-0 translate-middle-y ms-1 bg-primary rounded-circle" style="width: 8px; height: 8px;"></span>';
            li.innerHTML = `
                <a class="dropdown-item py-2 position-relative ${notification.is_read ? '' : 'ps-4'}" 
                   href="${notification.link}" 
                   data-notification-id="${notification.id}">
                    ${unreadDot}
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0 me-2">
                            <i class="ti ${getNotificationIcon(notification.type)} text-${getNotificationColor(notification.type)}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="small ${notification.is_read ? '' : 'fw-bold'}">${escapeHtml(notification.title)}</div>
                            <div class="small text-body-secondary text-truncate" style="max-width: 200px;">${escapeHtml(notification.message)}</div>
                            <div class="small text-body-tertiary">${notification.time_ago}</div>
                        </div>
                    </div>
                </a>
            `;

            // Mark as read on click
            li.querySelector('a').addEventListener('click', function (e) {
                const notifId = this.dataset.notificationId;
                markAsRead(notifId);
            });

            insertPoint.parentNode.insertBefore(li, insertPoint);
        });
    }

    /**
     * Get icon class for notification type
     */
    function getNotificationIcon(type) {
        const icons = {
            'leave': 'ti-calendar-event',
            'payroll': 'ti-receipt',
            'attendance': 'ti-clock',
            'performance': 'ti-chart-bar',
            'task': 'ti-list-check',
            'reminder': 'ti-bell',
            'system': 'ti-info-circle',
            'feedback': 'ti-message-circle',
            'file': 'ti-file'
        };
        return icons[type] || 'ti-bell';
    }

    /**
     * Get color for notification type
     */
    function getNotificationColor(type) {
        const colors = {
            'leave': 'primary',
            'payroll': 'success',
            'attendance': 'info',
            'performance': 'warning',
            'task': 'secondary',
            'reminder': 'danger',
            'system': 'dark'
        };
        return colors[type] || 'primary';
    }

    /**
     * Mark a notification as read
     */
    async function markAsRead(notificationId) {
        try {
            const formData = new FormData();
            formData.append('notification_id', notificationId);

            await fetch(`${NOTIFICATION_API}?action=mark_read`, {
                method: 'POST',
                body: formData
            });
        } catch (error) {
            console.error('Failed to mark notification as read:', error);
        }
    }

    /**
     * Mark all notifications as read
     */
    async function markAllAsRead(e) {
        e.preventDefault();
        try {
            await fetch(`${NOTIFICATION_API}?action=mark_all_read`, {
                method: 'POST'
            });
            fetchNotifications(); // Refresh the list
        } catch (error) {
            console.error('Failed to mark all notifications as read:', error);
        }
    }

    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
