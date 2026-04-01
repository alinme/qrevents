<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useTranslations } from '@/composables/useTranslations';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { store } from '@/routes/password/confirm';

const { t } = useTranslations();
</script>

<template>
    <AuthLayout
        :title="t('auth.confirm_password.title')"
        :description="t('auth.confirm_password.description')"
    >
        <Head :title="t('auth.confirm_password.head_title')" />

        <Form
            v-bind="store.form()"
            reset-on-success
            v-slot="{ errors, processing }"
            class="space-y-6"
        >
            <div class="space-y-6">
                <div class="grid gap-2">
                    <Label for="password" class="text-promo-ink">{{ t('auth.shared.password') }}</Label>
                    <PasswordInput
                        id="password"
                        name="password"
                        class="mt-1 block w-full"
                        required
                        autocomplete="current-password"
                        autofocus
                    />

                    <InputError :message="errors.password" />
                </div>

                <div class="flex items-center">
                    <Button
                        class="w-full rounded-full bg-promo-primary text-white hover:bg-promo-primary-strong"
                        :disabled="processing"
                        data-test="confirm-password-button"
                    >
                        <Spinner v-if="processing" />
                        {{ t('auth.confirm_password.submit') }}
                    </Button>
                </div>
            </div>
        </Form>
    </AuthLayout>
</template>
