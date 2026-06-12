<script setup lang="ts">
import { Mail, PartyPopper, Users } from 'lucide-vue-next';
import { useTranslations } from '@/composables/useTranslations';
import MarketingLayout from '@/layouts/MarketingLayout.vue';

defineProps<{
    canRegister?: boolean;
}>();

const { t } = useTranslations();
</script>

<template>
    <MarketingLayout
        :title="t('contact.title')"
        :description="t('contact.intro')"
        :can-register="canRegister"
    >
        <section class="px-6 pt-14 pb-20 sm:pt-20">
            <div class="mx-auto max-w-3xl">
                <p
                    class="text-xs font-semibold tracking-[0.26em] text-promo-primary uppercase"
                >
                    {{ t('contact.eyebrow') }}
                </p>
                <h1
                    class="mt-3 text-4xl font-semibold tracking-tight text-promo-ink sm:text-5xl"
                >
                    {{ t('contact.title') }}
                </h1>
                <p class="mt-5 text-base leading-7 text-promo-muted">
                    {{ t('contact.intro') }}
                </p>

                <div
                    class="mt-10 rounded-3xl border border-promo-line bg-promo-surface p-6 shadow-card sm:p-8"
                >
                    <span
                        class="flex size-11 items-center justify-center rounded-full bg-promo-primary/10 text-promo-primary"
                    >
                        <Mail class="size-5" />
                    </span>
                    <h2 class="mt-4 text-lg font-semibold text-promo-ink">
                        {{ t('contact.email_title') }}
                    </h2>
                    <p class="mt-2 text-sm leading-7 text-promo-muted">
                        {{ t('contact.email_body') }}
                    </p>
                    <a
                        href="mailto:events@wvdev.org"
                        class="mt-5 inline-flex items-center justify-center rounded-full bg-promo-primary px-6 py-3 text-sm font-semibold text-white transition hover:opacity-90"
                    >
                        {{ t('contact.email_cta') }}
                    </a>
                    <p class="mt-4 text-xs leading-6 text-promo-muted">
                        {{ t('contact.response') }}
                    </p>
                </div>

                <div class="mt-6 grid gap-6 sm:grid-cols-2">
                    <div
                        class="rounded-3xl border border-promo-line bg-promo-surface p-6 shadow-card sm:p-8"
                    >
                        <span
                            class="flex size-11 items-center justify-center rounded-full bg-promo-primary/10 text-promo-primary"
                        >
                            <PartyPopper class="size-5" />
                        </span>
                        <h2 class="mt-4 text-lg font-semibold text-promo-ink">
                            {{ t('contact.hosts_title') }}
                        </h2>
                        <p class="mt-2 text-sm leading-7 text-promo-muted">
                            {{ t('contact.hosts_body') }}
                        </p>
                    </div>
                    <div
                        class="rounded-3xl border border-promo-line bg-promo-surface p-6 shadow-card sm:p-8"
                    >
                        <span
                            class="flex size-11 items-center justify-center rounded-full bg-promo-primary/10 text-promo-primary"
                        >
                            <Users class="size-5" />
                        </span>
                        <h2 class="mt-4 text-lg font-semibold text-promo-ink">
                            {{ t('contact.guests_title') }}
                        </h2>
                        <p class="mt-2 text-sm leading-7 text-promo-muted">
                            {{ t('contact.guests_body') }}
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </MarketingLayout>
</template>
