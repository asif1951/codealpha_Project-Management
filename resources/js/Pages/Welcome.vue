<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';

// Define props
const props = defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    auth: {
        type: Object,
    },
    userTasks: {
        type: Array,
        default: () => []
    }
});

// State for mobile menu
const mobileMenuOpen = ref(false);

// State for showing tasks
const showTasks = ref(false);

// Get user tasks from props
const tasks = ref(props.userTasks.map(task => ({ ...task })));

// Loading states for individual tasks
const loadingStatus = ref({});

// Pagination state
const currentPage = ref(1);
const itemsPerPage = ref(10);
const searchQuery = ref('');
const statusFilter = ref('all');

// Toast notification state
const toast = ref({
    show: false,
    message: '',
    type: 'success',
    taskId: null
});

// Logo state
const logoError = ref(false);

// Notification state
const showNotifications = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);
let notificationInterval = null;

// Highlight task state
const highlightTaskId = ref(null);

// Load notifications from database
const loadNotifications = async () => {
    if (!props.auth?.user) return;
    
    try {
        const response = await fetch('/notifications');
        const data = await response.json();
        notifications.value = data.notifications || [];
        unreadCount.value = data.unread_count || 0;
    } catch (error) {
        console.error('Error loading notifications:', error);
    }
};

// Function to mark notification as read and show task
const markAsReadAndShowTask = async (notification) => {
    try {
        // Mark as read
        await fetch(`/notifications/${notification.id}/read`, { method: 'POST' });
        
        // Update local state
        const notif = notifications.value.find(n => n.id === notification.id);
        if (notif && !notif.is_read) {
            notif.is_read = true;
            unreadCount.value--;
        }
        
        // Close notification dropdown
        showNotifications.value = false;
        
        // Check if tasks section is hidden, then show it
        if (!showTasks.value) {
            showTasks.value = true;
            // Wait for DOM update
            await nextTick();
        }
        
        // Wait for the tasks section to be fully rendered
        await nextTick();
        
        // Find the task element and scroll to it
        if (notification.task_id) {
            // Set highlight for specific task
            highlightTaskId.value = notification.task_id;
            
            // Find the task in the tasks array to get its index for pagination
            const taskIndex = tasks.value.findIndex(t => t.id === notification.task_id);
            const task = tasks.value[taskIndex];
            
            if (task) {
                // Filter to see if task is visible with current filters
                let isVisible = true;
                
                // Check status filter
                if (statusFilter.value !== 'all' && task.status !== statusFilter.value) {
                    isVisible = false;
                    // Temporarily clear filters to show the task
                    statusFilter.value = 'all';
                }
                
                // Check search query
                if (searchQuery.value) {
                    const query = searchQuery.value.toLowerCase();
                    const matchesSearch = task.title.toLowerCase().includes(query) ||
                        (task.description && task.description.toLowerCase().includes(query));
                    if (!matchesSearch) {
                        isVisible = false;
                        searchQuery.value = '';
                    }
                }
                
                // Calculate which page the task is on
                const filteredIndex = filteredTasks.value.findIndex(t => t.id === notification.task_id);
                if (filteredIndex !== -1) {
                    const targetPage = Math.floor(filteredIndex / itemsPerPage.value) + 1;
                    if (targetPage !== currentPage.value) {
                        currentPage.value = targetPage;
                    }
                }
                
                // Wait for pagination to update
                await nextTick();
            }
            
            // Wait a bit for the table to render
            setTimeout(() => {
                const taskElement = document.getElementById(`task-row-${notification.task_id}`);
                if (taskElement) {
                    taskElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    taskElement.classList.add('task-highlight');
                    
                    // Remove highlight after 3 seconds
                    setTimeout(() => {
                        taskElement.classList.remove('task-highlight');
                        highlightTaskId.value = null;
                    }, 3000);
                }
            }, 500);
            
            // Show toast notification
            showToast('Showing task...', 'info');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error showing task', 'error');
    }
};

// Function to mark all as read
const markAllAsRead = async () => {
    try {
        await fetch('/notifications/read-all', { method: 'POST' });
        notifications.value.forEach(n => n.is_read = true);
        unreadCount.value = 0;
        showToast('All notifications marked as read', 'success');
    } catch (error) {
        console.error('Error marking all as read:', error);
    }
};

// Start polling for new notifications
const startPolling = () => {
    if (notificationInterval) clearInterval(notificationInterval);
    notificationInterval = setInterval(() => {
        loadNotifications();
    }, 10000); // Check every 10 seconds
};

// Computed property for filtered tasks
const filteredTasks = computed(() => {
    let filtered = [...tasks.value];
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(task => 
            task.title.toLowerCase().includes(query) ||
            (task.description && task.description.toLowerCase().includes(query))
        );
    }
    
    if (statusFilter.value !== 'all') {
        filtered = filtered.filter(task => task.status === statusFilter.value);
    }
    
    return filtered;
});

