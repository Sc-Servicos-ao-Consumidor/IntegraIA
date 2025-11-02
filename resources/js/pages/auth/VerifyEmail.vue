<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { ref, nextTick } from 'vue';

defineProps<{
    status?: string;
}>();

// Toast notification system
const toasts = ref([]);
let toastIdCounter = 0;

function showToast(message: string, type: 'success' | 'error' | 'warning' | 'info' = 'info', duration = 5000) {
    const toast = {
        id: ++toastIdCounter,
        message,
        type,
        visible: false
    };
    
    toasts.value.push(toast);
    
    // Show toast with animation (ensure DOM has rendered first)
    const newToastIndex = toasts.value.length - 1;
    nextTick(() => {
        const schedule = typeof requestAnimationFrame === 'function'
            ? requestAnimationFrame
            : (fn: (t: number) => void) => setTimeout(() => fn(Date.now()), 16);
        schedule(() => {
            if (toasts.value[newToastIndex]) {
                toasts.value[newToastIndex].visible = true;
            }
        });
    });
    
    // Auto-remove toast after duration
    setTimeout(() => {
        removeToast(toast.id);
    }, duration);
}

function removeToast(id: number) {
    const index = toasts.value.findIndex(t => t.id === id);
    if (index > -1) {
        toasts.value[index].visible = false;
        setTimeout(() => {
            toasts.value.splice(index, 1);
        }, 300);
    }
}

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'), {
        onSuccess: () => {
            showToast('Email de verificação reenviado com sucesso!', 'success');
        },
        onError: (errors) => {
            const errorMessage = Object.values(errors)[0] || 'Erro ao reenviar email de verificação. Tente novamente.';
            showToast(`Erro: ${errorMessage}`, 'error');
        }
    });
};
</script>

<template>
    <AuthLayout title="Verify email" description="Please verify your email address by clicking on the link we just emailed to you.">
        <Head title="Email verification" />

        <!-- Toast Notifications -->
        <div class="fixed top-4 right-4 z-50 space-y-2">
            <div
                v-for="toast in toasts"
                :key="toast.id"
                :class="[
                    'px-4 py-3 rounded-lg shadow-lg max-w-sm transform transition-all duration-300',
                    toast.type === 'success' ? 'bg-green-500 text-white' : '',
                    toast.type === 'error' ? 'bg-red-500 text-white' : '',
                    toast.type === 'warning' ? 'bg-yellow-500 text-white' : '',
                    toast.type === 'info' ? 'bg-blue-500 text-white' : '',
                    toast.visible ? 'translate-x-0 opacity-100' : 'translate-x-full opacity-0'
                ]"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span v-if="toast.type === 'success'" class="text-lg">✅</span>
                        <span v-else-if="toast.type === 'error'" class="text-lg">❌</span>
                        <span v-else-if="toast.type === 'warning'" class="text-lg">⚠️</span>
                        <span v-else-if="toast.type === 'info'" class="text-lg">ℹ️</span>
                        <span class="font-medium">{{ toast.message }}</span>
                    </div>
                    <button
                        @click="removeToast(toast.id)"
                        class="ml-2 text-white hover:text-gray-200 focus:outline-none"
                    >
                        ×
                    </button>
                </div>
            </div>
        </div>

        <div v-if="status === 'verification-link-sent'" class="mb-4 text-center text-sm font-medium text-green-600">
            A new verification link has been sent to the email address you provided during registration.
        </div>

        <form @submit.prevent="submit" class="space-y-6 text-center">
            <Button :disabled="form.processing" variant="secondary">
                <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                Resend verification email
            </Button>

            <TextLink :href="route('logout')" method="post" as="button" class="mx-auto block text-sm"> Log out </TextLink>
        </form>
    </AuthLayout>
</template>
