<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useTranslations } from '@/composables/useTranslations';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';

defineProps<{
    googleAuthEnabled: boolean;
    googleAuthUrl: string | null;
    socialAuthError?: string | null;
}>();

const { t } = useTranslations();
</script>

<template>
    <AuthBase
        :title="t('auth.register.title')"
        :description="t('auth.register.description')"
    >
        <Head :title="t('auth.register.head_title')" />

        <div
            v-if="socialAuthError"
            class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-center text-sm font-medium text-rose-700"
        >
            {{ socialAuthError }}
        </div>

        <div v-if="googleAuthEnabled" class="mb-6 flex flex-col gap-4">
            <Button
                as-child
                variant="outline"
                class="h-11 w-full rounded-full border-zinc-300 bg-white text-promo-ink hover:bg-zinc-50"
            >
                <a :href="googleAuthUrl!"> {{ t('auth.shared.continue_with_google') }} </a>
            </Button>

            <div
                class="flex items-center gap-3 text-xs tracking-[0.18em] text-promo-muted uppercase"
            >
                <span class="h-px flex-1 bg-zinc-200" />
                <span>{{ t('auth.register.divider') }}</span>
                <span class="h-px flex-1 bg-zinc-200" />
            </div>
        </div>

        <Form
            v-bind="store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name" class="text-promo-ink">{{ t('auth.shared.name') }}</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autofocus
                        autocomplete="name"
                        name="name"
                        :placeholder="t('auth.shared.full_name')"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email" class="text-promo-ink"
                        >{{ t('auth.shared.email_address') }}</Label
                    >
                    <Input
                        id="email"
                        type="email"
                        required
                        autocomplete="email"
                        name="email"
                        :placeholder="t('auth.shared.email_placeholder')"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password" class="text-promo-ink"
                        >{{ t('auth.shared.password') }}</Label
                    >
                    <PasswordInput
                        id="password"
                        required
                        autocomplete="new-password"
                        name="password"
                        :placeholder="t('auth.shared.password')"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation" class="text-promo-ink"
                        >{{ t('auth.shared.confirm_password') }}</Label
                    >
                    <PasswordInput
                        id="password_confirmation"
                        required
                        autocomplete="new-password"
                        name="password_confirmation"
                        :placeholder="t('auth.shared.confirm_password')"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <Button
                    type="submit"
                    class="mt-2 w-full rounded-full bg-promo-primary text-white hover:bg-promo-primary-strong"
                    :disabled="processing"
                    data-test="register-user-button"
                >
                    <Spinner v-if="processing" />
                    {{ t('auth.register.submit') }}
                </Button>
            </div>

            <div class="text-center text-sm text-promo-muted">
                {{ t('auth.register.has_account') }}
                <TextLink :href="login()" class="underline underline-offset-4"
                    >{{ t('auth.login.submit') }}</TextLink
                >
            </div>
        </Form>
    </AuthBase>
</template>
