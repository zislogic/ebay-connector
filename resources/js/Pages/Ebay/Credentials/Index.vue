<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

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
    credentials: Credential[];
    codeExchangeMethod: string;
}>();

const page = usePage();

const flash = computed(() => page.props.flash as { success?: string; error?: string } | undefined);

const confirmingDelete = ref<number | null>(null);

function confirmDelete(id: number) {
    confirmingDelete.value = id;
}

function cancelDelete() {
    confirmingDelete.value = null;
}

function deleteCredential(id: number) {
    router.delete(route('ebay.credentials.destroy', id), {
        onFinish: () => {
            confirmingDelete.value = null;
        },
    });
}

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
    <Head title="eBay Credentials" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    eBay Credentials
                </h2>
                <div class="flex gap-2">
                    <Link
                        :href="route('ebay.credentials.token')"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                    >
                        Generate Token
                    </Link>
                    <Link
                        v-if="codeExchangeMethod === 'manual'"
                        :href="route('ebay.credentials.code')"
                        class="inline-flex items-center rounded-md bg-gray-600 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700"
                    >
                        Enter Code
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div v-if="flash?.success" class="mb-4 rounded-md bg-green-50 p-4">
                    <p class="text-sm text-green-800">{{ flash.success }}</p>
                </div>
                <div v-if="flash?.error" class="mb-4 rounded-md bg-red-50 p-4">
                    <p class="text-sm text-red-800">{{ flash.error }}</p>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <table v-if="credentials.length > 0" class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">eBay User ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Environment</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Active</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Expires At</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="credential in credentials" :key="credential.id">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ credential.id }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ credential.ebay_user_id }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ credential.name || '-' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span
                                            :class="credential.environment === 'production'
                                                ? 'bg-red-100 text-red-800'
                                                : 'bg-yellow-100 text-yellow-800'"
                                            class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                        >
                                            {{ credential.environment }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span
                                            :class="credential.is_active
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-gray-100 text-gray-800'"
                                            class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                        >
                                            {{ credential.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        <span v-if="credential.is_refresh_token_expired" class="text-red-600">Expired</span>
                                        <span v-else>{{ formatDate(credential.refresh_token_expires_at) }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                        <div class="flex gap-3">
                                            <Link
                                                :href="route('ebay.credentials.show', credential.id)"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                View
                                            </Link>
                                            <Link
                                                :href="route('ebay.credentials.edit', credential.id)"
                                                class="text-yellow-600 hover:text-yellow-900"
                                            >
                                                Edit
                                            </Link>
                                            <button
                                                v-if="confirmingDelete !== credential.id"
                                                class="text-red-600 hover:text-red-900"
                                                @click="confirmDelete(credential.id)"
                                            >
                                                Delete
                                            </button>
                                            <span v-else class="flex gap-1">
                                                <button
                                                    class="text-red-800 font-bold hover:text-red-900"
                                                    @click="deleteCredential(credential.id)"
                                                >
                                                    Confirm
                                                </button>
                                                <button
                                                    class="text-gray-500 hover:text-gray-700"
                                                    @click="cancelDelete()"
                                                >
                                                    Cancel
                                                </button>
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div v-else class="text-center py-8 text-gray-500">
                            <p class="text-lg">No eBay credentials found.</p>
                            <p class="mt-2">Click "Generate Token" to connect an eBay account.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