// Computed property for paginated tasks
const paginatedTasks = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return filteredTasks.value.slice(start, end);
});

// Computed property for total pages
const totalPages = computed(() => {
    return Math.ceil(filteredTasks.value.length / itemsPerPage.value);
});

// Function to change page
const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

// Function to show toast
const showToast = (message, type = 'success', taskId = null) => {
    toast.value = {
        show: true,
        message: message,
        type: type,
        taskId: taskId
    };
    
    setTimeout(() => {
        toast.value.show = false;
    }, 3000);
};

// Function to toggle tasks
const toggleTasks = () => {
    showTasks.value = !showTasks.value;
    currentPage.value = 1;
    
    if (showTasks.value) {
        setTimeout(() => {
            const tasksSection = document.getElementById('my-tasks-section');
            if (tasksSection) {
                tasksSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 100);
    }
};

// Helper function to format date
const formatDate = (date) => {
    if (!date) return 'No date';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

// Helper function to get status label
const getStatusLabel = (status) => {
    switch(status?.toLowerCase()) {
        case 'completed': return 'Completed';
        case 'in_progress': return 'In Progress';
        case 'pending': return 'Pending';
        default: return status || 'Pending';
    }
};

// Helper function to get status badge color
const getStatusColor = (status) => {
    switch(status?.toLowerCase()) {
        case 'completed': return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
        case 'in_progress': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
        case 'pending': return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
        default: return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    }
};

// Format notification time
const formatNotificationTime = (date) => {
    if (!date) return '';
    const now = new Date();
    const notifDate = new Date(date);
    const diffMs = now - notifDate;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
    if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
    return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
};

// Function to update task status
const updateTaskStatus = async (taskId, newStatus) => {
    loadingStatus.value[taskId] = true;
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        const response = await fetch(`/tasks/${taskId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ status: newStatus })
        });
        
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Failed to update status');
        }
        
        const result = await response.json();
        
        const taskIndex = tasks.value.findIndex(t => t.id === taskId);
        if (taskIndex !== -1) {
            const oldStatus = tasks.value[taskIndex].status;
            tasks.value[taskIndex].status = newStatus;
            
            showToast(
                `Task status changed from "${getStatusLabel(oldStatus)}" to "${getStatusLabel(newStatus)}"`,
                'success',
                taskId
            );
        }
        
        console.log('Status updated successfully:', result.message);
        
    } catch (error) {
        console.error('Error updating task status:', error);
        showToast(
            error.message || 'Failed to update task status. Please try again.',
            'error',
            taskId
        );
    } finally {
        setTimeout(() => {
            loadingStatus.value[taskId] = false;
        }, 500);
    }
};

onMounted(() => {
    loadNotifications();
    startPolling();
});

onUnmounted(() => {
    if (notificationInterval) clearInterval(notificationInterval);
});
</script>

<template>
    <Head title="SoftNiq - Task Manager" />

    <!-- Toast Notification -->
    <div 
        v-if="toast.show"
        :class="{
            'fixed top-24 right-4 z-50 mb-4 p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-0 max-w-md': true,
            'bg-green-100 border-l-4 border-green-500 text-green-700': toast.type === 'success',
            'bg-red-100 border-l-4 border-red-500 text-red-700': toast.type === 'error',
            'bg-blue-100 border-l-4 border-blue-500 text-blue-700': toast.type === 'info'
        }"
        role="alert"
    >
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg v-if="toast.type === 'success'" class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg v-if="toast.type === 'error'" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg v-if="toast.type === 'info'" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium">{{ toast.message }}</p>
            </div>
            <button 
                @click="toast.show = false"
                class="ml-auto -mx-1.5 -my-1.5 rounded-lg p-1.5 inline-flex h-8 w-8"
                :class="{
                    'bg-green-100 text-green-500 hover:bg-green-200': toast.type === 'success',
                    'bg-red-100 text-red-500 hover:bg-red-200': toast.type === 'error',
                    'bg-blue-100 text-blue-500 hover:bg-blue-200': toast.type === 'info'
                }"
            >
                <span class="sr-only">Close</span>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Navbar -->
    <nav class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 fixed w-full z-10 top-0 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 md:h-20">
                <!-- Logo Section -->
                <div class="flex items-center">
                    <Link href="/" class="flex items-center space-x-2 md:space-x-3">
                        <div class="h-10 w-10 md:h-12 md:w-12 flex items-center justify-center">
                            <img 
                                src="/logo.png" 
                                alt="SoftNiq Logo" 
                                class="max-h-full max-w-full object-contain"
                                @error="logoError = true"
                                v-if="!logoError"
                                style="width: auto; height: 40px;"
                            >
                            <svg v-else class="h-8 w-8 md:h-10 md:w-10 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                        </div>
                        <span class="font-bold text-lg md:text-2xl text-gray-900 dark:text-white">
                            Soft<span class="text-indigo-600 dark:text-indigo-400">Niq</span>
                        </span>
                    </Link>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex md:items-center md:space-x-6">
                    <div v-if="props.auth.user" class="flex items-center space-x-6">
                        <button 
                            @click="toggleTasks"
                            class="inline-flex items-center px-5 py-2.5 bg-green-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wider hover:bg-green-700 transition"
                        >
                            <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                            {{ showTasks ? 'Hide Tasks' : 'My Tasks' }}
                        </button>

                        <!-- Notification Bell Icon -->
                        <div class="relative">
                            <button 
                                @click="showNotifications = !showNotifications"
                                class="relative p-2 text-gray-600 dark:text-gray-300 hover:text-indigo-600 focus:outline-none transition-colors"
                            >
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <span v-if="unreadCount > 0" class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-500 rounded-full">
                                    {{ unreadCount }}
                                </span>
                            </button>

                            <!-- Notification Dropdown -->
                            <div v-if="showNotifications" class="absolute right-0 mt-2 w-80 md:w-96 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50">
                                <div class="p-3 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Notifications</h3>
                                    <button @click="markAllAsRead" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">Mark all as read</button>
                                </div>
                                <div class="max-h-96 overflow-y-auto">
                                    <div v-if="notifications.length === 0" class="p-8 text-center">
                                        <svg class="h-12 w-12 text-gray-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                        <p class="text-gray-500 text-sm">No notifications</p>
                                    </div>
                                    <div v-for="notification in notifications" :key="notification.id" 
                                        @click="markAsReadAndShowTask(notification)"
                                        :class="[
                                            'p-3 border-b border-gray-100 cursor-pointer transition-colors hover:bg-gray-50',
                                            notification.is_read ? 'bg-white' : 'bg-blue-50'
                                        ]"
                                    >
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                                                    <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-900">{{ notification.title }}</p>
                                                <p class="text-xs text-gray-600 mt-1">{{ notification.message }}</p>
                                                <p class="text-xs text-gray-400 mt-1">{{ formatNotificationTime(notification.created_at) }}</p>
                                                <p class="text-xs text-green-600 mt-1 flex items-center">
                                                    <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                                    </svg>
                                                    Click to view task
                                                </p>
                                            </div>
                                            <div v-if="!notification.is_read" class="flex-shrink-0">
                                                <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User Profile Dropdown -->
                        <div class="relative group">
                            <button class="flex items-center space-x-3 focus:outline-none">
                                <div class="h-9 w-9 md:h-10 md:w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                    <span class="text-indigo-600 font-medium text-sm md:text-base">
                                        {{ props.auth.user.name.charAt(0).toUpperCase() }}
                                    </span>
                                </div>
                                <span class="text-sm md:text-base text-gray-700 font-medium">{{ props.auth.user.name }}</span>
                                <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg py-2 z-20 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 border border-gray-100">
                                <Link href="/profile" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                    <div class="flex items-center space-x-3">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span>My Profile</span>
                                    </div>
                                </Link>
                                <Link :href="route('logout')" method="post" as="button" class="block w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-gray-50">
                                    <div class="flex items-center space-x-3">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        <span>Logout</span>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>
                    <div v-else class="flex items-center space-x-6">
                        <Link v-if="props.canLogin" :href="route('login')" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-base font-medium">Login</Link>
                        <Link v-if="props.canRegister" :href="route('register')" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-indigo-700 transition">Register</Link>
                    </div>
                </div>

                <!-- Mobile menu button -->
                <div class="flex items-center space-x-2 md:hidden">
                    <div v-if="props.auth.user" class="relative">
                        <button @click="showNotifications = !showNotifications" class="relative p-2">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span v-if="unreadCount > 0" class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold text-white bg-red-500 rounded-full">{{ unreadCount }}</span>
                        </button>
                    </div>
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div v-if="mobileMenuOpen" class="md:hidden bg-white border-t border-gray-200 shadow-lg">
            <div class="px-4 pt-3 pb-4 space-y-2">
                <div v-if="props.auth.user" class="space-y-3">
                    <div class="px-3 py-3 flex items-center space-x-3 border-b border-gray-200">
                        <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center">
                            <span class="text-indigo-600 font-semibold text-lg">{{ props.auth.user.name.charAt(0).toUpperCase() }}</span>
                        </div>
                        <div>
                            <p class="text-base font-semibold text-gray-900">{{ props.auth.user.name }}</p>
                            <p class="text-xs text-gray-500">{{ props.auth.user.email }}</p>
                        </div>
                    </div>
                    
                    <button @click="toggleTasks; mobileMenuOpen = false" class="block w-full text-left px-4 py-3 rounded-lg text-base font-medium text-green-600 hover:text-green-700 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center space-x-3">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                            <span>{{ showTasks ? 'Hide Tasks' : 'My Tasks' }}</span>
                        </div>
                    </button>
                    
                    <Link href="/profile" class="block px-4 py-3 rounded-lg text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50 transition-colors" @click="mobileMenuOpen = false">
                        <div class="flex items-center space-x-3">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>My Profile</span>
                        </div>
                    </Link>
                    
                    <Link :href="route('logout')" method="post" as="button" class="block w-full text-left px-4 py-3 rounded-lg text-base font-medium text-red-600 hover:bg-gray-50 transition-colors" @click="mobileMenuOpen = false">
                        <div class="flex items-center space-x-3">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span>Logout</span>
                        </div>
                    </Link>
                </div>
                <div v-else class="space-y-2">
                    <Link v-if="props.canLogin" :href="route('login')" class="block px-4 py-3 rounded-lg text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50 transition-colors" @click="mobileMenuOpen = false">Login</Link>
                    <Link v-if="props.canRegister" :href="route('register')" class="block px-4 py-3 rounded-lg text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50 transition-colors" @click="mobileMenuOpen = false">Register</Link>
                </div>
            </div>
        </div>
    </nav>

    <!-- Click outside to close notification dropdown -->
    <div v-if="showNotifications" class="fixed inset-0 z-40" @click="showNotifications = false"></div>

    <!-- Hero Section -->
    <div class="relative bg-gradient-to-br from-indigo-50 via-white to-purple-50 pt-20 md:pt-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24 lg:py-32">
            <div class="text-center">
                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-4 md:mb-6">
                    Welcome to <span class="text-indigo-600">SoftNiq</span>
                </h1>
                <p class="text-base sm:text-lg md:text-xl text-gray-600 mb-3 md:mb-4 max-w-2xl mx-auto px-4">Innovative Task Management Solutions for Modern Teams</p>
                <p class="text-sm sm:text-base md:text-lg text-gray-500 mb-6 md:mb-8 max-w-2xl mx-auto px-4">Organize, track, and complete your tasks efficiently with SoftNiq's powerful management system.</p>
                <div class="flex flex-col sm:flex-row justify-center space-y-3 sm:space-y-0 sm:space-x-4 px-4">
                    <button v-if="props.auth.user" @click="toggleTasks" class="px-5 py-2.5 md:px-6 md:py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition duration-300 transform hover:scale-105 text-sm md:text-base">
                        {{ showTasks ? 'Hide My Tasks' : 'View My Tasks' }}
                    </button>
                    <Link v-else :href="route('register')" class="px-5 py-2.5 md:px-6 md:py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition duration-300 transform hover:scale-105 text-sm md:text-base">
                        Get Started with SoftNiq
                    </Link>
                    <a href="#features" class="px-5 py-2.5 md:px-6 md:py-3 bg-gray-200 text-gray-900 font-semibold rounded-lg hover:bg-gray-300 transition duration-300 text-sm md:text-base">
                        Learn More
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- My Tasks Section -->
    <div v-if="showTasks" id="my-tasks-section" class="py-12 md:py-16 lg:py-20 bg-gradient-to-br from-green-50 via-white to-indigo-50 transition-all duration-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-6 md:mb-8">
                <div class="inline-flex items-center justify-center p-2 bg-green-100 rounded-full mb-3 md:mb-4">
                    <svg class="h-6 w-6 md:h-8 md:w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <h2 class="text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900 mb-2 md:mb-4">My <span class="text-green-600">Tasks</span></h2>
                <p class="text-sm md:text-base lg:text-lg text-gray-600">Track and manage your assigned tasks efficiently</p>
            </div>

            <!-- Filters and Search -->
            <div class="bg-white rounded-t-xl p-3 md:p-4 border-b border-gray-200">
                <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center space-y-3 md:space-y-0 md:space-x-4">
                    <div class="relative flex-1 w-full md:w-72">
                        <input type="text" v-model="searchQuery" @input="currentPage = 1" placeholder="Search tasks..." class="w-full px-4 py-2.5 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        <svg class="absolute left-3 top-3 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <div class="flex items-center space-x-3">
                        <label class="text-sm text-gray-600 font-medium">Status:</label>
                        <select v-model="statusFilter" @change="currentPage = 1" class="px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="all">All Tasks</option>
                            <option value="pending">🟡 Pending</option>
                            <option value="in_progress">🔵 In Progress</option>
                            <option value="completed">🟢 Completed</option>
                        </select>
                    </div>
                    <div class="text-sm text-gray-600">
                        Total: <span class="font-semibold text-gray-900">{{ filteredTasks.length }}</span> tasks
                    </div>
                </div>
            </div>

            <!-- Task Table -->
            <div v-if="filteredTasks && filteredTasks.length > 0" class="bg-white rounded-b-xl shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-green-50 to-indigo-50">
                            <tr>
                                <th class="px-4 py-3 md:px-6 md:py-4 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Task Title</th>
                                <th class="px-4 py-3 md:px-6 md:py-4 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Description</th>
                                <th class="px-4 py-3 md:px-6 md:py-4 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Due Date</th>
                                <th class="px-4 py-3 md:px-6 md:py-4 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="task in paginatedTasks" :key="task.id" 
                                :id="`task-row-${task.id}`"
                                :class="[
                                    'hover:bg-gray-50 transition duration-150',
                                    highlightTaskId === task.id ? 'task-highlight' : ''
                                ]"
                            >
                                <td class="px-4 py-3 md:px-6 md:py-4">
                                    <div class="text-sm md:text-base font-semibold text-gray-900">{{ task.title }}</div>
                                </td>
                                <td class="px-4 py-3 md:px-6 md:py-4">
                                    <div class="text-sm md:text-base text-gray-600 max-w-xs md:max-w-md">{{ task.description || 'No description' }}</div>
                                </td>
                                <td class="px-4 py-3 md:px-6 md:py-4 whitespace-nowrap">
                                    <div class="flex items-center text-sm md:text-base text-gray-600">
                                        <svg class="h-3 w-3 md:h-4 md:w-4 mr-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {{ formatDate(task.due_date) }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 md:px-6 md:py-4 whitespace-nowrap">
                                    <div class="relative inline-block">
                                        <select :value="task.status || 'pending'" @change="updateTaskStatus(task.id, $event.target.value)" :disabled="loadingStatus[task.id]" :class="['px-2 py-1 md:px-3 md:py-1.5 text-xs md:text-sm font-medium rounded-full cursor-pointer transition-all', getStatusColor(task.status)]" class="focus:outline-none focus:ring-2 focus:ring-green-500">
                                            <option value="pending" class="bg-white">🟡 Pending</option>
                                            <option value="in_progress" class="bg-white">🔵 In Progress</option>
                                            <option value="completed" class="bg-white">🟢 Completed</option>
                                        </select>
                                        <div v-if="loadingStatus[task.id]" class="absolute -right-5 md:-right-6 top-1/2 transform -translate-y-1/2">
                                            <svg class="animate-spin h-3 w-3 md:h-4 md:w-4 text-green-600" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="totalPages > 1" class="bg-white px-4 py-3 md:px-6 md:py-4 border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-between items-center space-y-3 sm:space-y-0">
                        <div class="flex items-center space-x-2">
                            <label class="text-xs md:text-sm text-gray-600">Show:</label>
                            <select v-model="itemsPerPage" @change="currentPage = 1" class="px-2 py-1 text-xs md:text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                <option :value="5">5</option>
                                <option :value="10">10</option>
                                <option :value="20">20</option>
                                <option :value="50">50</option>
                            </select>
                            <span class="text-xs md:text-sm text-gray-600">per page</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1" class="px-2 py-1 md:px-3 md:py-1 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                <svg class="h-4 w-4 md:h-5 md:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <div class="flex space-x-1">
                                <button v-for="page in Math.min(5, totalPages)" :key="page" @click="goToPage(page)" :class="['px-2 py-1 md:px-3 md:py-1 text-xs md:text-sm rounded-lg transition-colors', currentPage === page ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']">
                                    {{ page }}
                                </button>
                                <span v-if="totalPages > 5" class="px-2 py-1 text-xs md:text-sm text-gray-600">...</span>
                                <button v-if="totalPages > 5" @click="goToPage(totalPages)" :class="['px-2 py-1 md:px-3 md:py-1 text-xs md:text-sm rounded-lg transition-colors', currentPage === totalPages ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']">
                                    {{ totalPages }}
                                </button>
                            </div>
                            <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages" class="px-2 py-1 md:px-3 md:py-1 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                <svg class="h-4 w-4 md:h-5 md:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                        <div class="text-xs md:text-sm text-gray-600">
                            Showing {{ ((currentPage - 1) * itemsPerPage) + 1 }} to {{ Math.min(currentPage * itemsPerPage, filteredTasks.length) }} of {{ filteredTasks.length }} entries
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white rounded-xl shadow-lg p-8 md:p-12 text-center">
                <svg class="h-12 w-12 md:h-16 md:w-16 text-gray-400 mx-auto mb-3 md:mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <h3 class="text-base md:text-lg font-medium text-gray-900 mb-2">No tasks found</h3>
                <p class="text-sm md:text-base text-gray-500">{{ searchQuery || statusFilter !== 'all' ? 'No tasks match your filters.' : 'You don\'t have any tasks assigned yet.' }}</p>
                <button v-if="searchQuery || statusFilter !== 'all'" @click="searchQuery = ''; statusFilter = 'all'" class="mt-4 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm md:text-base">Clear Filters</button>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div id="features" class="py-12 md:py-16 lg:py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8 md:mb-12">
                <h2 class="text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900 mb-2 md:mb-4">Why Choose <span class="text-indigo-600">SoftNiq</span>?</h2>
                <p class="text-sm md:text-base lg:text-lg text-gray-600">Enterprise-grade features to boost your team's productivity</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                <div class="p-5 md:p-6 bg-gray-50 rounded-xl shadow-sm hover:shadow-lg transition duration-300">
                    <div class="h-10 w-10 md:h-12 md:w-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-3 md:mb-4">
                        <svg class="h-5 w-5 md:h-6 md:w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h3 class="text-lg md:text-xl font-semibold text-gray-900 mb-2">Smart Task Organization</h3>
                    <p class="text-sm md:text-base text-gray-600">Create, categorize, and prioritize tasks with intelligent workflows.</p>
                </div>
                <div class="p-5 md:p-6 bg-gray-50 rounded-xl shadow-sm hover:shadow-lg transition duration-300">
                    <div class="h-10 w-10 md:h-12 md:w-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-3 md:mb-4">
                        <svg class="h-5 w-5 md:h-6 md:w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg md:text-xl font-semibold text-gray-900 mb-2">Real-time Tracking</h3>
                    <p class="text-sm md:text-base text-gray-600">Set deadlines and get instant reminders for upcoming tasks.</p>
                </div>
                <div class="p-5 md:p-6 bg-gray-50 rounded-xl shadow-sm hover:shadow-lg transition duration-300">
                    <div class="h-10 w-10 md:h-12 md:w-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-3 md:mb-4">
                        <svg class="h-5 w-5 md:h-6 md:w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg md:text-xl font-semibold text-gray-900 mb-2">Analytics & Reports</h3>
                    <p class="text-sm md:text-base text-gray-600">Track productivity with detailed insights and performance metrics.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Simplified Footer -->
    <footer class="bg-gray-900 text-white py-8 md:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 md:gap-8 mb-6 md:mb-8">
                <!-- Brand Column -->
                <div>
                    <h3 class="text-lg md:text-xl font-bold mb-3 md:mb-4">Soft<span class="text-indigo-400">Niq</span></h3>
                    <p class="text-gray-400 text-xs md:text-sm leading-relaxed">
                        Innovative task management solutions for modern businesses.
                    </p>
                </div>

                <!-- Quick Links Column -->
                <div>
                    <h4 class="text-base md:text-lg font-semibold mb-3 md:mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-xs md:text-sm text-gray-400">
                        <li><Link href="/" class="hover:text-indigo-400 transition">Home</Link></li>
                        <li><a href="#features" class="hover:text-indigo-400 transition">Features</a></li>
                        <li><button @click="toggleTasks" class="hover:text-green-400 transition">My Tasks</button></li>
                    </ul>
                </div>

                <!-- Contact Column -->
                <div>
                    <h4 class="text-base md:text-lg font-semibold mb-3 md:mb-4">Contact</h4>
                    <ul class="space-y-2 text-xs md:text-sm text-gray-400">
                        <li class="flex items-start space-x-2">
                            <svg class="h-4 w-4 text-gray-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Naya Shorok, Sylhet</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <svg class="h-4 w-4 text-gray-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <a href="mailto:softniq@gmail.com" class="hover:text-indigo-400 transition">softniq@gmail.com</a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Copyright Section -->
            <div class="border-t border-gray-800 pt-6 md:pt-8 text-center text-xs md:text-sm text-gray-400">
                <p>&copy; 2026 SoftNiq. All rights reserved. | Empowering teams with smart task management</p>
            </div>
        </div>
    </footer>
</template>

<style scoped>
@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.fixed.top-24.right-4 {
    animation: slideIn 0.3s ease-out;
}

/* Task highlight animation */
.task-highlight {
    animation: highlightPulse 0.5s ease-in-out 3;
    background-color: rgb(34, 197, 94, 0.1) !important;
}

@keyframes highlightPulse {
    0% {
        background-color: rgb(34, 197, 94, 0);
    }
    50% {
        background-color: rgb(34, 197, 94, 0.3);
    }
    100% {
        background-color: rgb(34, 197, 94, 0);
    }
}

/* Responsive table styles */
@media (max-width: 640px) {
    .table-responsive {
        display: block;
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
}
</style>