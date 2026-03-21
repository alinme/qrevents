<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

defineProps<{
    eventName: string;
    dashboardUrl: string;
}>();
</script>

<template>
    <Head title="You're all set" />

    <div
        class="grid min-h-screen place-items-center bg-neutral-50 px-4 dark:bg-neutral-950"
    >
        <Card class="w-full max-w-2xl">
            <CardHeader>
                <CardTitle class="text-2xl">You're all set up!</CardTitle>
                <CardDescription>
                    <span class="font-semibold text-foreground">{{
                        eventName
                    }}</span>
                    is ready. On the next screen you'll find your dashboard with
                    album links, QR sharing, and slideshow access.
                </CardDescription>
            </CardHeader>

            <CardContent>
                <Button as-child>
                    <Link :href="dashboardUrl">Go to your dashboard</Link>
                </Button>
            </CardContent>
        </Card>
    </div>
</template>
