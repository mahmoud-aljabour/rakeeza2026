<script setup>
import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue';
import axios from 'axios';
import { useApiError } from '../../composables/useApiError';
import { useToast } from '../../composables/useToast';
import { useLocale } from '../../composables/useLocale';
import { itemsFrom, metaFrom } from '../../composables/usePaginatedList';
import PaginationBar from '../../components/PaginationBar.vue';

const { message } = useApiError();
const toast = useToast();
const { t, locale } = useLocale();
const leads = ref([]);
const services = ref([]);
const filter = ref('');
const error = ref('');
const loading = ref(true);
const page = ref(1);
const meta = ref(metaFrom({}, 1));
const selectedLead = ref(null);
const statusDraft = ref(null);
const statusNote = ref('');
const statusPrice = ref('');
const statusSaving = ref(false);
const exporting = ref(false);
const createOpen = ref(false);
const createSaving = ref(false);
const createForm = reactive({
    name: '',
    phone: '',
    email: '',
    service_id: '',
    message: '',
    note: '',
});

const statuses = computed(() => [
    { value: '', label: t('all') },
    { value: 'pending', label: t('leads.pending') },
    { value: 'contacted', label: t('leads.contacted') },
    { value: 'completed', label: t('leads.completed') },
    { value: 'closed', label: t('leads.closed') },
]);
const completionPriceInvalid = computed(() => (
    statusDraft.value?.status === 'completed'
    && (!statusPrice.value || Number(statusPrice.value) <= 0)
));

function statusLabel(value) {
    return statuses.value.find((item) => item.value === value)?.label || value;
}

function servicesList(lead) {
    const titles = Array.isArray(lead.services)
        ? lead.services.map((service) => service.title).filter(Boolean)
        : [];

    if (titles.length) {
        return titles;
    }

    return lead.service?.title ? [lead.service.title] : [];
}

function servicesLabel(lead) {
    const titles = servicesList(lead);

    return titles.length
        ? titles.join(locale.value === 'en' ? ', ' : '، ')
        : t('leads.general');
}

function formatMoney(value) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
    }).format(Number(value || 0));
}

