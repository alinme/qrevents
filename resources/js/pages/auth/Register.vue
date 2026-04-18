<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    BadgeCheck,
    BriefcaseBusiness,
    Store,
} from 'lucide-vue-next';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useTranslations } from '@/composables/useTranslations';
import AuthLayout from '@/layouts/auth/AuthSimpleLayout.vue';
import { login, register } from '@/routes';
import { store } from '@/routes/register';

const props = withDefaults(
    defineProps<{
        registrationMode?: 'consumer' | 'business';
        businessRegisterUrl: string;
        googleAuthEnabled: boolean;
        googleAuthUrl: string | null;
        socialAuthError?: string | null;
    }>(),
    {
        registrationMode: 'consumer',
    },
);

const { t } = useTranslations();
const isBusinessRegistration = computed(
    () => props.registrationMode === 'business',
);
const pageTitle = computed(() =>
    isBusinessRegistration.value
        ? t('auth.register.business_title')
        : t('auth.register.title'),
);
const pageDescription = computed(() =>
    isBusinessRegistration.value
        ? t('auth.register.business_description')
        : t('auth.register.description'),
);
const pageHeadTitle = computed(() =>
    isBusinessRegistration.value
        ? t('auth.register.business_head_title')
        : t('auth.register.head_title'),
);
const pageEyebrow = computed(() =>
    isBusinessRegistration.value
        ? t('auth.register.business_eyebrow')
        : t('auth.register.eyebrow'),
);
const submitLabel = computed(() =>
    isBusinessRegistration.value
        ? t('auth.register.business_submit')
        : t('auth.register.submit'),
);
const dividerLabel = computed(() =>
    isBusinessRegistration.value
        ? t('auth.register.business_divider')
        : t('auth.register.divider'),
);
</script>

