<!-- resources/js/Pages/ManageUsers.vue -->

<script setup>
import { ref, computed } from 'vue';
import { router, useForm, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    users: Array,
    currentUser: Object
});

// Form for creating user
const createForm = useForm({
    name: '',
    email: '',
    password: '',
    role: 'user'
});

// Form for editing user
const editForm = useForm({
    id: null,
    name: '',
    email: '',
    password: '',
    role: 'user'
});

// Modal states
const showCreateModal = ref(false);
const showEditModal = ref(false);

// Toast notification state
const toast = ref({
    show: false,
    message: '',
    type: 'success' // success, error, warning
});

let toastTimeout = null;

// Show toast notification
const showToast = (message, type = 'success') => {
    // Clear existing timeout
    if (toastTimeout) {
        clearTimeout(toastTimeout);
    }
    
    toast.value = {
        show: true,
        message: message,
        type: type
    };
    
    // Auto hide after 3 seconds
    toastTimeout = setTimeout(() => {
        toast.value.show = false;
    }, 3000);
};

// Close toast manually
const closeToast = () => {
    toast.value.show = false;
    if (toastTimeout) {
        clearTimeout(toastTimeout);
    }
};

// Check if current user is admin
const isAdmin = computed(() => props.currentUser?.role === 'admin');

// Toggle user role
const toggleRole = (user) => {
    const newRole = user.role === 'admin' ? 'user' : 'admin';
    const action = newRole === 'admin' ? 'make admin' : 'remove admin rights';
    
    if (confirm(`Are you sure you want to ${action} for ${user.name}?`)) {
        router.patch(route('users.toggle-role', user.id), {
            preserveScroll: true,
            onSuccess: () => {
                showToast(`User ${newRole === 'admin' ? 'promoted to admin' : 'demoted to user'} successfully!`, 'success');
            },
            onError: (errors) => {
                showToast(errors.error || 'Unable to change user role.', 'error');
            }
        });
    }
};

// Create user
const createUser = () => {
    createForm.post(route('users.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
            showToast('User created successfully!', 'success');
        },
        onError: (errors) => {
            showToast('Please check your input and try again.', 'error');
        }
    });
};

// Edit user
const editUser = (user) => {
    editForm.id = user.id;
    editForm.name = user.name;
    editForm.email = user.email;
    editForm.password = '';
    editForm.role = user.role;
    showEditModal.value = true;
};

// Update user
const updateUser = () => {
    editForm.put(route('users.update', editForm.id), {
        preserveScroll: true,
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
            showToast('User updated successfully!', 'success');
        },
        onError: (errors) => {
            showToast('Please check your input and try again.', 'error');
        }
    });
};

// Delete user
const deleteUser = (user) => {
    if (user.id === props.currentUser?.id) {
        showToast('You cannot delete your own account!', 'warning');
        return;
    }
    
    if (confirm(`Are you sure you want to delete ${user.name}?`)) {
        router.delete(route('users.destroy', user.id), {
            preserveScroll: true,
            onSuccess: () => {
                showToast('User deleted successfully!', 'success');
            },
            onError: (errors) => {
                showToast(errors.error || 'Unable to delete user.', 'error');
            }
        });
    }
};

// Close modals
const closeModals = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    createForm.reset();
    editForm.reset();
};

// Get role badge class
const getRoleBadgeClass = (role) => {
    return role === 'admin' 
        ? 'bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-xs font-semibold'
        : 'bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs font-semibold';
};

// Get toast background color
const getToastBgColor = (type) => {
    switch(type) {
        case 'success':
            return 'bg-green-500';
        case 'error':
            return 'bg-red-500';
        case 'warning':
            return 'bg-yellow-500';
        default:
            return 'bg-blue-500';
    }
};
</script>

