<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';

const props = defineProps<{
    eventName: string;
    email: string;
    hasAccount: boolean;
    links: {
        register: string;
        login: string;
    };
}>();

const registerForm = useForm({
    name: '',
    password: '',
    password_confirmation: '',
});

const loginForm = useForm({
    password: '',
});

const registerEmailError = (): string | undefined => {
    return (registerForm.errors as Record<string, string | undefined>).email;
};

const loginEmailError = (): string | undefined => {
    return (loginForm.errors as Record<string, string | undefined>).email;
};

const submitRegister = (): void => {
    registerForm.post(props.links.register, {
        preserveScroll: true,
    });
};

const submitLogin = (): void => {
    loginForm.post(props.links.login, {
        preserveScroll: true,
    });
};
</script>

<template>
    <AuthLayout
        title="You were invited"
        :description="`Join ${eventName} as a collaborator`"
    >
        <Head title="Collaborator Invitation" />

        <div class="mb-4 rounded-[20px] border border-promo-line bg-promo-surface px-4 py-3 text-sm text-promo-ink">
            Invited email: <span class="font-semibold">{{ email }}</span>
        </div>

        <form
            v-if="!hasAccount"
            class="flex flex-col gap-6"
            @submit.prevent="submitRegister"
        >
            <div class="grid gap-4">
                <div class="grid gap-2">
                    <Label for="invited-email" class="text-promo-ink">Email</Label>
                    <Input
                        id="invited-email"
                        :model-value="email"
                        type="email"
                        readonly
                    />
                    <InputError :message="registerEmailError()" />
                </div>

                <div class="grid gap-2">
                    <Label for="name" class="text-promo-ink">Name</Label>
                    <Input
                        id="name"
                        v-model="registerForm.name"
                        type="text"
                        required
                        autofocus
                        placeholder="Your full name"
                    />
                    <InputError :message="registerForm.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="password" class="text-promo-ink">Password</Label>
                    <PasswordInput
                        id="password"
                        v-model="registerForm.password"
                        required
                        autocomplete="new-password"
                        placeholder="Password"
                    />
                    <InputError :message="registerForm.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation" class="text-promo-ink">
                        Confirm password
                    </Label>
                    <PasswordInput
                        id="password_confirmation"
                        v-model="registerForm.password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="Confirm password"
                    />
                </div>
            </div>

            <Button type="submit" class="w-full rounded-full bg-promo-primary text-white hover:bg-promo-primary-strong" :disabled="registerForm.processing">
                <Spinner v-if="registerForm.processing" />
                Create account and accept invite
            </Button>
        </form>

        <form
            v-else
            class="flex flex-col gap-6"
            @submit.prevent="submitLogin"
        >
            <div class="grid gap-4">
                <div class="grid gap-2">
                    <Label for="invited-email-login" class="text-promo-ink">Email</Label>
                    <Input
                        id="invited-email-login"
                        :model-value="email"
                        type="email"
                        readonly
                    />
                    <InputError :message="loginEmailError()" />
                </div>

                <div class="grid gap-2">
                    <Label for="login-password" class="text-promo-ink">Password</Label>
                    <PasswordInput
                        id="login-password"
                        v-model="loginForm.password"
                        required
                        autocomplete="current-password"
                        placeholder="Password"
                    />
                    <InputError :message="loginForm.errors.password" />
                </div>
            </div>

            <Button type="submit" class="w-full rounded-full bg-promo-primary text-white hover:bg-promo-primary-strong" :disabled="loginForm.processing">
                <Spinner v-if="loginForm.processing" />
                Log in and accept invite
            </Button>
        </form>
    </AuthLayout>
</template>
