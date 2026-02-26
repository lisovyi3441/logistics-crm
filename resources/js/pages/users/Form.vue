<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { computed } from 'vue';

const props = defineProps<{
    user?: any;
    companies: Array<{ id: number; name: string }>;
    roles: Array<{ id: number; name: string }>;
    default_company_id?: number | string;
    redirect_to?: string;
}>();

const isEditing = !!props.user;
const userData = computed(() => props.user?.data ?? props.user);

const form = useForm({
    name: userData.value?.name || '',
    email: userData.value?.email || '',
    password: '',
    password_confirmation: '',
    company_id: userData.value?.company_id || props.default_company_id || '',
    role: userData.value?.roles?.[0] || '',
});

const submit = () => {
    const urlSuffix = props.redirect_to ? `?redirect_to=${encodeURIComponent(props.redirect_to)}` : '';
    
    if (isEditing) {
        form.put(`/users/${userData.value.id}${urlSuffix}`);
    } else {
        form.post(`/users${urlSuffix}`);
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Edit User' : 'Create User'" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Users', href: '/users' },
            { title: isEditing ? 'Edit' : 'Create', href: '#' },
        ]"
    >
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 max-w-2xl mx-auto w-full">
            <h1 class="text-2xl font-bold dark:text-white">{{ isEditing ? 'Edit User' : 'Create User' }}</h1>
            
            <div class="rounded-xl border border-sidebar-border bg-white p-6 shadow-sm dark:bg-zinc-900 dark:border-zinc-800">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Name *</label>
                        <input id="name" type="text" v-model="form.name" required class="mt-1 block w-full rounded-md border border-zinc-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Email *</label>
                        <input id="email" type="email" v-model="form.email" required class="mt-1 block w-full rounded-md border border-zinc-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="password" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Password <span v-if="!isEditing">*</span> <span v-else class="text-zinc-400 font-normal">(Leave blank to keep current)</span>
                            </label>
                            <input id="password" type="password" v-model="form.password" :required="!isEditing" class="mt-1 block w-full rounded-md border border-zinc-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                            <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Confirm Password</label>
                            <input id="password_confirmation" type="password" v-model="form.password_confirmation" :required="!isEditing && form.password.length > 0" class="mt-1 block w-full rounded-md border border-zinc-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                        </div>
                    </div>

                    <hr class="border-zinc-200 dark:border-zinc-800">

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="role" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">System Role *</label>
                            <select id="role" v-model="form.role" required class="mt-1 block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                                <option value="" disabled>Select a role</option>
                                <option v-for="role in roles" :key="role.id" :value="role.name">
                                    {{ role.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.role" class="mt-1 text-sm text-red-600">{{ form.errors.role }}</p>
                        </div>

                        <div>
                            <label for="company_id" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Assign to Company</label>
                            <select id="company_id" v-model="form.company_id" class="mt-1 block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                                <option value="" :selected="!form.company_id">Global (No Company)</option>
                                <option v-for="company in companies" :key="company.id" :value="company.id">
                                    {{ company.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.company_id" class="mt-1 text-sm text-red-600">{{ form.errors.company_id }}</p>
                            <p class="mt-1 text-xs text-zinc-500" v-if="form.role === 'admin'">Admins usually don't need a specific company.</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                        <Link :href="redirect_to || '/users'" class="px-4 py-2 text-sm font-medium text-zinc-700 bg-white border border-zinc-300 rounded-md shadow-sm hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700 dark:hover:bg-zinc-700">
                            Cancel
                        </Link>
                        <button type="submit" :disabled="form.processing" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                            {{ isEditing ? 'Save Changes' : 'Create User' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
