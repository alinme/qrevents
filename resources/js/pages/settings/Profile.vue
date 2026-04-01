<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useTranslations } from '@/composables/useTranslations';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit, update as updateProfile } from '@/routes/profile';
import { send } from '@/routes/verification';
import type { BreadcrumbItem } from '@/types';

type Props = {
    mustVerifyEmail: boolean;
    status?: string;
};

defineProps<Props>();

const { t } = useTranslations();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: t('account_settings.profile.page_title'),
        href: edit(),
    },
];

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="t('account_settings.profile.page_title')" />

        <h1 class="sr-only">{{ t('account_settings.profile.page_title') }}</h1>

        <SettingsLayout>
            <div class="space-y-6">
                <Heading
                    variant="small"
                    :title="t('account_settings.profile.title')"
                    :description="t('account_settings.profile.description')"
                />

                <Form
                    v-bind="updateProfile.form()"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div class="grid gap-2">
                        <Label for="name">{{ t('auth.shared.name') }}</Label>
                        <Input
                            id="name"
                            class="mt-1 block w-full"
                            name="name"
                            :default-value="user.name"
                            required
                            autocomplete="name"
                            :placeholder="t('auth.shared.full_name')"
                        />
                        <InputError class="mt-2" :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">{{ t('auth.shared.email_address') }}</Label>
                        <Input
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            name="email"
                            :default-value="user.email"
                            required
                            autocomplete="username"
                            :placeholder="t('auth.shared.email_address')"
                        />
                        <InputError class="mt-2" :message="errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            {{ t('account_settings.profile.unverified_email') }}
                            <Link
                                :href="send()"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            >
                                {{ t('account_settings.profile.resend_verification') }}
                            </Link>
                        </p>

                        <div
                            v-if="status === 'verification-link-sent'"
                            class="mt-2 text-sm font-medium text-green-600"
                        >
                            {{ t('account_settings.profile.verification_sent') }}
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            :disabled="processing"
                            data-test="update-profile-button"
                            >{{ t('auth.shared.save') }}</Button
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

            <div class="border-t border-brand-border/70 pt-8">
                <DeleteUser />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
