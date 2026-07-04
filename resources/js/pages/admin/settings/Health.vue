<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { CheckCircle2, RefreshCw, XCircle } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Progress } from '@/components/ui/progress';
import SettingsSection from '@/components/settings/SettingsSection.vue';
import SettingsShell from '@/components/settings/SettingsShell.vue';
import { formatDateTime } from '@/lib/dashboard';

type SettingsTab = { key: string; title: string; href: string };

type Snapshot = {
    queue: { pending: number; failed: number; batches: number };
    failedJobs: {
        total: number;
        recent: {
            id: number;
            name: string;
            queue: string;
            failedAt: string | null;
            error: string;
        }[];
    };
    checks: { key: string; label: string; ok: boolean; detail: string }[];
    system: {
        phpVersion: string;
        laravelVersion: string;
        environment: string;
        debug: boolean;
        diskFreeBytes: number | null;
        diskTotalBytes: number | null;
    };
    generatedAt: string;
};

const props = defineProps<{
    settingsTabs: SettingsTab[];
    activeTab: string;
    snapshot: Snapshot;
    dataUrl: string;
    links: { retryFailed: string; flushFailed: string };
}>();

const data = ref<Snapshot>(props.snapshot);
let timer: ReturnType<typeof setInterval> | null = null;

const refresh = async (): Promise<void> => {
    try {
        const res = await fetch(props.dataUrl, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });
        if (res.ok) {
            data.value = await res.json();
        }
    } catch {
        // transient — keep the last snapshot
    }
};

onMounted(() => {
    timer = setInterval(refresh, 5000);
});
onUnmounted(() => {
    if (timer) clearInterval(timer);
});

const diskUsedPercent = computed(() => {
    const { diskFreeBytes, diskTotalBytes } = data.value.system;
    if (!diskFreeBytes || !diskTotalBytes) return null;
    return Math.round(((diskTotalBytes - diskFreeBytes) / diskTotalBytes) * 100);
});

const formatGb = (bytes: number | null): string =>
    bytes ? `${(bytes / 1_073_741_824).toFixed(1)} GB` : '—';

const queueStats = computed(() => [
    { label: 'Pending jobs', value: data.value.queue.pending },
    { label: 'Failed jobs', value: data.value.queue.failed },
    { label: 'Batches', value: data.value.queue.batches },
]);

const retryFailed = (): void =>
    router.post(props.links.retryFailed, {}, { preserveScroll: true });
const flushFailed = (): void =>
    router.post(props.links.flushFailed, {}, { preserveScroll: true });
</script>

<template>
    <SettingsShell
        :tabs="settingsTabs"
        :active-tab="activeTab"
        title="System Health"
        description="Live view of the backend infrastructure that's usually hidden."
    >
        <div class="flex items-center justify-between">
            <p class="dashboard-meta inline-flex items-center gap-1.5">
                <RefreshCw class="size-3.5" />
                Auto-refreshing · updated {{ formatDateTime(data.generatedAt) }}
            </p>
        </div>

        <SettingsSection title="Queues" description="Background job pipeline.">
            <dl class="grid grid-cols-3 gap-8">
                <div v-for="stat in queueStats" :key="stat.label">
                    <dt class="dashboard-eyebrow">{{ stat.label }}</dt>
                    <dd class="mt-1 text-2xl font-bold text-brand-ink">
                        {{ stat.value }}
                    </dd>
                </div>
            </dl>
        </SettingsSection>

        <SettingsSection
            title="Failed jobs"
            description="Jobs that threw an exception. Retry re-queues them; flush clears the list."
        >
            <div class="flex flex-wrap gap-2">
                <Button
                    size="sm"
                    variant="outline"
                    :disabled="data.failedJobs.total === 0"
                    @click="retryFailed"
                >
                    Retry all
                </Button>
                <Button
                    size="sm"
                    variant="outline"
                    class="text-rose-600"
                    :disabled="data.failedJobs.total === 0"
                    @click="flushFailed"
                >
                    Flush
                </Button>
            </div>

            <p v-if="data.failedJobs.recent.length === 0" class="dashboard-meta">
                No failed jobs — clean.
            </p>
            <ul v-else class="divide-y divide-brand-border/70">
                <li
                    v-for="job in data.failedJobs.recent"
                    :key="job.id"
                    class="py-3"
                >
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-semibold text-brand-ink">
                            {{ job.name }}
                        </p>
                        <span class="dashboard-meta shrink-0">
                            {{ formatDateTime(job.failedAt) }}
                        </span>
                    </div>
                    <p class="dashboard-meta mt-0.5 truncate">{{ job.error }}</p>
                </li>
            </ul>
        </SettingsSection>

        <SettingsSection
            title="Service checks"
            description="Connectivity to core dependencies."
        >
            <ul class="space-y-3">
                <li
                    v-for="check in data.checks"
                    :key="check.key"
                    class="flex items-center justify-between gap-3"
                >
                    <span class="inline-flex items-center gap-2 text-sm text-brand-ink">
                        <CheckCircle2
                            v-if="check.ok"
                            class="size-4 text-emerald-600"
                        />
                        <XCircle v-else class="size-4 text-rose-600" />
                        {{ check.label }}
                    </span>
                    <span class="dashboard-meta truncate">{{ check.detail }}</span>
                </li>
            </ul>
        </SettingsSection>

        <SettingsSection title="System" description="Runtime environment.">
            <dl class="grid gap-4 sm:grid-cols-2">
                <div class="flex justify-between gap-3">
                    <dt class="dashboard-meta">Environment</dt>
                    <dd>
                        <Badge variant="secondary">{{ data.system.environment }}</Badge>
                    </dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="dashboard-meta">PHP</dt>
                    <dd class="text-sm text-brand-ink">{{ data.system.phpVersion }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="dashboard-meta">Laravel</dt>
                    <dd class="text-sm text-brand-ink">{{ data.system.laravelVersion }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="dashboard-meta">Debug mode</dt>
                    <dd class="text-sm text-brand-ink">
                        {{ data.system.debug ? 'On' : 'Off' }}
                    </dd>
                </div>
            </dl>

            <div v-if="diskUsedPercent !== null" class="mt-2 space-y-1.5">
                <div class="flex items-center justify-between text-sm">
                    <span class="dashboard-meta">Disk usage</span>
                    <span class="text-brand-ink">
                        {{ formatGb(data.system.diskTotalBytes && data.system.diskFreeBytes ? data.system.diskTotalBytes - data.system.diskFreeBytes : null) }}
                        / {{ formatGb(data.system.diskTotalBytes) }}
                    </span>
                </div>
                <Progress :model-value="diskUsedPercent" />
            </div>
        </SettingsSection>
    </SettingsShell>
</template>
