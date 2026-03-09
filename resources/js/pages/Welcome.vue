<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Package, ShieldCheck, Truck, Users } from 'lucide-vue-next';
import { dashboard, login } from '@/routes';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);

const form = useForm({
    email: '',
    password: 'password',
    remember: true,
});

const loginAs = (email: string) => {
    form.email = email;
    form.post('/login');
};
</script>

<template>
    <Head title="Logistics CRM Demo">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div
        class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]"
    >
        <header
            class="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-4xl"
        >
            <nav class="flex items-center justify-end gap-4">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                    >
                        Developer Log in
                    </Link>
                </template>
            </nav>
        </header>
        <div
            class="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0"
        >
            <main
                class="flex w-full max-w-[335px] flex-col-reverse overflow-hidden rounded-lg shadow-xl ring-1 ring-zinc-200 lg:max-w-4xl lg:flex-row dark:ring-zinc-800"
            >
                <div
                    class="flex-1 rounded-br-lg rounded-bl-lg bg-white p-6 pb-12 text-[13px] leading-[20px] lg:rounded-tl-lg lg:rounded-br-none lg:p-16 dark:bg-[#161615] dark:text-[#EDEDEC]"
                >
                    <div
                        class="mb-6 inline-flex items-center rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 dark:border-zinc-800 dark:bg-zinc-900/50"
                    >
                        <div
                            class="mr-2 h-2 w-2 animate-pulse rounded-full bg-emerald-500"
                        ></div>
                        <span
                            class="text-xs font-semibold text-zinc-600 dark:text-zinc-400"
                            >Portfolio Demo Environment</span
                        >
                    </div>

                    <h1
                        class="mb-2 text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100"
                    >
                        Logistics CRM
                    </h1>
                    <p
                        class="mb-8 max-w-md text-sm text-zinc-500 dark:text-zinc-400"
                    >
                        Advanced B2B SaaS for freight management. Built with
                        <span class="font-medium text-indigo-500"
                            >Laravel 12 (Octane)</span
                        >,
                        <span class="font-medium text-emerald-500">Vue 3</span>,
                        and
                        <span class="font-medium text-pink-500"
                            >Pest (100% Coverage)</span
                        >. Demonstrates Pipeline patterns, strict Multi-tenancy,
                        and automated PDF Generation via Queues & S3.
                    </p>

                    <h2
                        class="mb-4 text-xs font-bold tracking-widest text-zinc-400 uppercase dark:text-zinc-500"
                    >
                        1-Click Live Setup
                    </h2>

                    <div class="space-y-3">
                        <button
                            @click="loginAs('admin@gmail.com')"
                            :disabled="form.processing"
                            class="group flex w-full items-center justify-between rounded-xl border border-zinc-200 bg-white p-4 text-left transition hover:border-indigo-500 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-indigo-500"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400"
                                >
                                    <ShieldCheck class="size-5" />
                                </div>
                                <div>
                                    <p
                                        class="font-medium text-zinc-900 dark:text-zinc-100"
                                    >
                                        Sign in as Administrator
                                    </p>
                                    <p
                                        class="text-xs text-zinc-500 dark:text-zinc-400"
                                    >
                                        Full system access, fleet tracking, user
                                        management.
                                    </p>
                                </div>
                            </div>
                            <span
                                class="mr-2 text-sm font-medium text-indigo-500 opacity-0 transition group-hover:opacity-100"
                                >Login &rarr;</span
                            >
                        </button>

                        <button
                            @click="loginAs('manager@gmail.com')"
                            :disabled="form.processing"
                            class="group flex w-full items-center justify-between rounded-xl border border-zinc-200 bg-white p-4 text-left transition hover:border-emerald-500 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-emerald-500"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400"
                                >
                                    <Truck class="size-5" />
                                </div>
                                <div>
                                    <p
                                        class="font-medium text-zinc-900 dark:text-zinc-100"
                                    >
                                        Sign in as Route Manager
                                    </p>
                                    <p
                                        class="text-xs text-zinc-500 dark:text-zinc-400"
                                    >
                                        Manage company orders, calculate
                                        pricing, generate documents.
                                    </p>
                                </div>
                            </div>
                            <span
                                class="mr-2 text-sm font-medium text-emerald-500 opacity-0 transition group-hover:opacity-100"
                                >Login &rarr;</span
                            >
                        </button>

                        <button
                            @click="loginAs('observer@gmail.com')"
                            :disabled="form.processing"
                            class="group flex w-full items-center justify-between rounded-xl border border-zinc-200 bg-white p-4 text-left transition hover:border-amber-500 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-amber-500"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400"
                                >
                                    <Users class="size-5" />
                                </div>
                                <div>
                                    <p
                                        class="font-medium text-zinc-900 dark:text-zinc-100"
                                    >
                                        Sign in as Observer
                                    </p>
                                    <p
                                        class="text-xs text-zinc-500 dark:text-zinc-400"
                                    >
                                        Restricted Read-Only role for warehouse
                                        staff.
                                    </p>
                                </div>
                            </div>
                            <span
                                class="mr-2 text-sm font-medium text-amber-500 opacity-0 transition group-hover:opacity-100"
                                >Login &rarr;</span
                            >
                        </button>
                    </div>

                    <div
                        class="mt-8 flex items-center gap-2 text-xs text-zinc-400 dark:text-zinc-500"
                        v-if="form.errors.email"
                    >
                        <span class="text-red-500">{{
                            form.errors.email
                        }}</span>
                    </div>
                </div>
                <!-- Right Side Image Block -->
                <div
                    class="relative hidden flex-col items-center justify-center overflow-hidden rounded-r-lg bg-zinc-900 lg:flex lg:w-5/12"
                >
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-indigo-500/20 via-transparent to-emerald-500/20"
                    ></div>

                    <!-- Abstract Grid pattern -->
                    <svg
                        class="absolute inset-0 h-full w-full opacity-[0.03]"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <defs>
                            <pattern
                                id="gridPattern"
                                width="40"
                                height="40"
                                patternUnits="userSpaceOnUse"
                            >
                                <path
                                    d="M 40 0 L 0 0 0 40"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1"
                                />
                            </pattern>
                        </defs>
                        <rect
                            width="100%"
                            height="100%"
                            fill="url(#gridPattern)"
                        />
                    </svg>

                    <div class="relative z-10 flex flex-col items-center">
                        <div
                            class="rounded-2xl border border-zinc-700/50 bg-zinc-800/80 p-6 shadow-2xl backdrop-blur-xl"
                        >
                            <Package
                                class="size-20 tracking-tighter text-indigo-400"
                            />
                        </div>
                        <div class="mt-8 space-y-2 text-center text-zinc-400">
                            <p class="font-mono text-xs text-zinc-500">
                                github.com/lisovyi3441/logistics-crm
                            </p>
                            <a
                                href="https://github.com/lisovyi3441/logistics-crm"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center text-sm font-medium text-zinc-300 transition hover:text-white"
                            >
                                View Source Code &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        <div class="hidden h-14.5 lg:block"></div>
    </div>
</template>
