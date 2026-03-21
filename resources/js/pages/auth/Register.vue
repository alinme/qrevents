<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';

defineProps<{
    googleAuthEnabled: boolean;
    googleAuthUrl: string | null;
    socialAuthError?: string | null;
}>();
</script>

<template>
    <AuthBase
        title="Create an account"
        description="Enter your details below to create your account"
    >
        <Head title="Register" />

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
                <Link :href="googleAuthUrl!"> Continue with Google </Link>
            </Button>

            <div
                class="flex items-center gap-3 text-xs tracking-[0.18em] text-promo-muted uppercase"
            >
                <span class="h-px flex-1 bg-zinc-200" />
                <span>Or create with email</span>
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
                    <Label for="name" class="text-promo-ink">Name</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autofocus
                        autocomplete="name"
                        name="name"
                        placeholder="Full name"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email" class="text-promo-ink"
                        >Email address</Label
                    >
                    <Input
                        id="email"
                        type="email"
                        required
                        autocomplete="email"
                        name="email"
                        placeholder="email@example.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password" class="text-promo-ink"
                        >Password</Label
                    >
                    <PasswordInput
                        id="password"
                        required
                        autocomplete="new-password"
                        name="password"
                        placeholder="Password"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation" class="text-promo-ink"
                        >Confirm password</Label
                    >
                    <PasswordInput
                        id="password_confirmation"
                        required
                        autocomplete="new-password"
                        name="password_confirmation"
                        placeholder="Confirm password"
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
                    Create account
                </Button>
            </div>

            <div class="text-center text-sm text-promo-muted">
                Already have an account?
                <TextLink :href="login()" class="underline underline-offset-4"
                    >Log in</TextLink
                >
            </div>
        </Form>
    </AuthBase>
</template>