<template>
    <AuthLayout
        :title="pageTitle"
        :description="pageDescription"
        :heading-eyebrow="pageEyebrow"
    >
        <Head :title="pageHeadTitle" />

        <template v-if="isBusinessRegistration" #aside>
            <div class="space-y-4">
                <p
                    class="inline-flex items-center gap-2 rounded-full border border-black/8 bg-white px-4 py-2 text-[0.68rem] font-semibold tracking-[0.2em] text-[#ff385c] uppercase shadow-[0_10px_24px_rgba(0,0,0,0.04)]"
                >
                    <BriefcaseBusiness class="size-3.5" />
                    {{ t('auth.register.business_badge') }}
                </p>
                <h2
                    class="max-w-md text-3xl leading-tight font-bold tracking-[-0.04em] text-[#222222] md:text-[2.5rem]"
                >
                    {{ t('auth.register.business_aside_title') }}
                </h2>
                <p
                    class="max-w-md text-sm leading-7 text-[#6a6a6a] md:text-[0.95rem]"
                >
                    {{ t('auth.register.business_aside_description') }}
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <article
                    class="rounded-[22px] border border-black/6 bg-white p-4 shadow-[rgba(0,0,0,0.02)_0px_0px_0px_1px,rgba(0,0,0,0.04)_0px_2px_6px,rgba(0,0,0,0.1)_0px_4px_8px]"
                >
                    <div class="flex items-start gap-3">
                        <div
                            class="flex size-10 items-center justify-center rounded-full bg-[#f7f7f7] text-[#222222]"
                        >
                            <Store class="size-4" />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-[#222222]">
                                {{
                                    t(
                                        'auth.register.business_feature_one_title',
                                    )
                                }}
                            </h3>
                            <p class="mt-1.5 text-sm leading-6 text-[#6a6a6a]">
                                {{
                                    t('auth.register.business_feature_one_body')
                                }}
                            </p>
                        </div>
                    </div>
                </article>

                <article
                    class="rounded-[22px] border border-black/6 bg-white p-4 shadow-[rgba(0,0,0,0.02)_0px_0px_0px_1px,rgba(0,0,0,0.04)_0px_2px_6px,rgba(0,0,0,0.1)_0px_4px_8px]"
                >
                    <div class="flex items-start gap-3">
                        <div
                            class="flex size-10 items-center justify-center rounded-full bg-[#f7f7f7] text-[#222222]"
                        >
                            <BadgeCheck class="size-4" />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-[#222222]">
                                {{
                                    t(
                                        'auth.register.business_feature_two_title',
                                    )
                                }}
                            </h3>
                            <p class="mt-1.5 text-sm leading-6 text-[#6a6a6a]">
                                {{
                                    t('auth.register.business_feature_two_body')
                                }}
                            </p>
                        </div>
                    </div>
                </article>
            </div>

            <div
                class="inline-flex items-center gap-2 text-sm font-medium text-[#6a6a6a]"
            >
                <ArrowRight class="size-4 text-[#ff385c]" />
                {{ t('auth.register.business_aside_footer') }}
            </div>
        </template>

        <div
            v-if="socialAuthError"
            class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-center text-sm font-medium text-rose-700"
        >
            {{ socialAuthError }}
        </div>

        <div
            class="mb-6 inline-flex w-full rounded-full border border-black/8 bg-[#f7f7f7] p-1"
        >
            <Link
                :href="register()"
                class="flex-1 rounded-full px-4 py-2.5 text-center text-sm font-semibold transition"
                :class="
                    isBusinessRegistration
                        ? 'text-[#6a6a6a] hover:text-[#222222]'
                        : 'bg-white text-[#222222] shadow-[0_1px_2px_rgba(0,0,0,0.08)]'
                "
            >
                {{ t('auth.register.personal_tab') }}
            </Link>
            <Link
                :href="businessRegisterUrl"
                class="flex-1 rounded-full px-4 py-2.5 text-center text-sm font-semibold transition"
                :class="
                    isBusinessRegistration
                        ? 'bg-white text-[#222222] shadow-[0_1px_2px_rgba(0,0,0,0.08)]'
                        : 'text-[#6a6a6a] hover:text-[#222222]'
                "
            >
                {{ t('auth.register.business_tab') }}
            </Link>
        </div>

        <div
            v-if="isBusinessRegistration"
            class="mb-6 rounded-[24px] border border-black/6 bg-[#fff8f6] p-4 text-sm leading-6 text-[#6a6a6a] shadow-[rgba(0,0,0,0.02)_0px_0px_0px_1px,rgba(0,0,0,0.04)_0px_2px_6px]"
        >
            <p class="font-semibold text-[#222222]">
                {{ t('auth.register.business_panel_title') }}
            </p>
            <p class="mt-2">
                {{ t('auth.register.business_panel_body') }}
            </p>
        </div>

        <div v-if="googleAuthEnabled" class="mb-6 flex flex-col gap-4">
            <Button
                as-child
                variant="outline"
                class="h-11 w-full rounded-full border-zinc-300 bg-white text-promo-ink hover:bg-zinc-50"
            >
                <a :href="googleAuthUrl!">
                    {{ t('auth.shared.continue_with_google') }}
                </a>
            </Button>

            <div
                class="flex items-center gap-3 text-xs tracking-[0.18em] text-promo-muted uppercase"
            >
                <span class="h-px flex-1 bg-zinc-200" />
                <span>{{ dividerLabel }}</span>
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
                <input
                    v-if="isBusinessRegistration"
                    type="hidden"
                    name="account_type"
                    value="business"
                />

                <div class="grid gap-2">
                    <Label for="name" class="text-promo-ink">{{
                        t('auth.shared.name')
                    }}</Label>
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
                    <Label for="email" class="text-promo-ink">{{
                        t('auth.shared.email_address')
                    }}</Label>
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
                    <Label for="password" class="text-promo-ink">{{
                        t('auth.shared.password')
                    }}</Label>
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
                    <Label for="password_confirmation" class="text-promo-ink">{{
                        t('auth.shared.confirm_password')
                    }}</Label>
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
                    {{ submitLabel }}
                </Button>
            </div>

            <div class="text-center text-sm text-promo-muted">
                {{ t('auth.register.has_account') }}
                <TextLink
                    :href="login()"
                    class="underline underline-offset-4"
                    >{{ t('auth.login.submit') }}</TextLink
                >
            </div>

            <div
                v-if="isBusinessRegistration"
                class="text-center text-sm text-promo-muted"
            >
                {{ t('auth.register.business_personal_prompt') }}
                <TextLink
                    :href="register()"
                    class="underline underline-offset-4"
                >
                    {{ t('auth.register.personal_tab') }}
                </TextLink>
            </div>
            <div v-else class="text-center text-sm text-promo-muted">
                {{ t('auth.register.business_prompt') }}
                <TextLink
                    :href="businessRegisterUrl"
                    class="underline underline-offset-4"
                >
                    {{ t('auth.register.business_tab') }}
                </TextLink>
            </div>
        </Form>
    </AuthLayout>
</template>
