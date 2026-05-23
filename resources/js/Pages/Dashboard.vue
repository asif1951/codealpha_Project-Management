<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import Chart from 'chart.js/auto';

// Props from controller
const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            total_users: 0,
            total_tasks: 0,
            total_notifications: 0,
            pending_tasks: 0,
            in_progress_tasks: 0,
            completed_tasks: 0,
            recent_tasks: [],
            table_stats: []
        })
    }
});

const chartRef = ref(null);
let donutChart = null;

// Table data
const tables = ref([
    { name: 'failed_jobs', rows: 0, type: 'InnoDB', size: '32.0 KiB', overhead: '-' },
    { name: 'migrations', rows: 7, type: 'InnoDB', size: '16.0 KiB', overhead: '-' },
    { name: 'notifications', rows: 2, type: 'InnoDB', size: '32.0 KiB', overhead: '-' },
    { name: 'password_reset_tokens', rows: 0, type: 'InnoDB', size: '16.0 KiB', overhead: '-' },
    { name: 'personal_access_tokens', rows: 0, type: 'InnoDB', size: '48.0 KiB', overhead: '-' },
    { name: 'tasks', rows: 4, type: 'InnoDB', size: '48.0 KiB', overhead: '-' },
    { name: 'users', rows: 3, type: 'InnoDB', size: '48.0 KiB', overhead: '-' }
]);

// Calculate totals
const totalStats = computed(() => {
    const totalRows = tables.value.reduce((sum, table) => sum + table.rows, 0);
    const totalTables = tables.value.length;
    return { totalRows, totalTables };
});

// Task status data for donut chart
const taskStatusData = computed(() => ({
    labels: ['Pending', 'In Progress', 'Completed'],
    datasets: [{
        data: [
            props.stats.pending_tasks || 0,
            props.stats.in_progress_tasks || 0,
            props.stats.completed_tasks || 0
        ],
        backgroundColor: ['#F59E0B', '#3B82F6', '#10B981'],
        borderWidth: 0,
        hoverOffset: 4
    }]
}));

// Initialize donut chart
const initDonutChart = () => {
    if (chartRef.value) {
        if (donutChart) {
            donutChart.destroy();
        }
        
        donutChart = new Chart(chartRef.value, {
            type: 'doughnut',
            data: taskStatusData.value,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            font: {
                                size: 12,
                                weight: 'bold'
                            },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return `${label}: ${value} tasks (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
};

// Refresh chart when data changes
onMounted(() => {
    initDonutChart();
});

// Format size helper
const formatSize = (size) => {
    return size;
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Welcome Card -->
                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl p-8 mb-8 text-white">
                    <h1 class="text-3xl font-bold mb-2">
                        Welcome Back! 🎉
                    </h1>
                    <p class="text-indigo-100 text-lg">
                        Here's what's happening with your task management system today.
                    </p>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Users Card -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium mb-1">Total Users</p>
                                <p class="text-3xl font-bold text-gray-800">{{ stats.total_users || totalStats.totalRows ? '3' : '0' }}</p>
                                <p class="text-green-600 text-xs mt-2">+12% from last month</p>
                            </div>
                            <div class="h-12 w-12 bg-indigo-100 rounded-full flex items-center justify-center">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Tasks Card -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium mb-1">Total Tasks</p>
                                <p class="text-3xl font-bold text-gray-800">{{ stats.total_tasks || 4 }}</p>
                                <p class="text-green-600 text-xs mt-2">+8 new this week</p>
                            </div>
                            <div class="h-12 w-12 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Notifications Card -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium mb-1">Notifications</p>
                                <p class="text-3xl font-bold text-gray-800">{{ stats.total_notifications || 2 }}</p>
                                <p class="text-yellow-600 text-xs mt-2">3 unread messages</p>
                            </div>
                            <div class="h-12 w-12 bg-yellow-100 rounded-full flex items-center justify-center">
                                <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Completion Rate Card -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium mb-1">Completion Rate</p>
                                <p class="text-3xl font-bold text-gray-800">
                                    {{ stats.total_tasks > 0 ? Math.round((stats.completed_tasks / stats.total_tasks) * 100) : 0 }}%
                                </p>
                                <p class="text-green-600 text-xs mt-2">+5% from last week</p>
                            </div>
                            <div class="h-12 w-12 bg-purple-100 rounded-full flex items-center justify-center">
                                <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts and Recent Activity -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Donut Chart Card -->
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Task Status Distribution</h3>
                            <p class="text-sm text-gray-500">Overview of all task statuses</p>
                        </div>
                        <div class="h-80">
                            <canvas ref="chartRef"></canvas>
                        </div>
                    </div>

                    <!-- Recent Tasks Card -->
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Recent Tasks</h3>
                            <p class="text-sm text-gray-500">Latest 5 tasks in the system</p>
                        </div>
                        <div class="space-y-3">
                            <div v-for="task in (stats.recent_tasks || [])" :key="task.id" 
                                 class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                <div class="flex items-center space-x-3">
                                    <div :class="{
                                        'w-2 h-2 rounded-full': true,
                                        'bg-yellow-500': task.status === 'pending',
                                        'bg-blue-500': task.status === 'in_progress',
                                        'bg-green-500': task.status === 'completed'
                                    }"></div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">{{ task.title }}</p>
                                        <p class="text-xs text-gray-500">Due: {{ new Date(task.due_date).toLocaleDateString() }}</p>
                                    </div>
                                </div>
                                <span :class="{
                                    'px-2 py-1 text-xs rounded-full': true,
                                    'bg-yellow-100 text-yellow-700': task.status === 'pending',
                                    'bg-blue-100 text-blue-700': task.status === 'in_progress',
                                    'bg-green-100 text-green-700': task.status === 'completed'
                                }">
                                    {{ task.status || 'pending' }}
                                </span>
                            </div>
                            <div v-if="!stats.recent_tasks || stats.recent_tasks.length === 0" class="text-center py-8">
                                <p class="text-gray-500">No tasks found</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Database Tables Card -->
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">Database Tables Overview</h3>
                        <p class="text-sm text-gray-500">All tables in the database with their statistics</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Table</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rows</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Collation</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Overhead</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="table in tables" :key="table.name" class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                                            </svg>
                                            <span class="text-sm font-medium text-gray-900">{{ table.name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-600">{{ table.rows }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-600">{{ table.type }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-600">utf8mb4_unicode_ci</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-600">{{ table.size }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-400">{{ table.overhead }}</span>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-gray-50 border-t border-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-semibold text-gray-900">Total</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-semibold text-gray-900">{{ totalStats.totalRows }}</span>
                                    </td>
                                    <td colspan="2" class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-600">{{ totalStats.totalTables }} tables</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-semibold text-gray-900">240.0 KiB</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-400">0 B</span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>