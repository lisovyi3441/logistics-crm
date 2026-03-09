<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

defineProps<{
    status?: string;
}>();

const bypassing = ref(false);

const bypassVerification = () => {
    bypassing.value = true;
    router.post(
        '/email/verify/demo-bypass',
        {},
        {
            onFinish: () => (bypassing.value = false),
        },
    );
};
</script>

<template>
    <AuthLayout
        title="Verify email"
        description="Please verify your email address by clicking on the link we just emailed to you."
    >
        <Head title="Email verification" />

        <!-- Demo Mode Banner -->
        <div
            class="mb-6 rounded-md border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/50 dark:bg-amber-950/30"
        >
            <div class="flex">
                <div class="shrink-0">
                    <svg
                        class="size-5 text-amber-400"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        aria-hidden="true"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3
                        class="text-sm font-medium text-amber-800 dark:text-amber-200"
                    >
                        Demo Environment
                    </h3>
                    <div
                        class="mt-2 text-sm text-amber-700 dark:text-amber-300"
                    >
                        <p>
                            Real emails are not sent in this environment. You
                            can instantly bypass this verification step.
                        </p>
                    </div>
                    <div class="mt-4">
                        <div class="-mx-2 -my-1.5 flex">
                            <Button
                                @click="bypassVerification"
                                :disabled="bypassing"
                                variant="default"
                                class="bg-amber-600 text-white hover:bg-amber-700 dark:bg-amber-700 dark:text-amber-100 dark:hover:bg-amber-600"
                            >
                                <Spinner v-if="bypassing" class="mr-2" />
                                Bypass Verification (Demo)
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="status === 'verification-link-sent'"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            A new verification link has been sent to the email address you
            provided during registration.
        </div>

        <Form
            v-bind="send.form()"
            class="space-y-6 text-center"
            v-slot="{ processing }"
        >
            <Button :disabled="processing" variant="secondary" class="w-full">
                <Spinner v-if="processing" />
                Resend verification email
            </Button>

            <TextLink
                :href="logout()"
                as="button"
                class="mx-auto block text-sm"
            >
                Log out
            </TextLink>
        </Form>
    </AuthLayout>
</template>
