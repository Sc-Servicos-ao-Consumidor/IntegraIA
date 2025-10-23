<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { ref, nextTick } from 'vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
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
    
    // Show toast with animation
    nextTick(() => {
        toast.visible = true;
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
    email: '',  
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
        onError: (errors) => {
            // Show error toast for authentication failures
            const errorMessage = Object.values(errors)[0] || 'Erro de autenticação. Verifique suas credenciais.';
            showToast(`Erro: ${errorMessage}`, 'error');
        }
    });
};
</script>

<template>
    <AuthBase title="Entre na sua conta" description="Digite seu e-mail e senha abaixo para entrar">
        <Head title="Entrar" />

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

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">E-mail</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        v-model="form.email"
                        placeholder="email@scservicos.com.br"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Senha</Label>
                        <TextLink v-if="canResetPassword" :href="route('password.request')" class="text-sm" :tabindex="5">
                            Esqueceu a senha?
                        </TextLink>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        v-model="form.password"
                        placeholder="Senha"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <Label for="remember" class="flex items-center space-x-3">
                        <Checkbox id="remember" v-model="form.remember" :tabindex="3" />
                        <span>Manter Conectado</span>
                    </Label>
                </div>

                <Button type="submit" class="mt-4 w-full" :tabindex="4" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Entrar
                </Button>
            </div>

            <!-- <div class="text-center text-sm text-muted-foreground">
                Não tem uma conta?
                <TextLink :href="route('register')" :tabindex="5">Criar conta</TextLink>
            </div> -->
        </form>
    </AuthBase>
</template>
