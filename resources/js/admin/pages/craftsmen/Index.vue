<script setup>

import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

import axios from 'axios';

import { useApiError } from '../../composables/useApiError';

import { useToast } from '../../composables/useToast';

import { useLocale } from '../../composables/useLocale';

import { itemsFrom, metaFrom } from '../../composables/usePaginatedList';

import PaginationBar from '../../components/PaginationBar.vue';



const { message } = useApiError();

const toast = useToast();

const { t, locale } = useLocale();

const craftsmen = ref([]);

const filter = ref('');

const error = ref('');

const loading = ref(true);

const page = ref(1);

const meta = ref(metaFrom({}, 1));

const exporting = ref(false);

const selectedCraftsman = ref(null);
const statusDraft = ref(null);
const statusNote = ref('');
const statusSaving = ref(false);



const statuses = computed(() => [

    { value: '', label: t('all') },

    { value: 'pending', label: t('craftsmen.pending') },

    { value: 'reviewing', label: t('craftsmen.reviewing') },

    { value: 'accepted', label: t('craftsmen.accepted') },

    { value: 'rejected', label: t('craftsmen.rejected') },

]);



function statusLabel(value) {

    return statuses.value.find((item) => item.value === value)?.label || value;

}

function specialtyList(value) {

    if (Array.isArray(value)) {

        return value.filter((specialty) => typeof specialty === 'string' && specialty.trim());

    }

    if (typeof value !== 'string' || !value.trim()) {

        return [];

    }

    try {

        const decoded = JSON.parse(value);

        return Array.isArray(decoded) ? specialtyList(decoded) : [value];

    } catch {

        return [value];

    }

}



function formatSubmittedAt(value) {

    if (!value) {

        return '-';

    }



    return new Intl.DateTimeFormat(locale.value === 'en' ? 'en-GB' : 'ar-EG', {

        dateStyle: 'medium',

        timeStyle: 'short',

    }).format(new Date(value));

}



async function load(nextPage = page.value) {

    loading.value = true;

    error.value = '';

    try {

        const { data } = await axios.get('/api/admin/craftsmen', {

            params: {

                page: nextPage,

                per_page: 10,

                ...(filter.value ? { status: filter.value } : {}),

            },

        });

        const rows = itemsFrom(data);

        const nextMeta = metaFrom(data, nextPage);

        if (nextMeta.last_page && nextPage > nextMeta.last_page) {

            await load(nextMeta.last_page);

            return;

        }

        craftsmen.value = rows;

        meta.value = nextMeta;

        page.value = nextMeta.current_page;

    } catch (e) {

        craftsmen.value = [];

        error.value = message(e);

        toast.error(error.value);

    } finally {

        loading.value = false;

    }

}



function onFilterChange() {

    load(1);

}



function beginStatusChange(craftsman, status) {

    if (status === craftsman.status) {

        return;

    }

    statusDraft.value = { craftsman, status };
    statusNote.value = '';

}



function cancelStatusChange() {

    statusDraft.value = null;
    statusNote.value = '';

}



async function confirmStatusChange() {

    if (!statusDraft.value) {

        return;

    }

    const { craftsman, status } = statusDraft.value;

    error.value = '';
    statusSaving.value = true;

    try {

        const { data } = await axios.patch(`/api/admin/craftsmen/${craftsman.id}`, {

            status,
            note: statusNote.value.trim() || null,

        });

        Object.assign(craftsman, data);

        if (selectedCraftsman.value?.id === craftsman.id) {

            selectedCraftsman.value = { ...data };

        }

        cancelStatusChange();
        toast.fromResponse(data, t('craftsmen.updated'));

        if (filter.value && filter.value !== status) {

            await load(page.value);

        }

    } catch (e) {

        error.value = message(e);

        toast.error(error.value);

    } finally {

        statusSaving.value = false;

    }

}



