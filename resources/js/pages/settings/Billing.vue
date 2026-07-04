<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { badgeClass, formatDateTime } from '@/lib/dashboard';
import type { BreadcrumbItem } from '@/types';
import type { Tone } from '@/types/dashboard';

type BillingRow = {
    id: number;
    name: string;
    planName: string;
    amountLabel: string;
    statusLabel: string;
    statusTone: Tone;
    isPaid: boolean;
    paidAt: string | null;
    dueAt: string | null;
    links: { manage: string };
};

defineProps<{ events: BillingRow[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Billing', href: '/settings/billing' },
];
</script>

<template>
    <Head title="Billing" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <SettingsLayout>
            <div>
                <h2 class="dashboard-section-title">Billing history</h2>
                <p class="dashboard-body mt-1">
                    Payments for each of your events. Open an event to pay or
                    manage its plan.
                </p>

                <div v-if="events.length === 0" class="dashboard-body py-8">
                    You don't have any events yet.
                </div>

                <div v-else class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[640px] text-left text-sm">
                        <thead>
                            <tr
                                class="border-b border-brand-border/70 text-[0.72rem] font-semibold tracking-wide text-brand-muted uppercase"
                            >
                                <th class="py-3 pr-4">Event</th>
                                <th class="py-3 pr-4">Plan</th>
                                <th class="py-3 pr-4">Amount</th>
                                <th class="py-3 pr-4">Status</th>
                                <th class="py-3 pr-4">Date</th>
                                <th class="py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border/70">
                            <tr v-for="event in events" :key="event.id">
                                <td class="py-3 pr-4 font-semibold text-brand-ink">
                                    {{ event.name }}
                                </td>
                                <td class="py-3 pr-4 text-brand-muted">
                                    {{ event.planName }}
                                </td>
                                <td class="py-3 pr-4 text-brand-muted">
                                    {{ event.amountLabel }}
                                </td>
                                <td class="py-3 pr-4">
                                    <span
                                        class="inline-flex rounded-full px-2 py-0.5 text-[0.7rem] font-semibold"
                                        :class="badgeClass(event.statusTone)"
                                    >
                                        {{ event.statusLabel }}
                                    </span>
                                </td>
                                <td class="py-3 pr-4 text-brand-muted">
                                    {{
                                        event.isPaid
                                            ? formatDateTime(event.paidAt)
                                            : event.dueAt
                                              ? 'Due ' + formatDateTime(event.dueAt)
                                              : '—'
                                    }}
                                </td>
                                <td class="py-3 text-right">
                                    <Button as-child size="sm" variant="outline">
                                        <Link :href="event.links.manage">
                                            {{ event.isPaid ? 'View' : 'Pay' }}
                                        </Link>
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
