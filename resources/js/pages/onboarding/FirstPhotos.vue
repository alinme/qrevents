<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Download, ExternalLink } from 'lucide-vue-next';
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
    albumUrl: string;
    wallUrl: string;
    qrCodeDataUrl: string;
    readyUrl: string;
}>();
</script>

<template>
    <Head title="Add your first photos" />

    <div class="min-h-screen bg-neutral-50 px-4 py-12 dark:bg-neutral-950">
        <div class="mx-auto w-full max-w-4xl">
            <Card>
                <CardHeader>
                    <CardTitle class="text-2xl"
                        >Let's add your first photos 🤳</CardTitle
                    >
                    <CardDescription>
                        Scan the QR code and follow the instructions to add new
                        photos. The photos will appear on your wall shortly
                        after.
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <div class="grid gap-8 md:grid-cols-2">
                        <div class="rounded-2xl border bg-background p-4">
                            <img
                                :src="qrCodeDataUrl"
                                alt="Album QR code"
                                class="mx-auto w-full max-w-[320px]"
                            />
                        </div>

                        <div class="space-y-4">
                            <p class="text-sm text-muted-foreground">
                                Event:
                                <span class="font-semibold text-foreground">{{
                                    eventName
                                }}</span>
                            </p>

                            <div class="flex flex-col gap-2">
                                <Button as-child variant="outline">
                                    <a
                                        :href="albumUrl"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <ExternalLink class="mr-2 size-4" />
                                        Open Digital Album
                                    </a>
                                </Button>
                                <Button as-child variant="outline">
                                    <a
                                        :href="wallUrl"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <ExternalLink class="mr-2 size-4" />
                                        Open Photo Wall
                                    </a>
                                </Button>
                                <Button as-child>
                                    <a
                                        :href="qrCodeDataUrl"
                                        download="digital-album-qr.svg"
                                    >
                                        <Download class="mr-2 size-4" />
                                        Download QR Code
                                    </a>
                                </Button>
                            </div>

                            <Button as-child variant="ghost" class="px-0">
                                <Link :href="readyUrl">I'll do it later</Link>
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
