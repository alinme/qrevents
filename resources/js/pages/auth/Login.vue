<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useTranslations } from '@/composables/useTranslations';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
    googleAuthEnabled: boolean;
    googleAuthUrl: string | null;
    socialAuthError?: string | null;
}>();

const { t } = useTranslations();
</script>

<template>
    <AuthBase
        :title="t('auth.login.title')"
        :description="t('auth.login.description')"
    >
        <Head :title="t('auth.login.head_title')" />

        <div
            v-if="status"
            class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-center text-sm font-medium text-emerald-700"
        >
            {{ status }}
        </div>

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
                <span>{{ t('auth.login.divider') }}</span>
                <span class="h-px flex-1 bg-zinc-200" />
            </div>
        </div>

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email" class="text-promo-ink"
                        >{{ t('auth.shared.email_address') }}</Label
                    >
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        autocomplete="email"
                        :placeholder="t('auth.shared.email_placeholder')"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password" class="text-promo-ink"
                            >{{ t('auth.shared.password') }}</Label
                        >
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-sm"
                        >
                            {{ t('auth.login.forgot_password') }}
                        </TextLink>
                    </div>
                    <PasswordInput
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        :placeholder="t('auth.shared.password')"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <Label for="remember" class="flex items-center space-x-3">
                        <Checkbox id="remember" name="remember" />
                        <span>{{ t('auth.login.remember_me') }}</span>
                    </Label>
                </div>

                <Button
                    type="submit"
                    class="mt-4 w-full rounded-full bg-promo-primary text-white hover:bg-promo-primary-strong"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <Spinner v-if="processing" />
                    {{ t('auth.login.submit') }}
                </Button>
            </div>

            <div
                class="text-center text-sm text-promo-muted"
                v-if="canRegister"
            >
                {{ t('auth.login.no_account') }}
                <TextLink :href="register()">{{ t('auth.login.register') }}</TextLink>
            </div>
        </Form>
    </AuthBase>
</template>