<template>
    <Head title="Manage Users" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Header -->
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-gray-900">Manage Users</h2>
                            <button 
                                v-if="isAdmin"
                                @click="showCreateModal = true"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                            >
                                Add New User
                            </button>
                        </div>

                        <!-- Users Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-300">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="border px-4 py-2 text-left">ID</th>
                                        <th class="border px-4 py-2 text-left">Name</th>
                                        <th class="border px-4 py-2 text-left">Email</th>
                                        <th class="border px-4 py-2 text-left">Role</th>
                                        <th class="border px-4 py-2 text-left">Created At</th>
                                        <th class="border px-4 py-2 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
                                        <td class="border px-4 py-2">{{ user.id }}</td>
                                        <td class="border px-4 py-2">
                                            {{ user.name }}
                                            <span v-if="user.id === currentUser?.id" class="text-xs text-blue-500 ml-2">(You)</span>
                                        </td>
                                        <td class="border px-4 py-2">{{ user.email }}</td>
                                        <td class="border px-4 py-2">
                                            <span :class="getRoleBadgeClass(user.role)">
                                                {{ user.role === 'admin' ? 'Admin' : 'User' }}
                                            </span>
                                        </td>
                                        <td class="border px-4 py-2">{{ new Date(user.created_at).toLocaleDateString() }}</td>
                                        <td class="border px-4 py-2 text-center">
                                            <!-- Edit Button -->
                                            <button 
                                                v-if="isAdmin"
                                                @click="editUser(user)"
                                                class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-3 rounded mr-2"
                                            >
                                                Edit
                                            </button>
                                            
                                            <!-- Toggle Role Button (Admin only) -->
                                            <button 
                                                v-if="isAdmin && user.id !== currentUser?.id"
                                                @click="toggleRole(user)"
                                                :class="user.role === 'admin' 
                                                    ? 'bg-orange-500 hover:bg-orange-700' 
                                                    : 'bg-green-500 hover:bg-green-700'"
                                                class="text-white font-bold py-1 px-3 rounded mr-2"
                                            >
                                                {{ user.role === 'admin' ? 'Remove Admin' : 'Make Admin' }}
                                            </button>
                                            
                                            <!-- Delete Button -->
                                            <button 
                                                v-if="isAdmin && user.id !== currentUser?.id"
                                                @click="deleteUser(user)"
                                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded"
                                            >
                                                Delete
                                            </button>
                                            
                                            <span v-if="!isAdmin && user.id !== currentUser?.id" class="text-gray-400 text-sm">
                                                No actions available
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="users.length === 0">
                                        <td colspan="6" class="border px-4 py-8 text-center text-gray-500">
                                            No users found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create User Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">Create New User</h3>
                    <button @click="closeModals" class="text-gray-600 hover:text-gray-900 text-2xl">&times;</button>
                </div>
                
                <form @submit.prevent="createUser">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                            Name
                        </label>
                        <input 
                            id="name"
                            type="text"
                            v-model="createForm.name"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            :class="{ 'border-red-500': createForm.errors.name }"
                        >
                        <p v-if="createForm.errors.name" class="text-red-500 text-xs mt-1">{{ createForm.errors.name }}</p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                            Email
                        </label>
                        <input 
                            id="email"
                            type="email"
                            v-model="createForm.email"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            :class="{ 'border-red-500': createForm.errors.email }"
                        >
                        <p v-if="createForm.errors.email" class="text-red-500 text-xs mt-1">{{ createForm.errors.email }}</p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                            Password
                        </label>
                        <input 
                            id="password"
                            type="password"
                            v-model="createForm.password"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            :class="{ 'border-red-500': createForm.errors.password }"
                        >
                        <p v-if="createForm.errors.password" class="text-red-500 text-xs mt-1">{{ createForm.errors.password }}</p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="role">
                            Role
                        </label>
                        <select 
                            id="role"
                            v-model="createForm.role"
                            class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        >
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    
                    <div class="flex justify-end gap-2">
                        <button 
                            type="button"
                            @click="closeModals"
                            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit"
                            :disabled="createForm.processing"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                            :class="{ 'opacity-50 cursor-not-allowed': createForm.processing }"
                        >
                            {{ createForm.processing ? 'Creating...' : 'Create User' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Edit User Modal -->
        <div v-if="showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">Edit User</h3>
                    <button @click="closeModals" class="text-gray-600 hover:text-gray-900 text-2xl">&times;</button>
                </div>
                
                <form @submit.prevent="updateUser">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_name">
                            Name
                        </label>
                        <input 
                            id="edit_name"
                            type="text"
                            v-model="editForm.name"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            :class="{ 'border-red-500': editForm.errors.name }"
                        >
                        <p v-if="editForm.errors.name" class="text-red-500 text-xs mt-1">{{ editForm.errors.name }}</p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_email">
                            Email
                        </label>
                        <input 
                            id="edit_email"
                            type="email"
                            v-model="editForm.email"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            :class="{ 'border-red-500': editForm.errors.email }"
                        >
                        <p v-if="editForm.errors.email" class="text-red-500 text-xs mt-1">{{ editForm.errors.email }}</p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_password">
                            Password <span class="text-gray-500 text-xs">(Leave blank to keep current)</span>
                        </label>
                        <input 
                            id="edit_password"
                            type="password"
                            v-model="editForm.password"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            :class="{ 'border-red-500': editForm.errors.password }"
                        >
                        <p v-if="editForm.errors.password" class="text-red-500 text-xs mt-1">{{ editForm.errors.password }}</p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_role">
                            Role
                        </label>
                        <select 
                            id="edit_role"
                            v-model="editForm.role"
                            class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            :disabled="editForm.id === currentUser?.id"
                        >
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                        <p v-if="editForm.id === currentUser?.id" class="text-xs text-gray-500 mt-1">
                            You cannot change your own role here.
                        </p>
                    </div>
                    
                    <div class="flex justify-end gap-2">
                        <button 
                            type="button"
                            @click="closeModals"
                            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit"
                            :disabled="editForm.processing"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                            :class="{ 'opacity-50 cursor-not-allowed': editForm.processing }"
                        >
                            {{ editForm.processing ? 'Updating...' : 'Update User' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Toast Notification -->
        <transition
            enter-active-class="transition ease-out duration-300 transform"
            enter-from-class="translate-x-full opacity-0"
            enter-to-class="translate-x-0 opacity-100"
            leave-active-class="transition ease-in duration-300 transform"
            leave-from-class="translate-x-0 opacity-100"
            leave-to-class="translate-x-full opacity-0"
        >
            <div v-if="toast.show" class="fixed top-4 right-4 z-50 w-96">
                <div :class="getToastBgColor(toast.type)" class="rounded-lg shadow-lg overflow-hidden">
                    <div class="p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <!-- Success Icon -->
                                <svg v-if="toast.type === 'success'" class="w-5 h-5 text-white mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <!-- Error Icon -->
                                <svg v-if="toast.type === 'error'" class="w-5 h-5 text-white mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <!-- Warning Icon -->
                                <svg v-if="toast.type === 'warning'" class="w-5 h-5 text-white mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <p class="text-white font-medium">{{ toast.message }}</p>
                            </div>
                            <button @click="closeToast" class="text-white hover:text-gray-200 focus:outline-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <!-- Progress bar -->
                    <div class="h-1 bg-white bg-opacity-30">
                        <div class="h-full bg-white animate-progress" style="width: 100%; animation: progress 3s linear forwards;"></div>
                    </div>
                </div>
            </div>
        </transition>
    </AuthenticatedLayout>
</template>

<style scoped>
@keyframes progress {
    0% {
        width: 100%;
    }
    100% {
        width: 0%;
    }
}

.animate-progress {
    animation: progress 3s linear forwards;
}
</style>