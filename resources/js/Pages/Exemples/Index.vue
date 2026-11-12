<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    exemples: { type: Array, required: true },
    page: { type: Number, required: true },
    parPage: { type: Number, required: true },
});

const inertiaPage = usePage();
const appName = computed(() => inertiaPage.props.appName);
const succes = computed(() => inertiaPage.props.flash?.succes);

const form = useForm({ libelle: '', montant: '' });

function creer() {
    form.post('/exemples', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}

const montant = (valeur) =>
    new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(valeur);
</script>

<template>
    <div>
        <Head title="Exemples" />

        <header class="border-b border-gray-200 bg-white">
            <div class="mx-auto max-w-4xl px-4 py-4 font-semibold">{{ appName }}</div>
        </header>

        <main class="mx-auto max-w-4xl space-y-8 px-4 py-10">
            <h1 class="text-2xl font-semibold">Exemples</h1>

            <p v-if="succes" class="rounded-md bg-green-50 px-4 py-3 text-sm text-green-800">{{ succes }}</p>
            <p v-if="form.errors.erreur" class="rounded-md bg-red-50 px-4 py-3 text-sm text-red-800">{{ form.errors.erreur }}</p>

            <form class="grid gap-4 rounded-lg bg-white p-6 shadow-sm sm:grid-cols-[1fr_10rem_auto] sm:items-start" @submit.prevent="creer">
                <div>
                    <label for="libelle" class="block text-sm font-medium">Libellé</label>
                    <input id="libelle" v-model="form.libelle" type="text" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2" />
                    <p v-if="form.errors.libelle" class="mt-1 text-sm text-red-600">{{ form.errors.libelle }}</p>
                </div>

                <div>
                    <label for="montant" class="block text-sm font-medium">Montant</label>
                    <input id="montant" v-model="form.montant" type="number" step="0.01" min="0" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2" />
                    <p v-if="form.errors.montant" class="mt-1 text-sm text-red-600">{{ form.errors.montant }}</p>
                </div>

                <button type="submit" :disabled="form.processing" class="rounded-md bg-gray-900 px-4 py-2 text-white hover:bg-gray-700 disabled:opacity-50 sm:mt-6">
                    Créer
                </button>
            </form>

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-100 text-gray-600">
                        <tr>
                            <th class="px-4 py-2">#</th>
                            <th class="px-4 py-2">Libellé</th>
                            <th class="px-4 py-2 text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="exemple in props.exemples" :key="exemple.id" class="border-t border-gray-100">
                            <td class="px-4 py-2">{{ exemple.id }}</td>
                            <td class="px-4 py-2">{{ exemple.libelle }}</td>
                            <td class="px-4 py-2 text-right">{{ montant(exemple.montant) }}</td>
                        </tr>
                        <tr v-if="props.exemples.length === 0">
                            <td colspan="3" class="px-4 py-6 text-center text-gray-500">Aucun exemple pour l'instant.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <nav class="flex items-center justify-between text-sm">
                <Link v-if="props.page > 1" :href="`/exemples?page=${props.page - 1}`" class="rounded-md px-3 py-1 hover:bg-gray-200" preserve-scroll>← Précédent</Link>
                <span v-else />
                <span>Page {{ props.page }}</span>
                <Link v-if="props.exemples.length >= props.parPage" :href="`/exemples?page=${props.page + 1}`" class="rounded-md px-3 py-1 hover:bg-gray-200" preserve-scroll>Suivant →</Link>
                <span v-else />
            </nav>
        </main>
    </div>
</template>
