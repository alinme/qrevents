<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    canRegister: boolean;
}>();
</script>

<template>
    <Head title="EventSmart" />

    <main class="flex min-h-screen items-center justify-center bg-white px-6 text-zinc-950">
        <nav class="flex items-center gap-6 text-sm font-medium">
            <Link href="/login" class="text-zinc-700 hover:text-zinc-950">
                Login
            </Link>

            <Link
                v-if="canRegister"
                href="/register"
                class="text-zinc-700 hover:text-zinc-950"
            >
                Register
            </Link>
        </nav>
    </main>
</template>
