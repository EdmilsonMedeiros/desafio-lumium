<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head title="Bem-vindo" />
    
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-cyan-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
        <!-- Header/Navigation -->
        <header class="relative z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <!-- Logo/Brand -->
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                <span class="text-indigo-600 dark:text-indigo-400">Desafio Lumium</span>
                            </h1>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <nav v-if="canLogin" class="flex items-center space-x-4">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            Dashboard
                        </Link>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="text-gray-700 dark:text-gray-200 hover:text-indigo-600 dark:hover:text-indigo-400 px-3 py-2 rounded-md text-sm font-medium transition duration-300"
                            >
                                Entrar
                            </Link>

                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Registrar
                            </Link>
                        </template>
                    </nav>
                </div>
            </div>
                </header>

        <!-- Hero Section -->
        <main>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-16 text-center lg:pt-32">
                <h1 class="mx-auto max-w-4xl font-display text-5xl font-medium tracking-tight text-slate-900 dark:text-white sm:text-7xl">
                    Bem-vindo
                </h1>
            </div>
        </main>
    </div>
</template>
