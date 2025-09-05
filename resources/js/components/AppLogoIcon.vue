<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <img 
        src="https://media.licdn.com/dms/image/v2/D4E0BAQH7CZ2y6sh1sw/company-logo_200_200/company-logo_200_200/0/1724182439857/sc_servicos_ao_consumidor_logo?e=2147483647&v=beta&t=MSGoAJSbCWGHKR4tIo35KZ4_85_7GgPH9iyB5J1lRhI"
        alt="SC Serviços Logo"
        :class="className"
        v-bind="$attrs"
    />
</template>
