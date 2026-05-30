<script setup>
import { Head } from '@inertiajs/vue3';
import { BrowserQRCodeReader } from '@zxing/browser';
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import AppShell from '../../Layouts/AppShell.vue';

const video = ref(null);
const scannerReady = ref(false);
const scannerSupported = ref(false);
const scanning = ref(false);
const errorMessage = ref('');
const codeReader = new BrowserQRCodeReader();
let scannerControls = null;
let mediaStream = null;

async function startScanner() {
    if (scanning.value) {
        return;
    }

    if (!window.isSecureContext) {
        errorMessage.value = 'Camera access requires HTTPS. Use your phone camera to open the session QR link instead.';
        return;
    }

    if (!('mediaDevices' in navigator) || !('getUserMedia' in navigator.mediaDevices)) {
        errorMessage.value = 'Camera access is not available in this browser.';
        return;
    }

    try {
        errorMessage.value = '';
        scannerSupported.value = true;
        scanning.value = true;

        const preview = await waitForVideoPreview();

        if (!preview) {
            scanning.value = false;
            errorMessage.value = 'Camera preview is not ready. Try again or use your phone camera directly.';
            return;
        }

        mediaStream = await navigator.mediaDevices.getUserMedia({
            audio: false,
            video: { facingMode: { ideal: 'environment' } },
        });

        scannerControls = await codeReader.decodeFromStream(
            mediaStream,
            preview,
            (result, error, controls) => {
                if (!result) {
                    return;
                }

                controls.stop();
                scannerControls = null;
                scannerReady.value = false;
                scanning.value = false;
                window.location.href = result.getText();
            },
        );
        scannerReady.value = true;
    } catch (error) {
        scannerSupported.value = false;
        scanning.value = false;
        errorMessage.value = 'Unable to open the camera. Allow camera access and try again.';
    }
}

async function waitForVideoPreview() {
    for (let attempt = 0; attempt < 20; attempt += 1) {
        await nextTick();

        if (video.value) {
            return video.value;
        }

        await new Promise((resolve) => window.setTimeout(resolve, 50));
    }

    return null;
}

function stopScanner() {
    scanning.value = false;
    scannerReady.value = false;

    if (scannerControls) {
        scannerControls.stop();
        scannerControls = null;
    }

    if (mediaStream) {
        mediaStream.getTracks().forEach((track) => track.stop());
        mediaStream = null;
    }
}

onMounted(async () => {
    await nextTick();
    startScanner();
});

onBeforeUnmount(() => {
    stopScanner();
});
</script>

<template>
    <Head title="Scan Attendance" />
    <AppShell title="Scan Attendance">
        <section class="teachify-card mx-auto max-w-4xl rounded-[1.8rem] p-5 sm:p-6">
            <div class="max-w-2xl">
                <h2 class="text-2xl font-black tracking-tight text-teachify-ink">Scan session QR code</h2>
                <p class="mt-2 text-sm font-medium text-teachify-muted">
                    Open the camera, point it at the QR code shown by the teacher, and you will be taken directly to the attendance confirmation page for that session.
                </p>
            </div>

            <div class="mt-6 grid gap-5 lg:grid-cols-[minmax(0,1.2fr)_minmax(280px,0.8fr)]">
                <div class="overflow-hidden rounded-[1.6rem] border border-teachify-line bg-slate-950">
                    <video ref="video" playsinline muted class="aspect-[4/3] w-full object-cover"></video>
                </div>

                <aside class="space-y-4 rounded-[1.6rem] border border-teachify-line bg-white p-4">
                    <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm font-medium text-teachify-muted">
                        <span class="font-black text-teachify-ink">Status:</span>
                        <span v-if="scannerReady && scanning" class="text-teachify-blue"> Camera is live and scanning.</span>
                        <span v-else-if="scanning && !errorMessage" class="text-teachify-blue"> Waiting for camera permission...</span>
                        <span v-else-if="scannerSupported && !scanning" class="text-teachify-muted"> Scanner stopped.</span>
                        <span v-else-if="errorMessage" class="text-amber-700"> Camera unavailable.</span>
                        <span v-else> Preparing camera...</span>
                    </div>

                    <div v-if="errorMessage" class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">
                        {{ errorMessage }}
                    </div>

                    <div class="space-y-3 text-sm font-medium text-teachify-muted">
                        <div class="rounded-2xl bg-slate-50 px-4 py-3">Week starts on Saturday and your dashboard shows this week’s sessions.</div>
                        <div class="rounded-2xl bg-slate-50 px-4 py-3">If scanning fails here, use your phone camera app to open the teacher’s QR code.</div>
                        <div class="rounded-2xl bg-slate-50 px-4 py-3">After the QR opens, sign in with your student code and password if asked.</div>
                    </div>
                </aside>
            </div>
        </section>
    </AppShell>
</template>
