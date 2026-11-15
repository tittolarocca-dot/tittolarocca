<x-filament-panels::page>
    @php
        $chip = 'display:inline-block;padding:5px 12px;border-radius:999px;font-size:13px;font-weight:700;';
        $th   = 'padding:9px 14px;text-align:left;font-weight:600;font-size:12px;letter-spacing:.03em;opacity:.7;';
        $td   = 'padding:9px 14px;font-size:14px;vertical-align:top;';
    @endphp

    <div style="display:flex;flex-direction:column;gap:20px;">

        {{-- Upload --}}
        <div style="border:1px solid rgba(255,255,255,.10);border-radius:14px;padding:18px;background:rgba(255,255,255,.02);">
            <div style="font-weight:700;font-size:16px;">CSV hochladen</div>
            <p style="margin-top:4px;font-size:13px;opacity:.7;">
                Spalten: <code>display_name, email, city, age, category, phone, description, language</code><br>
                Pflicht: <strong>display_name, email, city, category</strong>. Optional: age (darf leer sein), phone, description, language.
            </p>

            <div style="position:relative;border:2px dashed rgba(227,93,143,.5);border-radius:12px;padding:20px 16px;text-align:center;margin-top:12px;">
                <div style="font-weight:600;">CSV hierher ziehen oder klicken</div>
                <input type="file" wire:model="csv" accept=".csv,text/csv,text/plain"
                       style="position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer;">
            </div>

            @error('csv')
                <p style="margin-top:8px;color:#f87171;font-size:13px;">{{ $message }}</p>
            @enderror
            <div wire:loading wire:target="csv" style="margin-top:8px;font-size:13px;color:#e35d8f;">Analysiere CSV …</div>
        </div>

        {{-- Zusammenfassung + Vorschau --}}
        @if ($analyzed)
            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                <span style="{{ $chip }}background:rgba(34,197,94,.18);color:#4ade80;">Bereit: {{ $summary['ready'] }}</span>
                <span style="{{ $chip }}background:rgba(245,158,11,.18);color:#fbbf24;">Mögliche Duplikate: {{ $summary['duplicate'] }}</span>
                <span style="{{ $chip }}background:rgba(239,68,68,.18);color:#f87171;">Fehler: {{ $summary['error'] }}</span>
            </div>

            <div style="overflow-x:auto;border:1px solid rgba(255,255,255,.10);border-radius:14px;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead style="background:rgba(255,255,255,.04);">
                        <tr>
                            <th style="{{ $th }}">Profil</th>
                            <th style="{{ $th }}">E-Mail</th>
                            <th style="{{ $th }}">Ort</th>
                            <th style="{{ $th }}">Telefon</th>
                            <th style="{{ $th }}">Status</th>
                            <th style="{{ $th }}">Hinweise</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $r)
                            <tr style="border-top:1px solid rgba(255,255,255,.07);">
                                <td style="{{ $td }}font-weight:600;">{{ $r['display']['name'] }}</td>
                                <td style="{{ $td }}opacity:.85;">{{ $r['display']['email'] }}</td>
                                <td style="{{ $td }}opacity:.85;">{{ $r['display']['city'] }}</td>
                                <td style="{{ $td }}opacity:.85;">{{ $r['display']['phone'] }}</td>
                                <td style="{{ $td }}">
                                    @if ($r['status'] === 'ready')
                                        <span style="{{ $chip }}font-size:12px;padding:3px 10px;background:rgba(34,197,94,.18);color:#4ade80;">Bereit</span>
                                    @elseif ($r['status'] === 'duplicate')
                                        <span style="{{ $chip }}font-size:12px;padding:3px 10px;background:rgba(245,158,11,.18);color:#fbbf24;">Mögliches Duplikat</span>
                                    @else
                                        <span style="{{ $chip }}font-size:12px;padding:3px 10px;background:rgba(239,68,68,.18);color:#f87171;">Fehler</span>
                                    @endif
                                </td>
                                <td style="{{ $td }}opacity:.65;">{{ implode(' · ', $r['messages']) }}</td>
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
                    <p style="margin-top:8px;font-size:12px;opacity:.6;">Fehlerhafte Zeilen und mögliche Duplikate werden übersprungen.</p>
                </div>
            @endif
        @endif

        {{-- Ergebnis --}}
        @if ($result)
            <div style="border:1px solid rgba(34,197,94,.35);border-radius:14px;padding:18px;background:rgba(34,197,94,.08);">
                <div style="font-weight:700;font-size:16px;">Import abgeschlossen</div>
                <ul style="margin-top:8px;font-size:14px;line-height:1.7;list-style:none;padding:0;">
                    <li>✓ {{ $result['created'] }} Benutzer/Profile erfolgreich erstellt</li>
                    <li>↷ {{ $result['skipped'] }} mögliche Duplikate übersprungen</li>
                    <li>✕ {{ $result['errors'] }} Fehler</li>
                </ul>
                <div style="margin-top:14px;">
                    <a href="{{ \App\Filament\Pages\ManagePhotos::getUrl(['batch' => $batchId]) }}">
                        <x-filament::button icon="heroicon-o-photo" tag="span">
                            Fotos zu diesen Profilen hinzufügen
                        </x-filament::button>
                    </a>
                </div>
                <p style="margin-top:10px;font-size:12px;opacity:.6;">Import #{{ $batchId }}</p>
            </div>
        @endif

    </div>
</x-filament-panels::page>
