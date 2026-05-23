<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log in - SoftNiq" />

    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50">
        <!-- Logo/Nav Bar -->
        <nav class="bg-white/80 backdrop-blur-sm shadow-sm fixed w-full z-10 top-0">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <Link href="/" class="flex items-center space-x-2">
                        <div class="h-8 w-8 bg-indigo-600 rounded-lg flex items-center justify-center">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <span class="font-bold text-xl text-gray-900">Soft<span class="text-indigo-600">Niq</span></span>
                    </Link>
                    <Link href="/" class="text-gray-600 hover:text-indigo-600 transition text-sm font-medium">
                        Back to Home
                    </Link>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="pt-20 pb-12 px-4 sm:px-6 lg:px-8 min-h-screen flex items-center justify-center">
            <div class="max-w-md w-full">
                <!-- Status Message -->
                <div v-if="status" class="mb-6 p-4 rounded-lg bg-green-50 border-l-4 border-green-500">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm text-green-700">{{ status }}</p>
                    </div>
                </div>

                <!-- Login Card -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-6">
                        <h2 class="text-2xl font-bold text-white">Welcome Back</h2>
                        <p class="text-indigo-100 text-sm mt-1">Sign in to continue to your account</p>
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="submit" class="px-8 py-6">
                        <!-- Email Field -->
                        <div class="mb-5">
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                                Email Address
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <input
                                    id="email"
                                    type="email"
                                    class="w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                                    :class="{ 'border-red-500 focus:ring-red-500': form.errors.email }"
                                    v-model="form.email"
                                    required
                                    autofocus
                                    autocomplete="username"
                                    placeholder="you@example.com"
                                >
                            </div>
                            <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                        </div>

                        <!-- Password Field -->
                        <div class="mb-5">
                            <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                                Password
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <input
                                    :type="showPassword ? 'text' : 'password'"
                                    id="password"
                                    class="w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                                    :class="{ 'border-red-500 focus:ring-red-500': form.errors.password }"
                                    v-model="form.password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="Enter your password"
                                >
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                >
                                    <svg v-if="!showPassword" class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg v-else class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                            <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
                        </div>

                        <!-- Remember Me & Forgot Password -->
                        <div class="flex items-center justify-between mb-6">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" v-model="form.remember" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-600">Remember me</span>
                            </label>
                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-sm text-indigo-600 hover:text-indigo-800 font-medium transition"
                            >
                                Forgot password?
                            </Link>
                        </div>

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold py-2.5 rounded-lg hover:from-indigo-700 hover:to-purple-700 transition duration-200 transform hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
                        >
                            <span v-if="!form.processing">Sign In</span>
                            <span v-else class="flex items-center justify-center">
                                <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Signing in...
                            </span>
                        </button>

                        <!-- Register Link -->
                        <div class="mt-6 text-center">
                            <p class="text-sm text-gray-600">
                                Don't have an account?
                                <Link :href="route('register')" class="text-indigo-600 hover:text-indigo-800 font-semibold transition">
                                    Create an account
                                </Link>
                            </p>
                        </div>
                    </form>
                </div>

                <!-- Demo Credentials (Optional - Remove in production) -->
                <div class="mt-6 bg-blue-50 rounded-lg p-4 border border-blue-100">
                    <p class="text-xs text-blue-800 font-medium mb-2">Demo Credentials (for testing):</p>
                    <div class="space-y-1 text-xs text-blue-700">
                        <p>📧 Email: demo@softniq.com</p>
                        <p>🔑 Password: password</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>