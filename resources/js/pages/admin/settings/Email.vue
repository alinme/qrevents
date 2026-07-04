<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { Send } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import SettingsSection from '@/components/settings/SettingsSection.vue';
import SettingsShell from '@/components/settings/SettingsShell.vue';

type SettingsTab = { key: string; title: string; href: string };

const props = defineProps<{
    settingsTabs: SettingsTab[];
    activeTab: string;
    values: {
        host: string;
        port: number;
        encryption: string;
        username: string;
        from_address: string;
        from_name: string;
    };
    passwordSet: boolean;
    testRecipient: string;
    testUrl: string;
}>();

const form = useForm({
    host: props.values.host,
    port: props.values.port,
    encryption: props.values.encryption || 'tls',
    username: props.values.username,
    password: '',
    from_address: props.values.from_address,
    from_name: props.values.from_name,
});

const testing = ref(false);

const submit = (): void => {
    form.put('/admin/settings/email', { preserveScroll: true });
};

const sendTest = (): void => {
    router.post(
        props.testUrl,
        {},
        {
            preserveScroll: true,
            onStart: () => (testing.value = true),
            onFinish: () => (testing.value = false),
        },
    );
};
</script>

<template>
    <SettingsShell
        :tabs="settingsTabs"
        :active-tab="activeTab"
        title="Email & SMTP"
        description="Outgoing mail server used for all transactional email."
    >
        <form @submit.prevent="submit">
            <SettingsSection
                title="Mail server"
                description="Credentials for your SMTP provider. The password is stored encrypted and never shown again."
            >
                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <Label for="host">SMTP host</Label>
                        <Input id="host" v-model="form.host" placeholder="smtp.example.com" />
                        <InputError :message="form.errors.host" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="port">Port</Label>
                        <Input id="port" v-model.number="form.port" type="number" />
                        <InputError :message="form.errors.port" />
                    </div>
                    <div class="space-y-1.5">
                        <Label>Encryption</Label>
                        <Select v-model="form.encryption">
                            <SelectTrigger>
                                <SelectValue placeholder="Select encryption" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="tls">TLS</SelectItem>
                                <SelectItem value="ssl">SSL</SelectItem>
                                <SelectItem value="none">None</SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.encryption" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="username">Username</Label>
                        <Input id="username" v-model="form.username" />
                        <InputError :message="form.errors.username" />
                    </div>
                </div>
                <div class="space-y-1.5">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        v-model="form.password"
                        type="password"
                        :placeholder="passwordSet ? '•••••••••• saved — leave blank to keep' : 'Enter SMTP password'"
                    />
                    <InputError :message="form.errors.password" />
                </div>
            </SettingsSection>

            <SettingsSection
                title="Sender details"
                description="The From address and name recipients will see."
            >
                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <Label for="from_address">Email from address</Label>
                        <Input id="from_address" v-model="form.from_address" type="email" />
                        <InputError :message="form.errors.from_address" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="from_name">Email from name</Label>
                        <Input id="from_name" v-model="form.from_name" />
                        <InputError :message="form.errors.from_name" />
                    </div>
                </div>
            </SettingsSection>

            <SettingsSection
                title="Testing"
                description="Send yourself a test message to verify the configuration."
            >
                <div class="flex flex-wrap items-center gap-3">
                    <Button
                        type="button"
                        variant="outline"
                        :disabled="testing"
                        @click="sendTest"
                    >
                        <Send class="size-4" />
                        Send test email
                    </Button>
                    <span class="dashboard-meta">to {{ testRecipient }}</span>
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