function formatNoteAt(value) {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat(locale.value === 'en' ? 'en-GB' : 'ar-EG', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

async function loadServices() {
    try {
        const { data } = await axios.get('/api/admin/services', {
            params: { all: 1 },
        });
        services.value = Array.isArray(data) ? data : [];
    } catch {
        services.value = [];
    }
}

async function load(nextPage = page.value) {
    loading.value = true;
    error.value = '';

    try {
        const { data } = await axios.get('/api/admin/leads', {
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

        leads.value = rows;
        meta.value = nextMeta;
        page.value = nextMeta.current_page;
    } catch (e) {
        leads.value = [];
        error.value = message(e);
        toast.error(error.value);
    } finally {
        loading.value = false;
    }
}

function onFilterChange() {
    load(1);
}

function beginStatusChange(lead, status) {
    if (status === lead.status) {
        return;
    }

    statusDraft.value = { lead, status };
    statusNote.value = '';
    statusPrice.value = '';
}

function cancelStatusChange() {
    statusDraft.value = null;
    statusNote.value = '';
    statusPrice.value = '';
}

async function confirmStatusChange() {
    if (!statusDraft.value) {
        return;
    }

    const { lead, status } = statusDraft.value;
    error.value = '';
    statusSaving.value = true;

    try {
        const { data } = await axios.patch(`/api/admin/leads/${lead.id}`, {
            status,
            completed_price: status === 'completed' ? statusPrice.value : null,
            note: statusNote.value.trim() || null,
        });
        Object.assign(lead, data);
        if (selectedLead.value?.id === lead.id) {
            selectedLead.value = { ...data };
        }
        cancelStatusChange();
        toast.success(t('leads.updated'));

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

function openCreate() {
    createForm.name = '';
    createForm.phone = '';
    createForm.email = '';
    createForm.service_id = '';
    createForm.message = '';
    createForm.note = '';
    createOpen.value = true;
}

function closeCreate() {
    createOpen.value = false;
}

async function submitCreate() {
    error.value = '';
    createSaving.value = true;

    try {
        const { data } = await axios.post('/api/admin/leads', {
            name: createForm.name.trim(),
            phone: createForm.phone.trim(),
            email: createForm.email.trim() || null,
            service_id: createForm.service_id || null,
            message: createForm.message.trim() || null,
            note: createForm.note.trim() || null,
        });
        closeCreate();
        toast.success(t('leads.created'));
        await load(1);
        if (data?.id) {
            openMessage(data);
        }
    } catch (e) {
        error.value = message(e);
        toast.error(error.value);
    } finally {
        createSaving.value = false;
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
            '/api/admin/leads/export',
            t('leads.file_list'),
            filter.value ? { status: filter.value } : {},
        );
        toast.success(t('leads.exported_list'));
    } catch (e) {
        error.value = message(e);
        toast.error(error.value);
    } finally {
        exporting.value = false;
    }
}

async function remove(lead) {
    if (!confirm(t('leads.confirm_delete', { name: lead.name }))) return;
    error.value = '';
    try {
        const { data } = await axios.delete(`/api/admin/leads/${lead.id}`);
        if (selectedLead.value?.id === lead.id) {
            closeMessage();
        }
        toast.fromResponse(data, t('leads.deleted'));
        const nextPage = leads.value.length === 1 && page.value > 1 ? page.value - 1 : page.value;
        await load(nextPage);
    } catch (e) {
        error.value = message(e);
        toast.error(error.value);
    }
}

function openMessage(lead) {
    selectedLead.value = lead;
}

function closeMessage() {
    selectedLead.value = null;
}

function onKeydown(event) {
    if (event.key !== 'Escape') {
        return;
    }

    if (statusDraft.value) {
        cancelStatusChange();
        return;
    }

    if (createOpen.value) {
        closeCreate();
        return;
    }

    closeMessage();
}

watch([selectedLead, statusDraft, createOpen], ([lead, draft, create]) => {
    document.body.classList.toggle('overflow-hidden', Boolean(lead || draft || create));
});

onMounted(async () => {
    await Promise.all([load(1), loadServices()]);
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
                <h2 class="text-2xl font-black text-primary">{{ t('leads.title') }}</h2>
                <p class="text-sm text-slate-500">{{ t('leads.subtitle') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    class="h-11 rounded-xl bg-accent px-4 text-sm font-extrabold text-white hover:bg-accent-hover"
                    @click="openCreate"
                >
                    {{ t('leads.add') }}
                </button>
                <button
                    type="button"
                    class="h-11 rounded-xl bg-primary px-4 text-sm font-extrabold text-white hover:bg-primary-light disabled:opacity-60"
                    :disabled="exporting || !leads.length"
                    @click="exportList"
                >
                    {{ exporting ? t('leads.exporting') : t('leads.export') }}
                </button>
                <select v-model="filter" class="min-h-11 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold" @change="onFilterChange">
                    <option v-for="item in statuses" :key="item.value" :value="item.value">{{ item.label }}</option>
                </select>
            </div>
        </div>

        <p v-if="error" class="rounded-xl bg-red-50 px-4 py-3 text-sm font-bold text-red-700">{{ error }}</p>

        <div class="admin-table-scroll rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="w-full min-w-[56rem] text-start text-sm">
                <thead class="bg-slate-50 text-primary">
                    <tr>
                        <th class="px-4 py-3 font-extrabold">{{ t('name') }}</th>
                        <th class="px-4 py-3 font-extrabold">{{ t('phone') }}</th>
                        <th class="px-4 py-3 font-extrabold">{{ t('email') }}</th>
                        <th class="px-4 py-3 font-extrabold">{{ t('service') }}</th>
                        <th class="px-4 py-3 font-extrabold">{{ t('details') }}</th>
                        <th class="px-4 py-3 font-extrabold">{{ t('status') }}</th>
                        <th class="px-4 py-3 font-extrabold"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="lead in leads" :key="lead.id" class="border-t border-slate-100">
                        <td class="whitespace-nowrap px-4 py-3 font-bold">{{ lead.name }}</td>
                        <td class="whitespace-nowrap px-4 py-3" dir="ltr">{{ lead.phone }}</td>
                        <td class="max-w-[10rem] px-4 py-3 lg:max-w-[12rem] xl:max-w-[15rem]" dir="ltr">
                            <p class="truncate" :title="lead.email || undefined">{{ lead.email || '-' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex max-w-xs items-center gap-1.5">
                                <span class="inline-flex max-w-[11rem] truncate rounded-full bg-primary/8 px-2.5 py-1 text-xs font-extrabold text-primary">
                                    {{ servicesList(lead)[0] || t('leads.general') }}
                                </span>
                                <span
                                    v-if="servicesList(lead).length > 1"
                                    class="inline-flex min-w-7 items-center justify-center rounded-full bg-accent/15 px-2 py-1 text-xs font-black text-accent"
                                    :title="servicesList(lead).slice(1).join(locale === 'en' ? ', ' : '، ')"
                                >
                                    +{{ servicesList(lead).length - 1 }}
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <p class="line-clamp-2 max-w-[14rem] text-slate-500">{{ lead.message || '-' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <select
                                :value="lead.status"
                                class="min-h-11 min-w-[8.5rem] rounded-lg border border-slate-200 px-2 py-1 text-xs font-bold"
                                @change="beginStatusChange(lead, $event.target.value); $event.target.value = lead.status"
                            >
                                <option v-for="item in statuses.filter((s) => s.value)" :key="item.value" :value="item.value">{{ item.label }}</option>
                            </select>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1.5">
                                <button
                                    type="button"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-primary transition hover:border-primary/40 hover:bg-primary/5"
                                    :aria-label="t('leads.view')"
                                    @click="openMessage(lead)"
                                >
                                    <i class="fa-solid fa-eye text-[13px]" aria-hidden="true"></i>
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-red-600 transition hover:border-red-200 hover:bg-red-50"
                                    :aria-label="t('delete')"
                                    @click="remove(lead)"
                                >
                                    <i class="fa-solid fa-trash-can text-[13px]" aria-hidden="true"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!leads.length">
                        <td colspan="7" class="px-4 py-8 text-center text-slate-400">{{ t('leads.empty') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-xs font-bold text-slate-400 lg:hidden">{{ t('leads.scroll_hint') }}</p>

        <PaginationBar
            :page="page"
            :last-page="meta.last_page"
            :total="meta.total"
            :from="meta.from"
            :to="meta.to"
            @change="load"
        />

        <div
            v-if="createOpen"
            class="fixed inset-0 z-[95] flex items-end justify-center bg-primary-dark/50 p-4 sm:items-center"
            @click.self="closeCreate"
        >
            <form
                role="dialog"
                aria-modal="true"
                aria-labelledby="lead-create-title"
                class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-3xl bg-white p-5 shadow-lg sm:p-6"
                @submit.prevent="submitCreate"
            >
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold text-accent">{{ t('leads.add_kicker') }}</p>
                        <h3 id="lead-create-title" class="text-xl font-black text-primary">{{ t('leads.add') }}</h3>
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 text-slate-500"
                        :aria-label="t('close')"
                        @click="closeCreate"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="grid gap-4">
                    <div>
                        <label class="mb-2 block text-sm font-extrabold text-primary">{{ t('name') }}</label>
                        <input v-model="createForm.name" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-extrabold text-primary">{{ t('phone') }}</label>
                        <input v-model="createForm.phone" required dir="ltr" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-primary" placeholder="0597xxxxxx">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-extrabold text-primary">{{ t('email') }}</label>
                        <input v-model="createForm.email" type="email" dir="ltr" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-extrabold text-primary">{{ t('service') }}</label>
                        <select v-model="createForm.service_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-primary">
                            <option value="">{{ t('leads.general') }}</option>
                            <option v-for="service in services" :key="service.id" :value="String(service.id)">{{ service.title }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-extrabold text-primary">{{ t('details') }}</label>
                        <textarea v-model="createForm.message" rows="3" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-primary" :placeholder="t('leads.details_placeholder')"></textarea>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-extrabold text-primary">{{ t('leads.note_label') }}</label>
                        <textarea v-model="createForm.note" rows="2" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-primary" :placeholder="t('leads.create_note_placeholder')"></textarea>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap gap-2">
                    <button
                        type="submit"
                        class="rounded-xl bg-accent px-5 py-2.5 text-sm font-extrabold text-white hover:bg-accent-hover disabled:opacity-60"
                        :disabled="createSaving"
                    >
                        {{ createSaving ? t('leads.saving') : t('save') }}
                    </button>
                    <button
                        type="button"
                        class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-600"
                        :disabled="createSaving"
                        @click="closeCreate"
                    >
                        {{ t('cancel') }}
                    </button>
                </div>
            </form>
        </div>

        <div
            v-if="statusDraft"
            class="fixed inset-0 z-[95] flex items-end justify-center bg-primary-dark/50 p-4 sm:items-center"
            @click.self="cancelStatusChange"
        >
            <div
                role="dialog"
                aria-modal="true"
                aria-labelledby="lead-status-title"
                class="w-full max-w-lg rounded-3xl bg-white p-5 shadow-lg sm:p-6"
            >
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold text-accent">{{ t('leads.status_change_title') }}</p>
                        <h3 id="lead-status-title" class="text-xl font-black text-primary">{{ statusDraft.lead.name }}</h3>
                        <p class="mt-1 text-sm font-bold text-slate-500">
                            {{ t('leads.status_change_to', {
                                from: statusLabel(statusDraft.lead.status),
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

                <div v-if="statusDraft.status === 'completed'" class="mb-4">
                    <label class="mb-2 block text-sm font-extrabold text-primary" for="lead-completed-price">
                        {{ t('leads.completed_price') }}
                    </label>
                    <input
                        id="lead-completed-price"
                        v-model="statusPrice"
                        type="number"
                        min="0.01"
                        step="0.01"
                        required
                        inputmode="decimal"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-primary"
                        :placeholder="t('leads.completed_price_placeholder')"
                    >
                    <p class="mt-2 text-xs font-bold text-slate-400">{{ t('leads.completed_price_hint') }}</p>
                </div>

                <label class="mb-2 block text-sm font-extrabold text-primary" for="lead-status-note">{{ t('leads.note_label') }}</label>
                <textarea
                    id="lead-status-note"
                    v-model="statusNote"
                    rows="4"
                    class="mb-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-primary"
                    :placeholder="t('leads.note_placeholder')"
                ></textarea>
                <p class="mb-5 text-xs font-bold text-slate-400">{{ t('leads.note_required_hint') }}</p>

                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        class="rounded-xl bg-accent px-5 py-2.5 text-sm font-extrabold text-white hover:bg-accent-hover disabled:opacity-60"
                        :disabled="statusSaving || completionPriceInvalid"
                        @click="confirmStatusChange"
                    >
                        {{ statusSaving ? t('leads.saving') : t('leads.confirm_status') }}
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
            v-if="selectedLead"
            class="fixed inset-0 z-[90] flex items-end justify-center bg-primary-dark/50 p-4 sm:items-center"
            @click.self="closeMessage"
        >
            <div
                role="dialog"
                aria-modal="true"
                aria-labelledby="lead-message-title"
                class="max-h-[80vh] w-full max-w-lg overflow-y-auto rounded-3xl bg-white p-5 shadow-lg sm:p-6"
            >
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold text-accent">{{ t('leads.dialog_kicker') }}</p>
                        <h3 id="lead-message-title" class="text-xl font-black text-primary">{{ selectedLead.name }}</h3>
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 text-slate-500"
                        :aria-label="t('close')"
                        @click="closeMessage"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="font-extrabold text-primary">{{ t('phone') }}</dt>
                        <dd class="text-slate-600" dir="ltr">{{ selectedLead.phone }}</dd>
                    </div>
                    <div>
                        <dt class="font-extrabold text-primary">{{ t('email') }}</dt>
                        <dd class="text-slate-600" dir="ltr">{{ selectedLead.email || '-' }}</dd>
                    </div>
                    <div>
                        <dt class="font-extrabold text-primary">{{ t('service') }}</dt>
                        <dd class="mt-2 flex flex-wrap gap-2">
                            <span
                                v-for="service in servicesList(selectedLead)"
                                :key="service"
                                class="inline-flex items-center gap-1.5 rounded-full border border-primary/10 bg-primary/5 px-3 py-1.5 text-xs font-extrabold text-primary"
                            >
                                <i class="fa-solid fa-screwdriver-wrench text-[10px] text-accent" aria-hidden="true"></i>
                                {{ service }}
                            </span>
                            <span v-if="!servicesList(selectedLead).length" class="text-slate-500">{{ t('leads.general') }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="font-extrabold text-primary">{{ t('status') }}</dt>
                        <dd class="text-slate-600">{{ statusLabel(selectedLead.status) }}</dd>
                    </div>
                    <div v-if="selectedLead.completed_price">
                        <dt class="font-extrabold text-primary">{{ t('leads.completed_price') }}</dt>
                        <dd class="font-extrabold text-emerald-700" dir="ltr">{{ formatMoney(selectedLead.completed_price) }}</dd>
                    </div>
                    <div>
                        <dt class="font-extrabold text-primary">{{ t('message') }}</dt>
                        <dd class="whitespace-pre-wrap leading-7 text-slate-700">{{ selectedLead.message || t('leads.no_details') }}</dd>
                    </div>
                    <div>
                        <dt class="mb-2 font-extrabold text-primary">{{ t('leads.notes_title') }}</dt>
                        <dd>
                            <ul v-if="selectedLead.notes?.length" class="space-y-3">
                                <li
                                    v-for="item in selectedLead.notes"
                                    :key="item.id"
                                    class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3"
                                >
                                    <div class="mb-1 flex flex-wrap items-center justify-between gap-2">
                                        <span class="text-xs font-extrabold text-accent">{{ statusLabel(item.status) }}</span>
                                        <span class="text-[11px] font-bold text-slate-400">{{ formatNoteAt(item.created_at) }}</span>
                                    </div>
                                    <p class="whitespace-pre-wrap leading-6 text-slate-700">{{ item.note || t('leads.note_empty') }}</p>
                                </li>
                            </ul>
                            <p v-else class="text-slate-500">{{ t('leads.no_notes') }}</p>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>
</template>