async function downloadExport(url, fallbackName, params = {}) {

    const response = await axios.get(url, {

        params,

        responseType: 'blob',

    });



    if (response.data.type === 'application/json') {

        const payload = JSON.parse(await response.data.text());

        throw new Error(payload.message || t('errors.export_failed'));

    }



    const disposition = response.headers['content-disposition'] || '';

    const encoded = disposition.match(/filename\*=UTF-8''([^;]+)/i);

    const plain = disposition.match(/filename="?([^"]+)"?/i);

    const name = decodeURIComponent(encoded?.[1] || plain?.[1] || fallbackName);

    const objectUrl = URL.createObjectURL(response.data);

    const link = document.createElement('a');

    link.href = objectUrl;

    link.download = name;

    link.click();

    URL.revokeObjectURL(objectUrl);

}



async function exportList() {

    error.value = '';

    exporting.value = true;

    try {

        await downloadExport(

            '/api/admin/craftsmen/export',

            t('craftsmen.file_list'),

            filter.value ? { status: filter.value } : {},

        );

        toast.success(t('craftsmen.exported_list'));

    } catch (e) {

        error.value = message(e);

        toast.error(error.value);

    } finally {

        exporting.value = false;

    }

}



async function remove(item) {

    if (!confirm(t('craftsmen.confirm_delete', { name: item.name }))) return;

    error.value = '';

    try {

        const { data } = await axios.delete(`/api/admin/craftsmen/${item.id}`);

        if (selectedCraftsman.value?.id === item.id) {

            closeDetails();

        }

        toast.fromResponse(data, t('craftsmen.deleted'));

        const nextPage = craftsmen.value.length === 1 && page.value > 1 ? page.value - 1 : page.value;

        await load(nextPage);

    } catch (e) {

        error.value = message(e);

        toast.error(error.value);

    }

}



function openDetails(item) {

    selectedCraftsman.value = item;

}



function closeDetails() {

    selectedCraftsman.value = null;

}



function onKeydown(event) {

    if (event.key === 'Escape') {

        if (statusDraft.value) {

            cancelStatusChange();

            return;

        }

        closeDetails();

    }

}



watch([selectedCraftsman, statusDraft], ([craftsman, draft]) => {

    document.body.classList.toggle('overflow-hidden', Boolean(craftsman || draft));

});



onMounted(() => {

    load(1);

    window.addEventListener('keydown', onKeydown);

});



onUnmounted(() => {

    window.removeEventListener('keydown', onKeydown);

    document.body.classList.remove('overflow-hidden');

});

</script>



