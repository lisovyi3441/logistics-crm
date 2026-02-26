<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';

defineProps<{
    message: string;
    phpVersion: string;
}>();
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex min-h-screen items-center justify-center bg-zinc-950 p-4">
        <Card class="w-100 border-zinc-800 bg-zinc-900 text-zinc-100">
            <CardHeader>
                <CardTitle class="text-xl">System Status</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
                <p class="text-zinc-400">
                    Backend Message:
                    <span class="font-mono text-emerald-400">{{
                        message
                    }}</span>
                </p>
                <p class="text-zinc-400">
                    Running on PHP:
                    <span class="font-bold">{{ phpVersion }}</span>
                </p>

                <Button class="w-full bg-indigo-600 hover:bg-indigo-700">
                    Refresh Data
                </Button>
            </CardContent>
        </Card>
    </div>
</template>

<style scoped></style>
