<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { CheckCircle2, Terminal, TriangleAlert, XCircle } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { ScrollArea } from '@/components/ui/scroll-area';
import SettingsSection from '@/components/settings/SettingsSection.vue';
import SettingsShell from '@/components/settings/SettingsShell.vue';

type SettingsTab = { key: string; title: string; href: string };
type Operation = {
    key: string;
    label: string;
    description: string;
    danger: boolean;
};
type LastResult = { operation: string; ok: boolean; output: string } | null;

const props = defineProps<{
    settingsTabs: SettingsTab[];
    activeTab: string;
    operations: Operation[];
    runUrl: string;
    lastResult: LastResult;
}>();

const running = ref<string | null>(null);
const confirmOp = ref<Operation | null>(null);

const run = (operation: string): void => {
    router.post(
        props.runUrl,
        { operation },
        {
            preserveScroll: true,
            onStart: () => (running.value = operation),
            onFinish: () => (running.value = null),
        },
    );
};

const trigger = (op: Operation): void => {
    if (op.danger) {
        confirmOp.value = op;
        return;
    }
    run(op.key);
};

const confirmRun = (): void => {
    if (!confirmOp.value) return;
    const key = confirmOp.value.key;
    confirmOp.value = null;
    run(key);
};
</script>

<template>
    <SettingsShell
        :tabs="settingsTabs"
        :active-tab="activeTab"
        title="DevTools"
        description="Predefined maintenance operations. There is no free-form command input — each button runs a fixed, allowlisted task."
    >
        <SettingsSection
            title="Operations"
            description="Run a maintenance task. Output appears in the console below."
        >
            <div class="grid gap-3 sm:grid-cols-2">
                <div
                    v-for="op in operations"
                    :key="op.key"
                    class="flex flex-col justify-between gap-3 rounded-xl border border-brand-border/70 p-4"
                >
                    <div>
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-semibold text-brand-ink">
                                {{ op.label }}
                            </p>
                            <TriangleAlert
                                v-if="op.danger"
                                class="size-3.5 text-amber-500"
                            />
                        </div>
                        <p class="dashboard-meta mt-1">{{ op.description }}</p>
                    </div>
                    <Button
                        size="sm"
                        variant="outline"
                        class="self-start"
                        :disabled="running !== null"
                        @click="trigger(op)"
                    >
                        {{ running === op.key ? 'Running…' : 'Run' }}
                    </Button>
                </div>
            </div>
        </SettingsSection>

        <SettingsSection
            title="Console output"
            description="Result of the last operation you ran."
        >
            <div
                class="overflow-hidden rounded-xl border border-brand-border/70 bg-brand-ink"
            >
                <div
                    class="flex items-center gap-2 border-b border-white/10 px-4 py-2 text-xs font-medium text-white/70"
                >
                    <Terminal class="size-3.5" />
                    <span v-if="lastResult">
                        {{ lastResult.operation }}
                    </span>
                    <span v-else>console</span>
                    <span
                        v-if="lastResult"
                        class="ml-auto inline-flex items-center gap-1"
                        :class="lastResult.ok ? 'text-emerald-400' : 'text-rose-400'"
                    >
                        <CheckCircle2 v-if="lastResult.ok" class="size-3.5" />
                        <XCircle v-else class="size-3.5" />
                        {{ lastResult.ok ? 'success' : 'failed' }}
                    </span>
                </div>
                <ScrollArea class="max-h-80">
                    <pre
                        class="px-4 py-3 font-mono text-xs leading-relaxed whitespace-pre-wrap text-white/90"
                    >{{ lastResult ? lastResult.output : 'No command run yet.' }}</pre>
                </ScrollArea>
            </div>
        </SettingsSection>

        <Dialog
            :open="confirmOp !== null"
            @update:open="(v) => { if (!v) confirmOp = null }"
        >
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Run "{{ confirmOp?.label }}"?</DialogTitle>
                    <DialogDescription>
                        {{ confirmOp?.description }}. This runs against the live
                        environment.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="confirmOp = null">
                        Cancel
                    </Button>
                    <Button
                        class="bg-brand-ink text-brand-inverse hover:bg-brand-accent"
                        @click="confirmRun"
                    >
                        Run operation
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </SettingsShell>
</template>
