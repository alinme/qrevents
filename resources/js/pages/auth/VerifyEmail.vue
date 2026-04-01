<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { useTranslations } from '@/composables/useTranslations';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

defineProps<{
    status?: string;
}>();

const { t } = useTranslations();
</script>

<template>
    <AuthLayout
        :title="t('auth.verify_email.title')"
        :description="t('auth.verify_email.description')"
    >
        <Head :title="t('auth.verify_email.head_title')" />

        <div
            v-if="status === 'verification-link-sent'"
            class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-center text-sm font-medium text-emerald-700"
        >
            {{ t('auth.verify_email.sent') }}
        </div>

        <Form
            v-bind="send.form()"
            class="space-y-6 text-center"
            v-slot="{ processing }"
        >
            <Button :disabled="processing" class="rounded-full bg-promo-primary text-white hover:bg-promo-primary-strong">
                <Spinner v-if="processing" />
                {{ t('auth.verify_email.submit') }}
            </Button>

            <TextLink
                :href="logout()"
                as="button"
                class="mx-auto block text-sm"
            >
                {{ t('auth.shared.log_out') }}
            </TextLink>
        </Form>
    </AuthLayout>
</template>