<template>

    <section class="space-y-5">

        <div class="flex flex-wrap items-center justify-between gap-3">

            <div>

                <h2 class="text-2xl font-black text-primary">{{ t('craftsmen.title') }}</h2>

                <p class="text-sm text-slate-500">{{ t('craftsmen.subtitle') }}</p>

            </div>

            <div class="flex flex-wrap items-center gap-2">

                <button

                    type="button"

                    class="h-11 rounded-xl bg-primary px-4 text-sm font-extrabold text-white hover:bg-primary-light disabled:opacity-60"

                    :disabled="exporting || meta.total < 1"

                    @click="exportList"

                >

                    {{ exporting ? t('craftsmen.exporting') : t('craftsmen.export') }}

                </button>

                <label class="relative block">

                    <span class="sr-only">{{ t('craftsmen.filter_status') }}</span>

                    <select

                        v-model="filter"

                        class="h-11 min-w-[13.5rem] appearance-none rounded-xl border border-slate-200 bg-white pe-10 ps-4 text-sm font-extrabold text-primary shadow-sm outline-none transition hover:border-primary/40 focus:border-primary focus:ring-2 focus:ring-primary/15"

                        @change="onFilterChange"

                    >

                        <option v-for="item in statuses" :key="item.value" :value="item.value">{{ item.label }}</option>

                    </select>

                    <span class="pointer-events-none absolute inset-y-0 end-0 flex w-10 items-center justify-center text-slate-400">

                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">

                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />

                        </svg>

                    </span>

                </label>

            </div>

        </div>



        <p v-if="error" class="rounded-xl bg-red-50 px-4 py-3 text-sm font-bold text-red-700">{{ error }}</p>



        <div class="admin-table-scroll rounded-2xl border border-slate-200 bg-white shadow-sm">

            <table class="w-full min-w-[72rem] text-start text-sm">

                <thead class="bg-slate-50 text-primary">

                    <tr>

                        <th class="px-4 py-3 font-extrabold">{{ t('name') }}</th>

                        <th class="px-4 py-3 font-extrabold">{{ t('craftsmen.national_id') }}</th>

                        <th class="px-4 py-3 font-extrabold">{{ t('phone') }}</th>

                        <th class="px-4 py-3 font-extrabold">{{ t('craftsmen.city') }}</th>

                        <th class="px-4 py-3 font-extrabold">{{ t('craftsmen.specialty') }}</th>

                        <th class="px-4 py-3 font-extrabold">{{ t('craftsmen.experience') }}</th>

                        <th class="px-4 py-3 font-extrabold">{{ t('craftsmen.tools') }}</th>

                        <th class="px-4 py-3 font-extrabold">{{ t('craftsmen.bio') }}</th>

                        <th class="px-4 py-3 font-extrabold">{{ t('status') }}</th>

                        <th class="px-4 py-3 font-extrabold"></th>

                    </tr>

                </thead>

                <tbody>

                    <tr v-for="item in craftsmen" :key="item.id" class="border-t border-slate-100">

                        <td class="px-4 py-3 font-bold">{{ item.name }}</td>

                        <td class="px-4 py-3 whitespace-nowrap" dir="ltr">{{ item.national_id || '-' }}</td>

                        <td class="px-4 py-3" dir="ltr">{{ item.phone }}</td>

                        <td class="px-4 py-3">{{ item.city }}</td>

                        <td class="px-4 py-3">
                            <div v-if="specialtyList(item.specialty).length" class="flex max-w-xs items-center gap-1.5">
                                <span
                                    class="inline-flex max-w-[11rem] truncate rounded-full bg-primary/8 px-2.5 py-1 text-xs font-extrabold text-primary"
                                >
                                    {{ specialtyList(item.specialty)[0] }}
                                </span>
                                <span
                                    v-if="specialtyList(item.specialty).length > 1"
                                    class="inline-flex min-w-7 items-center justify-center rounded-full bg-accent/15 px-2 py-1 text-xs font-black text-accent"
                                    :title="specialtyList(item.specialty).slice(1).join('، ')"
                                >
                                    +{{ specialtyList(item.specialty).length - 1 }}
                                </span>
                            </div>
                            <span v-else class="text-slate-400">-</span>
                        </td>

                        <td class="px-4 py-3">{{ t('years', { count: item.experience_years }) }}</td>

                        <td class="px-4 py-3">{{ item.has_tools ? t('yes') : t('no') }}</td>

                        <td class="max-w-xs px-4 py-3 text-slate-500">{{ item.bio }}</td>

                        <td class="px-4 py-3">

                            <select
                                :value="item.status"
                                class="min-h-11 min-w-[8.5rem] rounded-lg border border-slate-200 px-2 py-1 text-xs font-bold"
                                @change="beginStatusChange(item, $event.target.value); $event.target.value = item.status"
                            >

                                <option v-for="option in statuses.filter((s) => s.value)" :key="option.value" :value="option.value">{{ option.label }}</option>

                            </select>

                        </td>

                        <td class="px-4 py-3">

                            <div class="flex items-center gap-1.5">
                                <button
                                    type="button"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-primary transition hover:border-primary/40 hover:bg-primary/5"
                                    :aria-label="t('craftsmen.view')"
                                    @click="openDetails(item)"
                                >
                                    <i class="fa-solid fa-eye text-[13px]" aria-hidden="true"></i>
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-red-600 transition hover:border-red-200 hover:bg-red-50"
                                    :aria-label="t('delete')"
                                    @click="remove(item)"
                                >
                                    <i class="fa-solid fa-trash-can text-[13px]" aria-hidden="true"></i>
                                </button>
                            </div>

                        </td>

                    </tr>

                    <tr v-if="!craftsmen.length">

                        <td colspan="10" class="px-4 py-8 text-center text-slate-400">{{ t('craftsmen.empty') }}</td>

                    </tr>

                </tbody>

            </table>

        </div>



        <PaginationBar

            :page="page"

            :last-page="meta.last_page"

            :total="meta.total"

            :from="meta.from"

            :to="meta.to"

            @change="load"

        />



        <div
            v-if="statusDraft"
            class="fixed inset-0 z-[95] flex items-end justify-center bg-primary-dark/50 p-4 sm:items-center"
            @click.self="cancelStatusChange"
        >
            <div
                role="dialog"
                aria-modal="true"
                aria-labelledby="craftsman-status-title"
                class="w-full max-w-lg rounded-3xl bg-white p-5 shadow-lg sm:p-6"
            >
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold text-accent">{{ t('craftsmen.status_change_title') }}</p>
                        <h3 id="craftsman-status-title" class="text-xl font-black text-primary">{{ statusDraft.craftsman.name }}</h3>
                        <p class="mt-1 text-sm font-bold text-slate-500">
                            {{ t('craftsmen.status_change_to', {
                                from: statusLabel(statusDraft.craftsman.status),
                                to: statusLabel(statusDraft.status),
                            }) }}
                        </p>
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 text-slate-500"
                        :aria-label="t('close')"
                        @click="cancelStatusChange"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <label class="mb-2 block text-sm font-extrabold text-primary" for="craftsman-status-note">{{ t('craftsmen.note_label') }}</label>
                <textarea
                    id="craftsman-status-note"
                    v-model="statusNote"
                    rows="4"
                    class="mb-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-primary"
                    :placeholder="t('craftsmen.note_placeholder')"
                ></textarea>
                <p class="mb-5 text-xs font-bold text-slate-400">{{ t('craftsmen.note_required_hint') }}</p>

                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        class="rounded-xl bg-accent px-5 py-2.5 text-sm font-extrabold text-white hover:bg-accent-hover disabled:opacity-60"
                        :disabled="statusSaving"
                        @click="confirmStatusChange"
                    >
                        {{ statusSaving ? t('craftsmen.saving') : t('craftsmen.confirm_status') }}
                    </button>
                    <button
                        type="button"
                        class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-600"
                        :disabled="statusSaving"
                        @click="cancelStatusChange"
                    >
                        {{ t('cancel') }}
                    </button>
                </div>
            </div>
        </div>



        <div

            v-if="selectedCraftsman"

            class="fixed inset-0 z-[90] flex items-end justify-center bg-primary-dark/50 p-4 sm:items-center"

            @click.self="closeDetails"

        >

            <div

                role="dialog"

                aria-modal="true"

                aria-labelledby="craftsman-details-title"

                class="max-h-[85vh] w-full max-w-lg overflow-y-auto rounded-3xl bg-white p-5 shadow-lg sm:p-6"

            >

                <div class="mb-4 flex items-start justify-between gap-3">

                    <div>

                        <p class="text-xs font-bold text-accent">{{ t('craftsmen.dialog_kicker') }}</p>

                        <h3 id="craftsman-details-title" class="text-xl font-black text-primary">{{ selectedCraftsman.name }}</h3>

                    </div>

                    <button

                        type="button"

                        class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 text-slate-500"

                        :aria-label="t('close')"

                        @click="closeDetails"

                    >

                        <i class="fa-solid fa-xmark"></i>

                    </button>

                </div>

                <dl class="space-y-3 text-sm">

                    <div>

                        <dt class="font-extrabold text-primary">{{ t('craftsmen.national_id') }}</dt>

                        <dd class="text-slate-600" dir="ltr">{{ selectedCraftsman.national_id || '-' }}</dd>

                    </div>

                    <div>

                        <dt class="font-extrabold text-primary">{{ t('phone') }}</dt>

                        <dd class="text-slate-600" dir="ltr">{{ selectedCraftsman.phone }}</dd>

                    </div>

                    <div>

                        <dt class="font-extrabold text-primary">{{ t('craftsmen.city') }}</dt>

                        <dd class="text-slate-600">{{ selectedCraftsman.city }}</dd>

                    </div>

                    <div>

                        <dt class="font-extrabold text-primary">{{ t('craftsmen.specialty') }}</dt>

                        <dd class="mt-2 flex flex-wrap gap-2">
                            <span
                                v-for="specialty in specialtyList(selectedCraftsman.specialty)"
                                :key="specialty"
                                class="inline-flex items-center gap-1.5 rounded-full border border-primary/10 bg-primary/5 px-3 py-1.5 text-xs font-extrabold text-primary"
                            >
                                <i class="fa-solid fa-screwdriver-wrench text-[10px] text-accent" aria-hidden="true"></i>
                                {{ specialty }}
                            </span>
                            <span v-if="!specialtyList(selectedCraftsman.specialty).length" class="text-slate-400">-</span>
                        </dd>

                    </div>

                    <div>

                        <dt class="font-extrabold text-primary">{{ t('craftsmen.experience') }}</dt>

                        <dd class="text-slate-600">{{ t('years', { count: selectedCraftsman.experience_years }) }}</dd>

                    </div>

                    <div>

                        <dt class="font-extrabold text-primary">{{ t('craftsmen.tools') }}</dt>

                        <dd class="text-slate-600">{{ selectedCraftsman.has_tools ? t('yes') : t('no') }}</dd>

                    </div>

                    <div>

                        <dt class="font-extrabold text-primary">{{ t('status') }}</dt>

                        <dd class="text-slate-600">{{ statusLabel(selectedCraftsman.status) }}</dd>

                    </div>

                    <div>

                        <dt class="font-extrabold text-primary">{{ t('craftsmen.submitted_at') }}</dt>

                        <dd class="text-slate-600">{{ formatSubmittedAt(selectedCraftsman.created_at) }}</dd>

                    </div>

                    <div>

                        <dt class="font-extrabold text-primary">{{ t('craftsmen.bio') }}</dt>

                        <dd class="whitespace-pre-wrap leading-7 text-slate-700">{{ selectedCraftsman.bio || t('craftsmen.no_bio') }}</dd>

                    </div>

                    <div>

                        <dt class="mb-2 font-extrabold text-primary">{{ t('craftsmen.notes_title') }}</dt>

                        <dd>
                            <ul v-if="selectedCraftsman.notes?.length" class="space-y-3">
                                <li
                                    v-for="item in selectedCraftsman.notes"
                                    :key="item.id"
                                    class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3"
                                >
                                    <div class="mb-1 flex flex-wrap items-center justify-between gap-2">
                                        <span class="text-xs font-extrabold text-accent">{{ statusLabel(item.status) }}</span>
                                        <span class="text-[11px] font-bold text-slate-400">{{ formatSubmittedAt(item.created_at) }}</span>
                                    </div>
                                    <p class="whitespace-pre-wrap leading-6 text-slate-700">{{ item.note || t('craftsmen.note_empty') }}</p>
                                </li>
                            </ul>
                            <p v-else class="text-slate-500">{{ t('craftsmen.no_notes') }}</p>
                        </dd>

                    </div>

                </dl>

            </div>

        </div>

    </section>

</template>


