<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import SettingsSection from '@/components/settings/SettingsSection.vue';
import SettingsShell from '@/components/settings/SettingsShell.vue';

type SettingsTab = { key: string; title: string; href: string };

const props = defineProps<{
    settingsTabs: SettingsTab[];
    activeTab: string;
    publishableKey: string;
    secretSet: boolean;
    webhookSecretSet: boolean;
    mode: 'test' | 'live' | null;
    webhookUrl: string;
}>();

const form = useForm({
    key: props.publishableKey,
    secret: '',
    webhook_secret: '',
});

const submit = (): void => {
    form.put('/admin/settings/payments', { preserveScroll: true });
};
</script>

<template>
    <SettingsShell
        :tabs="settingsTabs"
        :active-tab="activeTab"
        title="Payments"
        description="Stripe API keys for event checkouts and business wallet top-ups. Secrets are stored encrypted and never shown again."
    >
        <form @submit.prevent="submit">
            <SettingsSection
                title="Stripe API keys"
                description="Find these in your Stripe Dashboard under Developers → API keys. Use test keys while trialling, live keys in production."
            >
                <div
                    v-if="mode"
                    class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold"
                    :class="
                        mode === 'live'
                            ? 'bg-emerald-100 text-emerald-700'
                            : 'bg-amber-100 text-amber-700'
                    "
                >
                    <span
                        class="size-1.5 rounded-full"
                        :class="mode === 'live' ? 'bg-emerald-500' : 'bg-amber-500'"
                    />
                    {{ mode === 'live' ? 'Live mode' : 'Test mode' }}
                </div>

                <div class="space-y-5">
                    <div class="space-y-1.5">
                        <Label for="stripe-key">Publishable key</Label>
                        <Input
                            id="stripe-key"
                            v-model="form.key"
                            placeholder="pk_live_… or pk_test_…"
                        />
                        <InputError :message="form.errors.key" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="stripe-secret">Secret key</Label>
                        <Input
                            id="stripe-secret"
                            v-model="form.secret"
                            type="password"
                            autocomplete="off"
                            :placeholder="
                                secretSet
                                    ? '•••••••••• saved — leave blank to keep'
                                    : 'sk_live_… or sk_test_…'
                            "
                        />
                        <InputError :message="form.errors.secret" />
                    </div>
                </div>
            </SettingsSection>

            <SettingsSection
                title="Webhook"
                description="Add this endpoint in Stripe (Developers → Webhooks), then paste the signing secret it gives you. Required for payments to be confirmed."
            >
                <div class="space-y-5">
                    <div class="space-y-1.5">
                        <Label>Endpoint URL</Label>
                        <Input :model-value="webhookUrl" readonly class="bg-brand-panel-strong/20" />
                        <p class="dashboard-meta">
                            Send the <code>checkout.session.completed</code> and
                            <code>checkout.session.async_payment_succeeded</code>
                            events to this URL.
                        </p>
                    </div>
                    <div class="space-y-1.5">
                        <Label for="stripe-webhook">Webhook signing secret</Label>
                        <Input
                            id="stripe-webhook"
                            v-model="form.webhook_secret"
                            type="password"
                            autocomplete="off"
                            :placeholder="
                                webhookSecretSet
                                    ? '•••••••••• saved — leave blank to keep'
                                    : 'whsec_…'
                            "
                        />
                        <InputError :message="form.errors.webhook_secret" />
                    </div>
                </div>
            </SettingsSection>

            <div class="flex justify-end border-t border-brand-border/70 pt-6">
                <Button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-full bg-brand-ink text-brand-inverse hover:bg-brand-accent"
                >
                    Save changes
                </Button>
            </div>
        </form>
    </SettingsShell>
</template>
