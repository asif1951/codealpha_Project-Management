<template>
    <Head title="Task Management" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-indigo-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Total Tasks</p>
                                <p class="text-2xl font-bold text-gray-800">{{ tasks.length }}</p>
                            </div>
                            <div class="bg-indigo-100 rounded-full p-3">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Pending</p>
                                <p class="text-2xl font-bold text-gray-800">{{ getTasksByStatus('pending') }}</p>
                            </div>
                            <div class="bg-yellow-100 rounded-full p-3">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">In Progress</p>
                                <p class="text-2xl font-bold text-gray-800">{{ getTasksByStatus('in_progress') }}</p>
                            </div>
                            <div class="bg-blue-100 rounded-full p-3">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Completed</p>
                                <p class="text-2xl font-bold text-gray-800">{{ getTasksByStatus('completed') }}</p>
                            </div>
                            <div class="bg-green-100 rounded-full p-3">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Header -->
                        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
                            <h2 class="text-2xl font-bold text-gray-800">📋 Task List</h2>
                            <button 
                                @click="openCreateModal"
                                class="bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-medium py-2.5 px-5 rounded-lg transition duration-200 transform hover:scale-105 shadow-md"
                            >
                                <span class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Create New Task
                                </span>
                            </button>
                        </div>

                        <!-- Search Bar -->
                        <div class="mb-6">
                            <div class="relative">
                                <input 
                                    type="text" 
                                    v-model="searchQuery"
                                    placeholder="Search tasks by title..." 
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                />
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Tasks Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Title</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Description</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Due Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Assigned To</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="task in filteredTasks" :key="task.id" class="hover:bg-gray-50 transition duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ task.title }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-500 max-w-xs truncate">{{ task.description || '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">{{ formatDate(task.due_date) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center mr-2">
                                                    <span class="text-indigo-600 font-medium text-sm">{{ getInitials(task.assigned_user?.name) }}</span>
                                                </div>
                                                <div class="text-sm font-medium text-gray-900">{{ task.assigned_user?.name || 'N/A' }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <select 
                                                :value="task.status"
                                                @change="updateStatus(task.id, $event.target.value)"
                                                :class="`px-3 py-1 text-xs font-semibold rounded-full cursor-pointer transition duration-200 ${getStatusColor(task.status)}`"
                                            >
                                                <option value="pending" class="text-yellow-800">Pending</option>
                                                <option value="in_progress" class="text-blue-800">In Progress</option>
                                                <option value="completed" class="text-green-800">Completed</option>
                                            </select>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                                            <button 
                                                @click="openEditModal(task)"
                                                class="text-indigo-600 hover:text-indigo-900 transition duration-150"
                                            >
                                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                Edit
                                            </button>
                                            <button 
                                                @click="deleteTask(task.id)"
                                                class="text-red-600 hover:text-red-900 transition duration-150"
                                            >
                                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="filteredTasks.length === 0">
                                        <td colspan="6" class="px-6 py-12 text-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                            </svg>
                                            <p class="mt-2 text-sm text-gray-500">No tasks found. Click "Create New Task" to get started.</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeModal">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal"></div>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4">
                        <h3 class="text-lg leading-6 font-medium text-white">
                            {{ isEditing ? '✏️ Edit Task' : '✨ Create New Task' }}
                        </h3>
                    </div>
                    
                    <form @submit.prevent="submitForm">
                        <div class="bg-white px-6 pt-5 pb-4">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                                    <input 
                                        type="text" 
                                        v-model="form.title"
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                        required
                                        placeholder="Enter task title"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                    <textarea 
                                        v-model="form.description"
                                        rows="3"
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                        placeholder="Enter task description"
                                    ></textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Due Date *</label>
                                    <input 
                                        type="date" 
                                        v-model="form.due_date"
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                        required
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Assign To *</label>
                                    <select 
                                        v-model="form.assigned_to"
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                        required
                                    >
                                        <option value="">Select a user</option>
                                        <option v-for="user in users" :key="user.id" :value="user.id">
                                            {{ user.name }}
                                        </option>
                                    </select>
                                </div>

                                <div v-if="isEditing">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                    <select 
                                        v-model="form.status"
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    >
                                        <option value="pending">Pending</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="completed">Completed</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-3 flex justify-end space-x-3">
                            <button 
                                type="button"
                                @click="closeModal"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-100 transition duration-150"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit"
                                :disabled="isSubmitting"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition duration-150 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {{ isSubmitting ? 'Processing...' : (isEditing ? 'Update Task' : 'Create Task') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Toast Notification - Top Right Corner -->
        <div v-if="toast.show" 
             :class="[
                 'fixed top-4 right-4 z-50 flex items-center p-4 rounded-lg shadow-lg transition-all duration-300 transform',
                 toast.type === 'success' ? 'bg-green-500' : 'bg-red-500',
                 toast.show ? 'translate-x-0 opacity-100' : 'translate-x-full opacity-0'
             ]"
             style="min-width: 280px;"
             role="alert">
            <div class="flex items-center flex-1">
                <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg bg-white bg-opacity-30">
                    <svg v-if="toast.type === 'success'" class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <svg v-else class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3 text-sm font-normal text-white">{{ toast.message }}</div>
            </div>
            <button @click="toast.show = false" class="ml-4 text-white hover:text-gray-200 focus:outline-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    tasks: {
        type: Array,
        default: () => []
    },
    users: {
        type: Array,
        default: () => []
    }
});

// Local reactive tasks array
const localTasks = ref([...props.tasks]);

// Watch for props changes
const tasks = computed({
    get: () => localTasks.value,
    set: (value) => { localTasks.value = value; }
});

// Toast state
const toast = ref({
    show: false,
    message: '',
    type: 'success'
});

// Show toast notification
const showToast = (message, type = 'success') => {
    toast.value.message = message;
    toast.value.type = type;
    toast.value.show = true;
    
    setTimeout(() => {
        toast.value.show = false;
    }, 3000);
};

// Search functionality
const searchQuery = ref('');

// Submitting state
const isSubmitting = ref(false);

// Filtered tasks based on search
const filteredTasks = computed(() => {
    if (!searchQuery.value) return localTasks.value;
    return localTasks.value.filter(task => 
        task.title.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

// Get tasks by status for stats
const getTasksByStatus = (status) => {
    return localTasks.value.filter(task => task.status === status).length;
};

// Get user initials for avatar
const getInitials = (name) => {
    if (!name) return '?';
    return name.charAt(0).toUpperCase();
};

// Format date for input field (YYYY-MM-DD)
const formatDateForInput = (date) => {
    if (!date) return '';
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

// Modal state
const showModal = ref(false);
const isEditing = ref(false);
const editingTaskId = ref(null);

// Form state
const form = ref({
    title: '',
    description: '',
    due_date: '',
    assigned_to: '',
    status: 'pending'
});

// Open modal for create
const openCreateModal = () => {
    isEditing.value = false;
    editingTaskId.value = null;
    form.value = {
        title: '',
        description: '',
        due_date: '',
        assigned_to: '',
        status: 'pending'
    };
    showModal.value = true;
};

// Open modal for edit
const openEditModal = (task) => {
    isEditing.value = true;
    editingTaskId.value = task.id;
    form.value = {
        title: task.title,
        description: task.description || '',
        due_date: formatDateForInput(task.due_date),
        assigned_to: task.assigned_to,
        status: task.status
    };
    showModal.value = true;
};

// Close modal
const closeModal = () => {
    showModal.value = false;
    form.value = {
        title: '',
        description: '',
        due_date: '',
        assigned_to: '',
        status: 'pending'
    };
    isEditing.value = false;
    editingTaskId.value = null;
};

// Add new task to local array
const addTaskLocally = (newTask) => {
    localTasks.value.unshift(newTask); // Add to beginning of array
};

// Update task in local array
const updateTaskLocally = (updatedTask) => {
    const index = localTasks.value.findIndex(task => task.id === updatedTask.id);
    if (index !== -1) {
        localTasks.value[index] = updatedTask;
    }
};

// Remove task from local array
const removeTaskLocally = (taskId) => {
    const index = localTasks.value.findIndex(task => task.id === taskId);
    if (index !== -1) {
        localTasks.value.splice(index, 1);
    }
};

// Submit form
const submitForm = async () => {
    isSubmitting.value = true;
    try {
        if (isEditing.value) {
            const response = await axios.put(`/tasks/${editingTaskId.value}`, form.value);
            showToast(response.data.message, 'success');
            closeModal();
            // Update the task in local array
            updateTaskLocally(response.data.task);
        } else {
            const response = await axios.post('/tasks', form.value);
            showToast(response.data.message, 'success');
            closeModal();
            // Add the new task to local array
            addTaskLocally(response.data.task);
        }
    } catch (error) {
        if (error.response?.data?.errors) {
            Object.values(error.response.data.errors).forEach(err => {
                showToast(err[0], 'error');
            });
        } else {
            showToast(error.response?.data?.message || 'Something went wrong', 'error');
        }
    } finally {
        isSubmitting.value = false;
    }
};

// Delete task
const deleteTask = async (id) => {
    if (confirm('Are you sure you want to delete this task?')) {
        try {
            const response = await axios.delete(`/tasks/${id}`);
            showToast(response.data.message, 'success');
            // Remove task from local array
            removeTaskLocally(id);
        } catch (error) {
            showToast('Failed to delete task', 'error');
        }
    }
};

// Update status
const updateStatus = async (id, newStatus) => {
    try {
        const response = await axios.patch(`/tasks/${id}/status`, { status: newStatus });
        showToast(response.data.message, 'success');
        // Update the task status in local array
        const task = localTasks.value.find(t => t.id === id);
        if (task) {
            task.status = newStatus;
            updateTaskLocally(task);
        }
    } catch (error) {
        showToast('Failed to update status', 'error');
    }
};

// Get status badge color
const getStatusColor = (status) => {
    switch(status) {
        case 'pending': return 'bg-yellow-100 text-yellow-800 border border-yellow-200';
        case 'in_progress': return 'bg-blue-100 text-blue-800 border border-blue-200';
        case 'completed': return 'bg-green-100 text-green-800 border border-green-200';
        default: return 'bg-gray-100 text-gray-800';
    }
};

// Format date for display
const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>

<style scoped>
input, textarea, select {
    @apply border-gray-300 focus:ring-indigo-500 focus:border-indigo-500;
}

input:focus, textarea:focus, select:focus {
    outline: none;
}
</style>