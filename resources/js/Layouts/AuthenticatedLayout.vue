<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex">

        <!-- Sidebar -->
        <aside class="w-64 bg-gray-900 text-white min-h-screen flex flex-col">

            <div class="p-6 text-2xl font-bold border-b border-gray-700">
                Task Manager
            </div>

            <nav class="flex-1 mt-5 px-4 space-y-2">

                <Link 
                    href="/dashboard" 
                    class="block px-4 py-3 rounded-lg hover:bg-gray-800 transition-colors duration-200"
                    :class="{ 'bg-gray-800': $page.url === '/dashboard' }"
                >
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span>Dashboard</span>
                    </div>
                </Link>

                <Link 
                    href="/create-task" 
                    class="block px-4 py-3 rounded-lg hover:bg-gray-800 transition-colors duration-200"
                    :class="{ 'bg-gray-800': $page.url === '/create-task' }"
                >
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Create Task</span>
                    </div>
                </Link>

                <!-- <Link 
                    href="/all-tasks" 
                    class="block px-4 py-3 rounded-lg hover:bg-gray-800 transition-colors duration-200"
                    :class="{ 'bg-gray-800': $page.url === '/all-tasks' }"
                >
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <span>All Tasks</span>
                    </div>
                </Link> -->

                <Link 
                    href="/manage-users" 
                    class="block px-4 py-3 rounded-lg hover:bg-gray-800 transition-colors duration-200"
                    :class="{ 'bg-gray-800': $page.url === '/manage-users' }"
                >
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <span>Manage Users</span>
                    </div>
                </Link>

            </nav>

        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">

            <!-- Top Navbar with Profile Dropdown -->
            <nav class="bg-white shadow px-6 py-4 flex justify-between items-center">
                <h1 class="text-xl font-semibold text-gray-800">
                    {{ $page.component }}
                </h1>
                
                <!-- User Profile Dropdown in Navbar -->
                <div class="relative">
                    <button 
                        @click="showingNavigationDropdown = !showingNavigationDropdown"
                        class="flex items-center space-x-3 focus:outline-none hover:bg-gray-50 rounded-lg px-3 py-2 transition-colors duration-200"
                    >
                        <div class="h-10 w-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-semibold">
                            {{ $page.props.auth.user?.name?.charAt(0).toUpperCase() || 'U' }}
                        </div>
                        <div class="hidden md:block text-left">
                            <div class="text-sm font-medium text-gray-700">
                                {{ $page.props.auth.user?.name || 'User' }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $page.props.auth.user?.email || '' }}
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div 
                        v-show="showingNavigationDropdown"
                        class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 border"
                        @click.away="showingNavigationDropdown = false"
                    >
                        <Link 
                            :href="route('profile.edit')"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                            @click="showingNavigationDropdown = false"
                        >
                            <div class="flex items-center space-x-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span>My Profile</span>
                            </div>
                        </Link>
                        
                        <hr class="my-1">
                        
                        <button 
                            @click="logout"
                            class="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
                        >
                            <div class="flex items-center space-x-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                <span>Logout</span>
                            </div>
                        </button>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main class="flex-1 p-6">
                <div class="max-w-7xl mx-auto">
                    <slot />
                </div>
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t mt-auto py-4 px-6">
                <div class="text-center text-sm text-gray-600">
                    &copy; 2026 Task Manager. All rights reserved.
                </div>
            </footer>

        </div>
    </div>
</template>

<style scoped>
.router-link-active {
    background-color: #374151;
}

/* Dropdown animation */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>