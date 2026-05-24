<script setup>
import QRCode from 'qrcode';
import { onMounted, ref, watch } from 'vue';

const props = defineProps({
    value: {
        type: String,
        required: true,
    },
});

const dataUrl = ref('');

async function renderCode() {
    dataUrl.value = await QRCode.toDataURL(props.value, {
        margin: 1,
        width: 240,
    });
}

onMounted(renderCode);
watch(() => props.value, renderCode);
</script>

<template>
    <div class="inline-flex rounded-[1.6rem] border border-teachify-line bg-white p-4 shadow-sm">
        <img v-if="dataUrl" :src="dataUrl" alt="QR code" class="h-56 w-56 rounded-xl" />
    </div>
</template>
