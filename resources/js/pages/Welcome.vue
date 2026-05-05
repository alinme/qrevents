<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    Camera,
    Images,
    MonitorPlay,
    QrCode,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { dashboard, login, register } from '@/routes';

const props = defineProps<{
    canRegister: boolean;
}>();

const page = usePage<{
    auth?: {
        user?: {
            name?: string;
        } | null;
    };
}>();

const isAuthenticated = computed(() => Boolean(page.props.auth?.user));
const primaryLink = computed(() =>
    isAuthenticated.value
        ? dashboard()
        : props.canRegister
          ? register()
          : login(),
);
const primaryLabel = computed(() =>
    isAuthenticated.value
        ? 'Open dashboard'
        : props.canRegister
          ? 'Create album'
          : 'Login',
);

const previewTiles = [
    'bg-[#f8dfe6]',
    'bg-[#dceeff]',
    'bg-[#f5f0dd]',
    'bg-[#dff7ef]',
    'bg-[#ebe5ff]',
    'bg-[#ffe3d7]',
];
</script>

<template>
    <Head title="EventSmart" />

    <main
        class="relative min-h-screen overflow-hidden bg-[#080b12] px-5 py-5 text-white sm:px-7 lg:px-10"
    >
        <img
            src="/theme/ai-saas-software/gradient/opai-2.png"
            alt=""
            class="pointer-events-none absolute inset-x-0 bottom-0 h-[58vh] w-full object-cover opacity-75"
        />
        <img
            src="/theme/ai-saas-software/gradient/opai-15.png"
            alt=""
            class="pointer-events-none absolute top-28 -right-28 h-72 w-72 opacity-55 blur-[1px] sm:h-96 sm:w-96"
        />
        <div
            class="absolute inset-0 bg-[radial-gradient(circle_at_top,#1b2540_0%,transparent_42%),linear-gradient(180deg,rgba(8,11,18,0.24)_0%,#080b12_82%)]"
        />

        <div
            class="relative z-10 mx-auto flex min-h-[calc(100vh-2.5rem)] max-w-7xl flex-col"
        >
            <header
                class="flex items-center justify-between rounded-full border border-white/12 bg-white/[0.06] px-4 py-3 shadow-[0_16px_70px_rgba(0,0,0,0.24)] backdrop-blur-xl sm:px-5"
            >
                <Link href="/" class="flex items-center gap-3">
                    <span
                        class="grid size-10 place-items-center rounded-full bg-white text-[#080b12]"
                        aria-hidden="true"
                    >
                        <QrCode class="size-5" />
                    </span>
                    <span
                        class="text-sm font-semibold tracking-wide sm:text-base"
                    >
                        EventSmart
                    </span>
                </Link>

                <nav class="flex items-center gap-2 text-sm font-medium">
                    <Link
                        :href="login()"
                        class="rounded-full px-4 py-2 text-white/76 transition hover:bg-white/10 hover:text-white"
                    >
                        Login
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="register()"
                        class="rounded-full bg-white px-4 py-2 text-[#080b12] transition hover:bg-white/88"
                    >
                        Register
                    </Link>
                </nav>
            </header>

            <section
                class="grid flex-1 items-center gap-12 py-12 lg:grid-cols-[0.92fr_1.08fr] lg:py-16"
            >
                <div class="max-w-3xl">
                    <p
                        class="mb-5 inline-flex rounded-full border border-white/12 bg-white/[0.07] px-4 py-2 text-xs font-semibold tracking-[0.2em] text-white/72 uppercase"
                    >
                        QR albums for live events
                    </p>
                    <h1
                        class="max-w-4xl text-5xl leading-[0.95] font-semibold text-white sm:text-7xl lg:text-8xl"
                    >
                        Collect the photos. Project the moment.
                    </h1>
                    <p
                        class="mt-7 max-w-2xl text-base leading-8 text-white/68 sm:text-lg"
                    >
                        Guests scan one QR code to upload photos, messages, and
                        memories. You moderate the album and show approved media
                        on a live projector wall.
                    </p>

                    <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                        <Link
                            :href="primaryLink"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-[#d0ff00] px-6 py-3 text-sm font-semibold text-[#080b12] transition hover:bg-white"
                        >
                            {{ primaryLabel }}
                            <ArrowRight class="size-4" />
                        </Link>
                        <Link
                            href="/album"
                            class="inline-flex items-center justify-center gap-2 rounded-full border border-white/14 bg-white/[0.06] px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/12"
                        >
                            Guest album
                        </Link>
                    </div>

                    <dl
                        class="mt-10 grid max-w-2xl grid-cols-3 gap-3 border-t border-white/12 pt-6"
                    >
                        <div>
                            <dt class="text-2xl font-semibold">QR</dt>
                            <dd class="mt-1 text-xs leading-5 text-white/56">
                                Scan to upload
                            </dd>
                        </div>
                        <div>
                            <dt class="text-2xl font-semibold">Live</dt>
                            <dd class="mt-1 text-xs leading-5 text-white/56">
                                Projector wall
                            </dd>
                        </div>
                        <div>
                            <dt class="text-2xl font-semibold">Paid</dt>
                            <dd class="mt-1 text-xs leading-5 text-white/56">
                                Stripe checkout
                            </dd>
                        </div>
                    </dl>
                </div>

                <div
                    class="relative mx-auto w-full max-w-2xl rounded-[2rem] border border-white/12 bg-white/[0.07] p-3 shadow-[0_40px_140px_rgba(0,0,0,0.44)] backdrop-blur-xl"
                >
                    <div
                        class="overflow-hidden rounded-[1.5rem] border border-white/10 bg-[#101521]"
                    >
                        <div
                            class="flex items-center justify-between border-b border-white/10 px-4 py-3"
                        >
                            <div class="flex items-center gap-2">
                                <span
                                    class="size-2.5 rounded-full bg-[#ff6b51]"
                                />
                                <span
                                    class="size-2.5 rounded-full bg-[#d0ff00]"
                                />
                                <span
                                    class="size-2.5 rounded-full bg-[#227eff]"
                                />
                            </div>
                            <span class="text-xs font-medium text-white/46">
                                wall/event-09
                            </span>
                        </div>

                        <div class="grid gap-4 p-4 sm:grid-cols-[0.8fr_1.2fr]">
                            <aside
                                class="flex flex-col gap-4 rounded-2xl border border-white/10 bg-black/22 p-4"
                            >
                                <div class="flex items-center gap-3">
                                    <span
                                        class="grid size-11 place-items-center rounded-full bg-white text-[#080b12]"
                                    >
                                        <QrCode class="size-5" />
                                    </span>
                                    <div>
                                        <p class="text-sm font-semibold">
                                            Scan to add
                                        </p>
                                        <p class="text-xs text-white/48">
                                            Photos, notes, clips
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="grid aspect-square place-items-center rounded-2xl bg-white p-5 text-[#080b12]"
                                >
                                    <div
                                        class="grid size-full grid-cols-5 grid-rows-5 gap-1"
                                        aria-hidden="true"
                                    >
                                        <span
                                            v-for="index in 25"
                                            :key="index"
                                            class="rounded-[0.18rem]"
                                            :class="
                                                [
                                                    1, 2, 3, 5, 6, 9, 11, 13,
                                                    14, 17, 19, 20, 22, 23, 25,
                                                ].includes(index)
                                                    ? 'bg-[#080b12]'
                                                    : 'bg-[#080b12]/10'
                                            "
                                        />
                                    </div>
                                </div>

                                <div class="grid gap-2 text-xs text-white/58">
                                    <div class="flex items-center gap-2">
                                        <Camera class="size-4 text-[#d0ff00]" />
                                        Guest uploads
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <Images class="size-4 text-[#8d59ff]" />
                                        Album gallery
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <MonitorPlay
                                            class="size-4 text-[#227eff]"
                                        />
                                        Projector mode
                                    </div>
                                </div>
                            </aside>

                            <div
                                class="rounded-2xl border border-white/10 bg-white p-3"
                            >
                                <div class="grid grid-cols-3 gap-2">
                                    <span
                                        v-for="(tile, index) in previewTiles"
                                        :key="tile"
                                        class="aspect-[4/5] rounded-xl"
                                        :class="tile"
                                    >
                                        <span
                                            class="block h-full rounded-xl bg-[radial-gradient(circle_at_35%_25%,rgba(255,255,255,0.9),transparent_24%),linear-gradient(160deg,rgba(8,11,18,0.06),rgba(8,11,18,0.22))]"
                                            :class="{
                                                'translate-y-4':
                                                    index === 1 || index === 4,
                                            }"
                                        />
                                    </span>
                                </div>
                                <div
                                    class="mt-3 flex items-center justify-between rounded-xl bg-[#080b12] px-4 py-3 text-white"
                                >
                                    <div>
                                        <p class="text-sm font-semibold">
                                            Live wall ready
                                        </p>
                                        <p class="text-xs text-white/48">
                                            128 approved memories
                                        </p>
                                    </div>
                                    <span
                                        class="rounded-full bg-[#d0ff00] px-3 py-1 text-xs font-semibold text-[#080b12]"
                                    >
                                        On air
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
</template>
