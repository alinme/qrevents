<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Check, Shield, Sparkles, Zap } from 'lucide-vue-next';
import MarketingSectionHeading from '@/components/marketing/MarketingSectionHeading.vue';
import MarketingLayout from '@/layouts/MarketingLayout.vue';
import { register } from '@/routes';

type PricingPlan = {
    id: number;
    name: string;
    description: string | null;
    currency: string;
    priceLabel: string;
    storageLabel: string;
    uploadLabel: string;
    retentionLabel: string;
    videoLabel: string;
    photoSizeLabel: string;
    videoSizeLabel: string;
    isDefault: boolean;
    ctaHref: string;
    ctaLabel: string;
};

const props = defineProps<{
    canRegister: boolean;
    plans: PricingPlan[];
}>();

const compareRows = [
    { label: 'Storage included', values: props.plans.map((plan) => plan.storageLabel) },
    { label: 'Uploads included', values: props.plans.map((plan) => plan.uploadLabel) },
    { label: 'Retention window', values: props.plans.map((plan) => plan.retentionLabel) },
    { label: 'Video length', values: props.plans.map((plan) => plan.videoLabel) },
];

const compareColumns = props.plans.map((plan, index) => ({
    id: plan.id,
    name: plan.name,
    rows: compareRows.map((row) => ({
        label: row.label,
        value: row.values[index] ?? '',
    })),
}));
</script>

