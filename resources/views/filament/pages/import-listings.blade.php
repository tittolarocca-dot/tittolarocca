<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Upload --}}
        <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 p-5">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">CSV hochladen</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Spalten: <code class="text-xs">display_name, email, city, age, category, phone, description, language</code><br>
                Pflicht: <strong>display_name, email, city, category</strong>. Optional: age (darf leer sein), phone, description, language.
            </p>

            <input type="file" wire:model="csv" accept=".csv,text/csv,text/plain"
                class="mt-4 block w-full text-sm text-gray-600 dark:text-gray-300
                       file:mr-3 file:rounded-lg file:border-0 file:bg-primary-600 file:px-4 file:py-2
                       file:text-sm file:font-semibold file:text-white hover:file:bg-primary-500 cursor-pointer" />

            @error('csv')
                <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
            @enderror

            <div wire:loading wire:target="csv" class="mt-2 text-sm text-gray-500">Analysiere CSV …</div>
        </div>

        {{-- Zusammenfassung + Vorschau --}}
        @if ($analyzed)
            <div class="flex flex-wrap gap-2">
                <span class="rounded-full bg-success-100 px-3 py-1 text-sm font-semibold text-success-700 dark:bg-success-500/20 dark:text-success-300">
                    Bereit: {{ $summary['ready'] }}
                </span>
                <span class="rounded-full bg-warning-100 px-3 py-1 text-sm font-semibold text-warning-700 dark:bg-warning-500/20 dark:text-warning-300">
                    Mögliche Duplikate: {{ $summary['duplicate'] }}
                </span>
                <span class="rounded-full bg-danger-100 px-3 py-1 text-sm font-semibold text-danger-700 dark:bg-danger-500/20 dark:text-danger-300">
                    Fehler: {{ $summary['error'] }}
                </span>
            </div>

            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-white/10">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-left text-gray-500">
                        <tr>
                            <th class="px-3 py-2 font-medium">Profil</th>
                            <th class="px-3 py-2 font-medium">E-Mail</th>
                            <th class="px-3 py-2 font-medium">Ort</th>
                            <th class="px-3 py-2 font-medium">Telefon</th>
                            <th class="px-3 py-2 font-medium">Status</th>
                            <th class="px-3 py-2 font-medium">Hinweise</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach ($rows as $r)
                            <tr>
                                <td class="px-3 py-2 font-medium text-gray-900 dark:text-white">{{ $r['display']['name'] }}</td>
                                <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ $r['display']['email'] }}</td>
                                <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ $r['display']['city'] }}</td>
                                <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ $r['display']['phone'] }}</td>
                                <td class="px-3 py-2">
                                    @if ($r['status'] === 'ready')
                                        <span class="inline-flex rounded-full bg-success-100 px-2 py-0.5 text-xs font-semibold text-success-700 dark:bg-success-500/20 dark:text-success-300">Bereit</span>
                                    @elseif ($r['status'] === 'duplicate')
                                        <span class="inline-flex rounded-full bg-warning-100 px-2 py-0.5 text-xs font-semibold text-warning-700 dark:bg-warning-500/20 dark:text-warning-300">Mögliches Duplikat</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-danger-100 px-2 py-0.5 text-xs font-semibold text-danger-700 dark:bg-danger-500/20 dark:text-danger-300">Fehler</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-gray-500">{{ implode(' · ', $r['messages']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if (! $result)
                <div>
                    <x-filament::button wire:click="import" wire:loading.attr="disabled" wire:target="import"
                        icon="heroicon-o-user-plus" :disabled="$summary['ready'] === 0">
                        Benutzer &amp; Profile erstellen ({{ $summary['ready'] }})
                    </x-filament::button>
                    <p class="mt-2 text-xs text-gray-500">Fehlerhafte Zeilen und mögliche Duplikate werden übersprungen.</p>
                </div>
            @endif
        @endif

        {{-- Ergebnis --}}
        @if ($result)
            <div class="rounded-xl border border-success-500/30 bg-success-50 p-5 dark:bg-success-500/10">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Import abgeschlossen</h2>
                <ul class="mt-2 space-y-1 text-sm text-gray-700 dark:text-gray-200">
                    <li>✓ {{ $result['created'] }} Benutzer/Profile erfolgreich erstellt</li>
                    <li>↷ {{ $result['skipped'] }} mögliche Duplikate übersprungen</li>
                    <li>✕ {{ $result['errors'] }} Fehler</li>
                </ul>
                <p class="mt-3 text-sm text-gray-500">
                    Import #{{ $batchId }} · Die Drag-&-Drop-Foto-Zuordnung folgt in Phase&nbsp;2.
                </p>
            </div>
        @endif

    </div>
</x-filament-panels::page>
