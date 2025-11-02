<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { ref, nextTick } from 'vue';

// Toast notification system
type Toast = { id: number; message: string; type: 'success' | 'error' | 'warning' | 'info'; visible: boolean };
const toasts = ref<Toast[]>([]);
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

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => {
            form.reset();
        },
        onError: (errors) => {
            const errorMessage = Object.values(errors)[0] || 'Erro ao confirmar senha. Tente novamente.';
            showToast(`Erro: ${errorMessage}`, 'error');
        }
    });
};
</script>

<template>
    <AuthLayout title="Confirm your password" description="This is a secure area of the application. Please confirm your password before continuing.">
        <Head title="Confirm password" />

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

        <form @submit.prevent="submit">
            <div class="space-y-6">
                <div class="grid gap-2">
                    <Label htmlFor="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        class="mt-1 block w-full"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        autofocus
                    />

                    <InputError :message="form.errors.password" />
                </div>

                <div class="flex items-center">
                    <Button class="w-full" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        Confirm Password
                    </Button>
                </div>
            </div>
        </form>
    </AuthLayout>
</template>
