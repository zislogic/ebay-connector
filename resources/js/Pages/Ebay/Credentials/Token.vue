<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    codeExchangeMethod: string;
    authUrl?: string;
    selectedEnvironment?: string;
}>();

const page = usePage();

const flash = computed(() => page.props.flash as { success?: string; error?: string } | undefined);

const form = useForm({
    environment: props.selectedEnvironment || 'sandbox',
});

function submit() {
    form.post(route('ebay.credentials.tokenRedirect'));
}
</script>

<template>
    <Head title="Generate eBay Token" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Generate eBay Token
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
                <div v-if="flash?.error" class="mb-4 rounded-md bg-red-50 p-4">
                    <p class="text-sm text-red-800">{{ flash.error }}</p>
                </div>

                <!-- After POST in manual mode: show link to open eBay -->
                <div v-if="authUrl" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 space-y-6">
                        <div class="rounded-md bg-blue-50 p-4">
                            <p class="text-sm text-blue-800">
                                The eBay consent URL has been generated for the
                                <strong>{{ selectedEnvironment }}</strong> environment.
                                Click the button below to open it in a new tab.
                            </p>
                        </div>

                        <div>
                            <a
                                :href="authUrl"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-3 text-sm font-medium text-white hover:bg-indigo-700"
                            >
                                Open eBay Consent Page
                                <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                        </div>

                        <div class="border-t pt-4">
                            <p class="text-sm text-gray-600 mb-3">
                                After granting consent on eBay, copy the callback URL from your browser
                                and paste it on the Code page.
                            </p>
                            <Link
                                :href="route('ebay.credentials.code')"
                                class="inline-flex items-center rounded-md bg-gray-600 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700"
                            >
                                Go to Enter Code Page
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Initial state: environment selector form -->
                <div v-else class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="mb-6">
                            <p v-if="codeExchangeMethod === 'manual'" class="text-sm text-gray-600">
                                Select an environment and click the button below. eBay will open in a new tab
                                where you can grant consent. After granting consent, copy the callback URL and
                                paste it on the Code page.
                            </p>
                            <p v-else class="text-sm text-gray-600">
                                Select an environment and click the button below. You will be redirected to eBay
                                to grant consent. After granting consent, eBay will redirect you back and the
                                credential will be stored automatically.
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
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-3 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    Go to eBay for Consent
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
