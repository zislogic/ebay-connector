<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const flash = computed(() => page.props.flash as { success?: string; error?: string } | undefined);

const form = useForm({
    environment: 'sandbox',
    callback_url: '',
});

function submit() {
    form.post(route('ebay.credentials.codeExchange'));
}
</script>

<template>
    <Head title="Enter Authorization Code" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Enter Authorization Code
                </h2>
                <Link
                    :href="route('ebay.credentials.index')"
                    class="inline-flex items-center rounded-md bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300"
                >
                    Back to List
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div v-if="flash?.success" class="mb-4 rounded-md bg-green-50 p-4">
                    <p class="text-sm text-green-800">{{ flash.success }}</p>
                </div>
                <div v-if="flash?.error" class="mb-4 rounded-md bg-red-50 p-4">
                    <p class="text-sm text-red-800">{{ flash.error }}</p>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="mb-6">
                            <p class="text-sm text-gray-600">
                                If the automatic callback did not work, paste the full callback URL from eBay
                                below. Select the environment that matches the credentials you used during
                                the consent flow.
                            </p>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <label for="environment" class="block text-sm font-medium text-gray-700">
                                    Environment
                                </label>
                                <select
                                    id="environment"
                                    v-model="form.environment"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                    <option value="sandbox">Sandbox</option>
                                    <option value="production">Production</option>
                                </select>
                                <p v-if="form.errors.environment" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.environment }}
                                </p>
                            </div>

                            <div>
                                <label for="callback_url" class="block text-sm font-medium text-gray-700">
                                    Callback URL
                                </label>
                                <input
                                    id="callback_url"
                                    v-model="form.callback_url"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Paste the full callback URL from eBay here"
                                />
                                <p class="mt-1 text-xs text-gray-500">
                                    The URL should contain a <code>code</code> parameter, e.g. <code>https://...?code=v^1.1#i^1...</code>
                                </p>
                                <p v-if="form.errors.callback_url" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.callback_url }}
                                </p>
                            </div>

                            <div>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-3 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    Exchange Code
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