<template>
    <MarketingLayout
        title="Pricing"
        description="Simple event pricing for guest albums, QR code sharing, and live slideshows."
        :can-register="canRegister"
    >
        <section class="mx-auto max-w-7xl px-4 pb-18 pt-12 sm:px-6 lg:px-8 lg:pb-24 lg:pt-16">
            <div class="grid items-end gap-10 lg:grid-cols-[0.9fr_1.1fr]">
                <div class="max-w-2xl">
                    <p class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-promo-primary shadow-[0_10px_24px_rgba(232,79,154,0.08)]">
                        <Sparkles class="size-3.5" />
                        Pricing
                    </p>
                    <h1 class="mt-6 text-5xl font-extrabold leading-[0.96] tracking-[-0.06em] text-promo-ink sm:text-6xl">
                        Clear event pricing with beautiful albums, QR access, and live display built in
                    </h1>
                </div>

                <div class="rounded-[30px] border border-promo-line bg-white p-6 shadow-[0_18px_55px_rgba(120,86,255,0.08)]">
                    <p class="text-base leading-8 text-promo-muted">
                        Choose a plan that fits your event size and upload needs. Every package is designed to feel simple for hosts and effortless for guests.
                    </p>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8">
            <div
                v-if="plans.length > 0"
                class="grid gap-6 [grid-template-columns:repeat(auto-fit,minmax(290px,1fr))]"
            >
                <article
                    v-for="plan in plans"
                    :key="plan.id"
                    class="flex h-full flex-col rounded-[30px] border bg-white p-7 shadow-[0_18px_55px_rgba(232,79,154,0.08)]"
                    :class="plan.isDefault ? 'border-promo-primary/30 bg-linear-to-br from-white to-promo-surface/80' : 'border-promo-line'"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-promo-primary">
                                {{ plan.currency }}
                            </p>
                            <h2 class="mt-4 text-4xl font-extrabold tracking-[-0.05em] text-promo-ink">
                                {{ plan.priceLabel }}
                            </h2>
                            <p class="mt-3 text-sm leading-7 text-promo-muted">
                                {{ plan.name }}
                            </p>
                        </div>

                        <div
                            v-if="plan.isDefault"
                            class="rounded-full bg-promo-primary px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-white"
                        >
                            Most loved
                        </div>
                    </div>

                    <div class="mt-6 rounded-[22px] border border-promo-line bg-white/80 px-4 py-4 text-sm leading-7 text-promo-muted">
                        {{ plan.description || 'A polished plan for collecting guest uploads, running a live wall, and downloading every memory afterward.' }}
                    </div>

                    <div class="mt-8 flex-1 space-y-4">
                        <div
                            v-for="feature in [plan.storageLabel, plan.uploadLabel, plan.retentionLabel, plan.videoLabel, plan.photoSizeLabel, plan.videoSizeLabel]"
                            :key="feature"
                            class="flex items-start gap-3"
                        >
                            <div class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-promo-surface text-promo-primary">
                                <Check class="size-3.5" />
                            </div>
                            <div class="text-sm leading-6 text-promo-ink">
                                {{ feature }}
                            </div>
                        </div>
                    </div>

                    <Link
                        :href="plan.ctaHref"
                        class="mt-8 inline-flex items-center justify-center gap-2 rounded-full px-5 py-3 text-sm font-semibold transition"
                        :class="
                            plan.isDefault
                                ? 'bg-promo-primary text-white hover:bg-promo-primary-strong'
                                : 'border border-promo-line bg-white text-promo-ink hover:bg-promo-surface'
                        "
                    >
                        {{ plan.ctaLabel }}
                        <ArrowRight class="size-4" />
                    </Link>
                </article>
            </div>

            <div
                v-else
                class="rounded-[30px] border border-promo-line bg-white px-8 py-12 text-center text-promo-muted"
            >
                Pricing is being finalized. Check back shortly for published event plans.
            </div>
        </section>

        <section class="bg-white">
            <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
                <MarketingSectionHeading
                    eyebrow="Compare plans"
                    title="See what changes as your event grows"
                    description="The comparison below keeps the essentials visible without making the page feel like a spreadsheet."
                    centered
                />

                <div v-if="plans.length > 0" class="mt-12 grid gap-4 lg:hidden">
                    <article
                        v-for="plan in compareColumns"
                        :key="plan.id"
                        class="rounded-[26px] border border-promo-line bg-promo-bg p-5"
                    >
                        <h3 class="text-2xl font-bold text-promo-ink">{{ plan.name }}</h3>
                        <div class="mt-4 grid gap-3">
                            <div
                                v-for="row in plan.rows"
                                :key="`${plan.id}-${row.label}`"
                                class="rounded-[18px] bg-white px-4 py-3"
                            >
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-promo-primary">
                                    {{ row.label }}
                                </p>
                                <p class="mt-2 text-sm font-medium leading-6 text-promo-ink">
                                    {{ row.value }}
                                </p>
                            </div>
                        </div>
                    </article>
                </div>

                <div
                    v-if="plans.length > 0"
                    class="mt-12 hidden overflow-hidden rounded-[28px] border border-promo-line bg-white lg:block"
                >
                    <div
                        class="grid border-b border-promo-line bg-promo-surface px-6 py-4 text-xs font-semibold uppercase tracking-[0.18em] text-promo-muted"
                        :style="{ gridTemplateColumns: `1.1fr repeat(${plans.length}, minmax(0, 1fr))` }"
                    >
                        <div>Capability</div>
                        <div v-for="plan in plans" :key="plan.id">{{ plan.name }}</div>
                    </div>

                    <div
                        v-for="row in compareRows"
                        :key="row.label"
                        class="grid px-6 py-4 text-sm text-promo-muted [&:not(:last-child)]:border-b [&:not(:last-child)]:border-promo-line"
                        :style="{ gridTemplateColumns: `1.1fr repeat(${plans.length}, minmax(0, 1fr))` }"
                    >
                        <div class="font-semibold text-promo-ink">{{ row.label }}</div>
                        <div v-for="value in row.values" :key="`${row.label}-${value}`">{{ value }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-promo-surface/55">
            <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
                <div class="grid gap-6 md:grid-cols-3">
                    <article class="rounded-[26px] border border-promo-line bg-white px-6 py-7">
                        <div class="flex size-12 items-center justify-center rounded-[18px] bg-promo-surface text-promo-primary">
                            <Sparkles class="size-5" />
                        </div>
                        <h3 class="mt-5 text-2xl font-bold text-promo-ink">Clear package choices</h3>
                        <p class="mt-4 text-sm leading-7 text-promo-muted">
                            Every plan is easy to understand without forcing hosts to decode vague SaaS jargon.
                        </p>
                    </article>

                    <article class="rounded-[26px] border border-promo-line bg-white px-6 py-7">
                        <div class="flex size-12 items-center justify-center rounded-[18px] bg-promo-surface text-promo-primary">
                            <Shield class="size-5" />
                        </div>
                        <h3 class="mt-5 text-2xl font-bold text-promo-ink">Built for confidence</h3>
                        <p class="mt-4 text-sm leading-7 text-promo-muted">
                            Storage, upload limits, and retention stay visible up front so there are fewer surprises later.
                        </p>
                    </article>

                    <article class="rounded-[26px] border border-promo-line bg-white px-6 py-7">
                        <div class="flex size-12 items-center justify-center rounded-[18px] bg-promo-surface text-promo-primary">
                            <Zap class="size-5" />
                        </div>
                        <h3 class="mt-5 text-2xl font-bold text-promo-ink">Start light, scale later</h3>
                        <p class="mt-4 text-sm leading-7 text-promo-muted">
                            Start with a single event and grow into bigger activations, venue programs, or recurring client work.
                        </p>
                    </article>
                </div>

                <div class="mt-12 text-center">
                    <Link
                        :href="register()"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-promo-primary px-6 py-4 text-sm font-semibold text-white transition hover:bg-promo-primary-strong"
                    >
                        Start Free
                        <ArrowRight class="size-4" />
                    </Link>
                </div>
            </div>
        </section>
    </MarketingLayout>
</template>
