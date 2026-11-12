import { Head, Link, useForm, usePage } from '@inertiajs/react';

const formatMontant = (valeur) =>
    new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(valeur);

export default function Index({ exemples, page, parPage }) {
    const { appName, flash } = usePage().props;
    const { data, setData, post, processing, errors, reset } = useForm({ libelle: '', montant: '' });

    function creer(event) {
        event.preventDefault();
        post('/exemples', {
            preserveScroll: true,
            onSuccess: () => reset(),
        });
    }

    return (
        <div>
            <Head title="Exemples" />

            <header className="border-b border-gray-200 bg-white">
                <div className="mx-auto max-w-4xl px-4 py-4 font-semibold">{appName}</div>
            </header>

            <main className="mx-auto max-w-4xl space-y-8 px-4 py-10">
                <h1 className="text-2xl font-semibold">Exemples</h1>

                {flash?.succes && <p className="rounded-md bg-green-50 px-4 py-3 text-sm text-green-800">{flash.succes}</p>}
                {errors.erreur && <p className="rounded-md bg-red-50 px-4 py-3 text-sm text-red-800">{errors.erreur}</p>}

                <form onSubmit={creer} className="grid gap-4 rounded-lg bg-white p-6 shadow-sm sm:grid-cols-[1fr_10rem_auto] sm:items-start">
                    <div>
                        <label htmlFor="libelle" className="block text-sm font-medium">Libellé</label>
                        <input
                            id="libelle"
                            type="text"
                            value={data.libelle}
                            onChange={(e) => setData('libelle', e.target.value)}
                            className="mt-1 w-full rounded-md border border-gray-300 px-3 py-2"
                        />
                        {errors.libelle && <p className="mt-1 text-sm text-red-600">{errors.libelle}</p>}
                    </div>

                    <div>
                        <label htmlFor="montant" className="block text-sm font-medium">Montant</label>
                        <input
                            id="montant"
                            type="number"
                            step="0.01"
                            min="0"
                            value={data.montant}
                            onChange={(e) => setData('montant', e.target.value)}
                            className="mt-1 w-full rounded-md border border-gray-300 px-3 py-2"
                        />
                        {errors.montant && <p className="mt-1 text-sm text-red-600">{errors.montant}</p>}
                    </div>

                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-md bg-gray-900 px-4 py-2 text-white hover:bg-gray-700 disabled:opacity-50 sm:mt-6"
                    >
                        Créer
                    </button>
                </form>

                <div className="overflow-hidden rounded-lg bg-white shadow-sm">
                    <table className="w-full text-left text-sm">
                        <thead className="bg-gray-100 text-gray-600">
                            <tr>
                                <th className="px-4 py-2">#</th>
                                <th className="px-4 py-2">Libellé</th>
                                <th className="px-4 py-2 text-right">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            {exemples.map((exemple) => (
                                <tr key={exemple.id} className="border-t border-gray-100">
                                    <td className="px-4 py-2">{exemple.id}</td>
                                    <td className="px-4 py-2">{exemple.libelle}</td>
                                    <td className="px-4 py-2 text-right">{formatMontant(exemple.montant)}</td>
                                </tr>
                            ))}
                            {exemples.length === 0 && (
                                <tr>
                                    <td colSpan={3} className="px-4 py-6 text-center text-gray-500">Aucun exemple pour l'instant.</td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                <nav className="flex items-center justify-between text-sm">
                    {page > 1 ? (
                        <Link href={`/exemples?page=${page - 1}`} preserveScroll className="rounded-md px-3 py-1 hover:bg-gray-200">← Précédent</Link>
                    ) : <span />}
                    <span>Page {page}</span>
                    {exemples.length >= parPage ? (
                        <Link href={`/exemples?page=${page + 1}`} preserveScroll className="rounded-md px-3 py-1 hover:bg-gray-200">Suivant →</Link>
                    ) : <span />}
                </nav>
            </main>
        </div>
    );
}
