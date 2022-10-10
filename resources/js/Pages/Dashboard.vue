<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/inertia-vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <aside class="bg-white mt-4 ml-2 w-6/12">
            <ul class="ml-4">
                <li class="mb-4"> <Link :href="route('ins-pos')">Inserimento Libri</Link> </li>
                <li class="mb-4"> <Link :href="route('consultazione')">Consultazione Libri</Link> </li>
            </ul>
        </aside>
    </AuthenticatedLayout>
</template>
