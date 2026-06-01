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
    <Head :title="$t('admin.settings.title')" />
    <AppShell :title="$t('admin.settings.title')">
        <form class="space-y-5" @submit.prevent="submit">
            <section class="teachify-card rounded-[1.6rem] p-5">
                <h2 class="text-lg font-black">{{ $t('admin.settings.brand') }}</h2>
                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    <TextInput v-model="form.name" :label="$t('fields.name')" required :error="form.errors.name" />
                    <TextInput v-model="form.tagline" :label="$t('fields.tagline')" :error="form.errors.tagline" />
                    <FileInput :label="$t('fields.logo')" :error="form.errors.logo" @change="form.logo = $event" />
                    <FileInput :label="$t('fields.favicon')" :error="form.errors.favicon" @change="form.favicon = $event" />
                </div>
                <div class="mt-4 flex flex-wrap gap-4">
                    <div v-if="media.logoUrl" class="rounded-2xl border border-teachify-line bg-white p-4">
                        <div class="mb-2 text-xs font-black uppercase text-teachify-muted">{{ $t('admin.settings.currentLogo') }}</div>
                        <img :src="media.logoUrl" :alt="$t('admin.settings.currentLogo')" class="h-14 w-auto" />
                    </div>
                    <div v-if="media.faviconUrl" class="rounded-2xl border border-teachify-line bg-white p-4">
                        <div class="mb-2 text-xs font-black uppercase text-teachify-muted">{{ $t('admin.settings.currentFavicon') }}</div>
                        <img :src="media.faviconUrl" :alt="$t('admin.settings.currentFavicon')" class="h-14 w-14 rounded-xl object-contain" />
                    </div>
                </div>
            </section>

            <section class="teachify-card rounded-[1.6rem] p-5">
                <h2 class="text-lg font-black">{{ $t('admin.settings.contact') }}</h2>
                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    <TextInput v-model="form.contact_email" :label="$t('fields.contactEmail')" type="email" :error="form.errors.contact_email" />
                    <TextInput v-model="form.contact_phone" :label="$t('fields.contactPhone')" :error="form.errors.contact_phone" />
                    <TextInput v-model="form.city" :label="$t('fields.city')" :error="form.errors.city" />
                    <TextInput v-model="form.country" :label="$t('fields.country')" :error="form.errors.country" />
                </div>
                <div class="mt-4">
                    <TextareaInput v-model="form.address" :label="$t('fields.address')" :error="form.errors.address" />
                </div>
            </section>

            <section class="teachify-card rounded-[1.6rem] p-5">
                <h2 class="text-lg font-black">{{ $t('admin.settings.socialLinks') }}</h2>
                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    <TextInput v-model="form.facebook_url" :label="$t('fields.facebookUrl')" :error="form.errors.facebook_url" />
                    <TextInput v-model="form.instagram_url" :label="$t('fields.instagramUrl')" :error="form.errors.instagram_url" />
                    <TextInput v-model="form.linkedin_url" :label="$t('fields.linkedInUrl')" :error="form.errors.linkedin_url" />
                    <TextInput v-model="form.youtube_url" :label="$t('fields.youtubeUrl')" :error="form.errors.youtube_url" />
                    <TextInput v-model="form.website_url" :label="$t('fields.websiteUrl')" :error="form.errors.website_url" />
                </div>
            </section>

            <section class="teachify-card rounded-[1.6rem] p-5">
                <h2 class="text-lg font-black">{{ $t('admin.settings.localizationAndReports') }}</h2>
                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    <SelectInput
                        v-model="form.timezone"
                        :label="$t('fields.timezone')"
                        required
                        :options="timezones.map((timezone) => ({ value: timezone, label: timezone }))"
                        :error="form.errors.timezone"
                    />
                    <TextInput v-model="form.locale" :label="$t('fields.locale')" required :error="form.errors.locale" />
                </div>
                <div class="mt-4 space-y-4">
                    <TextareaInput v-model="form.report_footer_text" :label="$t('fields.reportFooterText')" :error="form.errors.report_footer_text" />
                    <CheckboxInput v-model="form.show_logo_on_reports" :label="$t('fields.showLogoOnReports')" :error="form.errors.show_logo_on_reports" />
                </div>
            </section>

            <Button type="submit" :disabled="form.processing">{{ $t('admin.settings.save') }}</Button>
        </form>
    </AppShell>
</template>
