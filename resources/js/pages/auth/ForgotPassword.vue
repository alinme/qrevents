<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useTranslations } from '@/composables/useTranslations';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { email } from '@/routes/password';

defineProps<{
    status?: string;
}>();

const { t } = useTranslations();
</script>

<template>
    <AuthLayout
        :title="t('auth.forgot_password.title')"
        :description="t('auth.forgot_password.description')"
    >
        <Head :title="t('auth.forgot_password.head_title')" />

        <div
            v-if="status"
            class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-center text-sm font-medium text-emerald-700"
        >
            {{ status }}
        </div>

        <div class="space-y-6">
            <Form v-bind="email.form()" v-slot="{ errors, processing }">
                <div class="grid gap-2">
                    <Label for="email" class="text-promo-ink">{{ t('auth.shared.email_address') }}</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        autocomplete="off"
                        autofocus
                        :placeholder="t('auth.shared.email_placeholder')"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="my-6 flex items-center justify-start">
                    <Button
                        class="w-full rounded-full bg-promo-primary text-white hover:bg-promo-primary-strong"
                        :disabled="processing"
                        data-test="email-password-reset-link-button"
                    >
                        <Spinner v-if="processing" />
                        {{ t('auth.forgot_password.submit') }}
                    </Button>
                </div>
            </Form>

            <div class="space-x-1 text-center text-sm text-promo-muted">
                <span>{{ t('auth.forgot_password.back_prompt') }}</span>
                <TextLink :href="login()">{{ t('auth.login.submit') }}</TextLink>
            </div>
        </div>
    </AuthLayout>
</template>
