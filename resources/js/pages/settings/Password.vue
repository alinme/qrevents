<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { useTranslations } from '@/composables/useTranslations';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit, update as updatePassword } from '@/routes/user-password';
import type { BreadcrumbItem } from '@/types';

const { t } = useTranslations();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: t('account_settings.password.page_title'),
        href: edit(),
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="t('account_settings.password.page_title')" />

        <h1 class="sr-only">{{ t('account_settings.password.page_title') }}</h1>

        <SettingsLayout>
            <div class="space-y-6">
                <Heading
                    variant="small"
                    :title="t('account_settings.password.title')"
                    :description="t('account_settings.password.description')"
                />

                <Form
                    v-bind="updatePassword.form()"
                    :options="{
                        preserveScroll: true,
                    }"
                    reset-on-success
                    :reset-on-error="[
                        'password',
                        'password_confirmation',
                        'current_password',
                    ]"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div class="grid gap-2">
                        <Label for="current_password">{{ t('account_settings.password.current_password') }}</Label>
                        <PasswordInput
                            id="current_password"
                            name="current_password"
                            class="mt-1 block w-full"
                            autocomplete="current-password"
                            :placeholder="t('account_settings.password.current_password')"
                        />
                        <InputError :message="errors.current_password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">{{ t('account_settings.password.new_password') }}</Label>
                        <PasswordInput
                            id="password"
                            name="password"
                            class="mt-1 block w-full"
                            autocomplete="new-password"
                            :placeholder="t('account_settings.password.new_password')"
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation"
                            >{{ t('auth.shared.confirm_password') }}</Label
                        >
                        <PasswordInput
                            id="password_confirmation"
                            name="password_confirmation"
                            class="mt-1 block w-full"
                            autocomplete="new-password"
                            :placeholder="t('auth.shared.confirm_password')"
                        />
                        <InputError :message="errors.password_confirmation" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            :disabled="processing"
                            data-test="update-password-button"
                            >{{ t('account_settings.password.save') }}</Button
                        >

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="recentlySuccessful"
                                class="text-sm text-neutral-600"
                            >
                                {{ t('auth.shared.saved') }}
                            </p>
                        </Transition>
                    </div>
                </Form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
