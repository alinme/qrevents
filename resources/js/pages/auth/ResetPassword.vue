<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useTranslations } from '@/composables/useTranslations';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { update } from '@/routes/password';

const props = defineProps<{
    token: string;
    email: string;
}>();

const inputEmail = ref(props.email);
const { t } = useTranslations();
</script>

<template>
    <AuthLayout
        :title="t('auth.reset_password.title')"
        :description="t('auth.reset_password.description')"
    >
        <Head :title="t('auth.reset_password.head_title')" />

        <Form
            v-bind="update.form()"
            :transform="(data) => ({ ...data, token, email })"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email" class="text-promo-ink">{{ t('auth.shared.email') }}</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        autocomplete="email"
                        v-model="inputEmail"
                        class="mt-1 block w-full"
                        readonly
                    />
                    <InputError :message="errors.email" class="mt-2" />
                </div>

                <div class="grid gap-2">
                    <Label for="password" class="text-promo-ink">{{ t('auth.shared.password') }}</Label>
                    <PasswordInput
                        id="password"
                        name="password"
                        autocomplete="new-password"
                        class="mt-1 block w-full"
                        autofocus
                        :placeholder="t('auth.shared.password')"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation" class="text-promo-ink">
                        {{ t('auth.shared.confirm_password') }}
                    </Label>
                    <PasswordInput
                        id="password_confirmation"
                        name="password_confirmation"
                        autocomplete="new-password"
                        class="mt-1 block w-full"
                        :placeholder="t('auth.shared.confirm_password')"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <Button
                    type="submit"
                    class="mt-4 w-full rounded-full bg-promo-primary text-white hover:bg-promo-primary-strong"
                    :disabled="processing"
                    data-test="reset-password-button"
                >
                    <Spinner v-if="processing" />
                    {{ t('auth.reset_password.submit') }}
                </Button>
            </div>
        </Form>
    </AuthLayout>
</template>
