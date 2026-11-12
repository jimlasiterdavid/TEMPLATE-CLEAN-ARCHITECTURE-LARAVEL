<div class="space-y-8">
    <h1 class="text-2xl font-semibold">Exemples</h1>

    @if (session('succes'))
        <p class="rounded-md bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('succes') }}</p>
    @endif

    @error('erreur')
        <p class="rounded-md bg-red-50 px-4 py-3 text-sm text-red-800">{{ $message }}</p>
    @enderror

    <form wire:submit="creer" class="grid gap-4 rounded-lg bg-white p-6 shadow-sm sm:grid-cols-[1fr_10rem_auto] sm:items-start">
        <div>
            <label for="libelle" class="block text-sm font-medium">Libellé</label>
            <input id="libelle" type="text" wire:model="libelle" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2">
            @error('libelle') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="montant" class="block text-sm font-medium">Montant</label>
            <input id="montant" type="number" step="0.01" min="0" wire:model="montant" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2">
            @error('montant') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <button type="submit" wire:loading.attr="disabled" class="rounded-md bg-gray-900 px-4 py-2 text-white hover:bg-gray-700 disabled:opacity-50 sm:mt-6">
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
                @forelse ($exemples as $exemple)
                    <tr wire:key="exemple-{{ $exemple->id }}" class="border-t border-gray-100">
                        <td class="px-4 py-2">{{ $exemple->id }}</td>
                        <td class="px-4 py-2">{{ $exemple->libelle }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($exemple->montant->valeur, 2, ',', ' ') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-6 text-center text-gray-500">Aucun exemple pour l'instant.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex items-center justify-between text-sm">
        <button wire:click="pagePrecedente" @disabled($page <= 1) class="rounded-md px-3 py-1 hover:bg-gray-200 disabled:opacity-40">← Précédent</button>
        <span>Page {{ $page }}</span>
        <button wire:click="pageSuivante" @disabled(count($exemples) < $parPage) class="rounded-md px-3 py-1 hover:bg-gray-200 disabled:opacity-40">Suivant →</button>
    </div>
</div>
