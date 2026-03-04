<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

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

const form = useForm({
    name: props.credential.name || '',
    is_active: props.credential.is_active,
});

function submit() {
    form.put(route('ebay.credentials.update', props.credential.id));
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
    <Head :title="`Edit Credential: ${credential.ebay_user_id}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Edit Credential: {{ credential.ebay_user_id }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Read-only fields -->
                            <div>
                                <label class="block text-sm font-medium text-gray-500">eBay User ID</label>
                                <p class="mt-1 text-sm text-gray-900">{{ credential.ebay_user_id }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Environment</label>
                                <p class="mt-1">
                                    <span
                                        :class="credential.environment === 'production'
                                            ? 'bg-red-100 text-red-800'
                                            : 'bg-yellow-100 text-yellow-800'"
                                        class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                    >
                                        {{ credential.environment }}
                                    </span>
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Token Expires At</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ formatDate(credential.refresh_token_expires_at) }}
                                </p>
                            </div>

                            <hr />

                            <!-- Editable fields -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Optional display name"
                                />
                                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                            </div>

                            <div class="flex items-center gap-3">
                                <input
                                    id="is_active"
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
                                <p v-if="form.errors.is_active" class="text-sm text-red-600">{{ form.errors.is_active }}</p>
                            </div>

                            <div class="flex items-center gap-3 pt-4">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    Save Changes
                                </button>
                                <Link
                                    :href="route('ebay.credentials.show', credential.id)"
                                    class="inline-flex items-center rounded-md bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300"
                                >
                                    Cancel
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
