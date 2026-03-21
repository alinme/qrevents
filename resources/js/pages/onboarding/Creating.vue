<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';

const props = defineProps<{
    eventName: string;
    nextUrl: string;
}>();

onMounted(() => {
    window.setTimeout(() => {
        router.visit(props.nextUrl);
    }, 1800);
});
</script>

<template>
    <Head title="Creating your album" />

    <div
        class="grid min-h-screen place-items-center bg-neutral-50 px-4 dark:bg-neutral-950"
    >
        <Card class="w-full max-w-lg">
            <CardHeader class="text-center">
                <CardTitle class="text-xl">Creating your album...</CardTitle>
                <CardDescription>
                    Preparing {{ eventName }} and generating your QR sharing
                    link.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Spinner class="mx-auto" />
            </CardContent>
        </Card>
    </div>
</template>
