<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Button from '../../../Components/Button.vue';
import CheckboxInput from '../../../Components/CheckboxInput.vue';
import FileInput from '../../../Components/FileInput.vue';
import SelectInput from '../../../Components/SelectInput.vue';
import TextareaInput from '../../../Components/TextareaInput.vue';
import TextInput from '../../../Components/TextInput.vue';
import AppShell from '../../../Layouts/AppShell.vue';

const props = defineProps({
    settings: Object,
    media: Object,
    timezones: Array,
    action: String,
});

const form = useForm({
    ...props.settings,
    logo: null,
    favicon: null,
});

function submit() {
    form.transform((data) => ({
        ...data,
        _method: 'put',
        show_logo_on_reports: data.show_logo_on_reports ? '1' : '0',
    })).post(props.action, {
        forceFormData: true,
        onSuccess: () => {
            form.logo = null;
            form.favicon = null;
        },
    });
}
</script>

<template>
    <Head title="Settings" />
    <AppShell title="Settings" subtitle="Brand, contact, social, localization, and report defaults.">
        <form class="space-y-5" @submit.prevent="submit">
            <section class="teachify-card rounded-[1.6rem] p-5">
                <h2 class="text-lg font-black">Brand</h2>
                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    <TextInput v-model="form.name" label="Name" required :error="form.errors.name" />
                    <TextInput v-model="form.tagline" label="Tagline" :error="form.errors.tagline" />
                    <FileInput label="Logo" :error="form.errors.logo" @change="form.logo = $event" />
                    <FileInput label="Favicon" :error="form.errors.favicon" @change="form.favicon = $event" />
                </div>
                <div class="mt-4 flex flex-wrap gap-4">
                    <div v-if="media.logoUrl" class="rounded-2xl border border-teachify-line bg-white p-4">
                        <div class="mb-2 text-xs font-black uppercase text-teachify-muted">Current logo</div>
                        <img :src="media.logoUrl" alt="Current logo" class="h-14 w-auto" />
                    </div>
                    <div v-if="media.faviconUrl" class="rounded-2xl border border-teachify-line bg-white p-4">
                        <div class="mb-2 text-xs font-black uppercase text-teachify-muted">Current favicon</div>
                        <img :src="media.faviconUrl" alt="Current favicon" class="h-14 w-14 rounded-xl object-contain" />
                    </div>
                </div>
            </section>

            <section class="teachify-card rounded-[1.6rem] p-5">
                <h2 class="text-lg font-black">Contact</h2>
                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    <TextInput v-model="form.contact_email" label="Contact Email" type="email" :error="form.errors.contact_email" />
                    <TextInput v-model="form.contact_phone" label="Contact Phone" :error="form.errors.contact_phone" />
                    <TextInput v-model="form.city" label="City" :error="form.errors.city" />
                    <TextInput v-model="form.country" label="Country" :error="form.errors.country" />
                </div>
                <div class="mt-4">
                    <TextareaInput v-model="form.address" label="Address" :error="form.errors.address" />
                </div>
            </section>

            <section class="teachify-card rounded-[1.6rem] p-5">
                <h2 class="text-lg font-black">Social links</h2>
                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    <TextInput v-model="form.facebook_url" label="Facebook URL" :error="form.errors.facebook_url" />
                    <TextInput v-model="form.instagram_url" label="Instagram URL" :error="form.errors.instagram_url" />
                    <TextInput v-model="form.linkedin_url" label="LinkedIn URL" :error="form.errors.linkedin_url" />
                    <TextInput v-model="form.youtube_url" label="YouTube URL" :error="form.errors.youtube_url" />
                    <TextInput v-model="form.website_url" label="Website URL" :error="form.errors.website_url" />
                </div>
            </section>

            <section class="teachify-card rounded-[1.6rem] p-5">
                <h2 class="text-lg font-black">Localization and reports</h2>
                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    <SelectInput
                        v-model="form.timezone"
                        label="Timezone"
                        required
                        :options="timezones.map((timezone) => ({ value: timezone, label: timezone }))"
                        :error="form.errors.timezone"
                    />
                    <TextInput v-model="form.locale" label="Locale" required :error="form.errors.locale" />
                </div>
                <div class="mt-4 space-y-4">
                    <TextareaInput v-model="form.report_footer_text" label="Report Footer Text" :error="form.errors.report_footer_text" />
                    <CheckboxInput v-model="form.show_logo_on_reports" label="Show logo on reports" :error="form.errors.show_logo_on_reports" />
                </div>
            </section>

            <Button type="submit" :disabled="form.processing">Save Settings</Button>
        </form>
    </AppShell>
</template>
