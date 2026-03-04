<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Credential {
    id: number;
    name: string | null;
    environment: string;
    ebay_user_id: string;
    is_active: boolean;
    has_refresh_token: boolean;
    refresh_token_expires_at: string | null;
    is_refresh_token_expired: boolean;
    created_at: string | null;
    updated_at: string | null;
}

const props = defineProps<{
    credential: Credential;
}>();

const page = usePage();

const flash = computed(() => page.props.flash as { success?: string; error?: string } | undefined);

function formatDate(dateString: string | null): string {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('en-GB', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <Head :title="`Credential: ${credential.ebay_user_id}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Credential: {{ credential.ebay_user_id }}
                </h2>
                <div class="flex gap-2">
                    <Link
                        :href="route('ebay.credentials.edit', credential.id)"
                        class="inline-flex items-center rounded-md bg-yellow-500 px-4 py-2 text-sm font-medium text-white hover:bg-yellow-600"
                    >
                        Edit
                    </Link>
                    <Link
                        :href="route('ebay.credentials.index')"
                        class="inline-flex items-center rounded-md bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300"
                    >
                        Back to List
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div v-if="flash?.success" class="mb-4 rounded-md bg-green-50 p-4">
                    <p class="text-sm text-green-800">{{ flash.success }}</p>
                </div>
                <div v-if="flash?.error" class="mb-4 rounded-md bg-red-50 p-4">
                    <p class="text-sm text-red-800">{{ flash.error }}</p>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <dl class="divide-y divide-gray-200">
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                <dt class="text-sm font-medium text-gray-500">ID</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ credential.id }}</dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                <dt class="text-sm font-medium text-gray-500">eBay User ID</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ credential.ebay_user_id }}</dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                <dt class="text-sm font-medium text-gray-500">Name</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ credential.name || '-' }}</dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                <dt class="text-sm font-medium text-gray-500">Environment</dt>
                                <dd class="mt-1 sm:col-span-2 sm:mt-0">
                                    <span
                                        :class="credential.environment === 'production'
                                            ? 'bg-red-100 text-red-800'
                                            : 'bg-yellow-100 text-yellow-800'"
                                        class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                    >
                                        {{ credential.environment }}
                                    </span>
                                </dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1 sm:col-span-2 sm:mt-0">
                                    <span
                                        :class="credential.is_active
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-gray-100 text-gray-800'"
                                        class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                    >
                                        {{ credential.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                <dt class="text-sm font-medium text-gray-500">Refresh Token</dt>
                                <dd class="mt-1 text-sm sm:col-span-2 sm:mt-0">
                                    <span v-if="credential.has_refresh_token" class="text-green-600">Present (encrypted)</span>
                                    <span v-else class="text-red-600">Not set</span>
                                </dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                <dt class="text-sm font-medium text-gray-500">Token Expires At</dt>
                                <dd class="mt-1 text-sm sm:col-span-2 sm:mt-0">
                                    <span v-if="credential.is_refresh_token_expired" class="text-red-600 font-medium">
                                        Expired ({{ formatDate(credential.refresh_token_expires_at) }})
                                    </span>
                                    <span v-else class="text-gray-900">
                                        {{ formatDate(credential.refresh_token_expires_at) }}
                                    </span>
                                </dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                <dt class="text-sm font-medium text-gray-500">Created</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ formatDate(credential.created_at) }}</dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                <dt class="text-sm font-medium text-gray-500">Updated</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ formatDate(credential.updated_at) }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
