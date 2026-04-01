<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { useTemplateRef } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { useTranslations } from '@/composables/useTranslations';
import { destroy } from '@/routes/profile';

const passwordInput = useTemplateRef('passwordInput');
const { t } = useTranslations();
</script>

<template>
    <div class="space-y-6">
        <Heading
            variant="small"
            :title="t('account_settings.delete_account.title')"
            :description="t('account_settings.delete_account.description')"
        />
        <div class="space-y-4 border-t border-brand-border/70 pt-6">
            <p class="text-sm leading-6 text-brand-muted">
                {{ t('account_settings.delete_account.warning') }}
            </p>
            <Dialog>
                <DialogTrigger as-child>
                    <Button variant="destructive" data-test="delete-user-button"
                        >{{ t('account_settings.delete_account.trigger') }}</Button
                    >
                </DialogTrigger>
                <DialogContent class="sm:max-w-md">
                    <Form
                        v-bind="destroy.form()"
                        reset-on-success
                        @error="() => passwordInput?.focus()"
                        :options="{
                            preserveScroll: true,
                        }"
                        class="space-y-6"
                        v-slot="{ errors, processing, reset, clearErrors }"
                    >
                        <DialogHeader class="space-y-2 text-left">
                            <DialogTitle>
                                {{ t('account_settings.delete_account.confirm_title') }}
                            </DialogTitle>
                            <DialogDescription>
                                {{ t('account_settings.delete_account.confirm_description') }}
                            </DialogDescription>
                        </DialogHeader>

                        <div class="grid gap-2">
                            <Label for="password" class="sr-only"
                                >{{ t('auth.shared.password') }}</Label
                            >
                            <PasswordInput
                                id="password"
                                name="password"
                                ref="passwordInput"
                                :placeholder="t('auth.shared.password')"
                            />
                            <InputError :message="errors.password" />
                        </div>

                        <DialogFooter class="gap-2">
                            <DialogClose as-child>
                                <Button
                                    variant="secondary"
                                    @click="
                                        () => {
                                            clearErrors();
                                            reset();
                                        }
                                    "
                                >
                                    {{ t('auth.shared.cancel') }}
                                </Button>
                            </DialogClose>

                            <Button
                                type="submit"
                                variant="destructive"
                                :disabled="processing"
                                data-test="confirm-delete-user-button"
                            >
                                {{ t('account_settings.delete_account.trigger') }}
                            </Button>
                        </DialogFooter>
                    </Form>
                </DialogContent>
            </Dialog>
        </div>
    </div>
</template>
