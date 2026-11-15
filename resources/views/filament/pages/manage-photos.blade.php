<x-filament-panels::page>
    @php
        $profiles = $this->profiles();
        $btn = 'display:inline-flex;align-items:center;justify-content:center;width:26px;height:24px;border-radius:7px;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.04);color:inherit;font-size:12px;line-height:1;cursor:pointer;';
    @endphp

    @if ($profiles->isEmpty())
        <div style="padding:24px;text-align:center;opacity:.7;">
            Keine Profile für diese Auswahl gefunden.
        </div>
    @else
        <div style="display:flex;flex-direction:column;gap:18px;">
            @foreach ($profiles as $p)
                @php $photos = $this->photosFor($p->id); @endphp
                <div wire:key="profile-{{ $p->id }}"
                     style="border:1px solid rgba(255,255,255,.10);border-radius:14px;padding:18px;background:rgba(255,255,255,.02);">

                    {{-- Kopf --}}
                    <div style="display:flex;justify-content:space-between;align-items:baseline;gap:12px;margin-bottom:14px;">
                        <div>
                            <div style="font-weight:700;font-size:16px;">{{ $p->display_name }} — {{ $p->city?->name }}</div>
                            <div style="font-size:13px;opacity:.6;">{{ $p->user?->email }}</div>
                        </div>
                        <div style="font-size:12px;opacity:.6;white-space:nowrap;">{{ count($photos) }} / 10 Fotos</div>
                    </div>

                    {{-- Thumbnail-Strip --}}
                    @if (count($photos))
                        <div style="display:flex;flex-wrap:wrap;gap:12px;margin-bottom:14px;">
                            @foreach ($photos as $ph)
                                <div wire:key="media-{{ $ph['id'] }}" style="width:112px;">
                                    <div style="position:relative;">
                                        <img src="{{ $ph['url'] }}" alt=""
                                             style="width:112px;height:144px;object-fit:cover;border-radius:10px;display:block;border:2px solid {{ $ph['main'] ? '#e35d8f' : 'transparent' }};">
                                        @if ($ph['main'])
                                            <span style="position:absolute;top:6px;left:6px;background:#e35d8f;color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:999px;">Hauptbild</span>
                                        @endif
                                    </div>
                                    <div style="display:flex;gap:4px;justify-content:center;margin-top:6px;">
                                        <button type="button" title="Nach vorne" style="{{ $btn }}" wire:click="move({{ $ph['id'] }}, -1)" wire:loading.attr="disabled">◀</button>
                                        @unless ($ph['main'])
                                            <button type="button" title="Als Hauptbild" style="{{ $btn }}" wire:click="setMain({{ $ph['id'] }})" wire:loading.attr="disabled">★</button>
                                        @endunless
                                        <button type="button" title="Nach hinten" style="{{ $btn }}" wire:click="move({{ $ph['id'] }}, 1)" wire:loading.attr="disabled">▶</button>
                                        <button type="button" title="Löschen" style="{{ $btn }}color:#f87171;" wire:click="deletePhoto({{ $ph['id'] }})" wire:confirm="Dieses Foto wirklich löschen?">✕</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Drop-Zone / Upload (natives Drag & Drop + Klick) --}}
                    <div style="position:relative;border:2px dashed rgba(227,93,143,.5);border-radius:12px;padding:22px 16px;text-align:center;">
                        <div style="font-size:28px;line-height:1;">⬆️</div>
                        <div style="font-weight:600;margin-top:6px;">Fotos hierher ziehen oder klicken zum Auswählen</div>
                        <div style="font-size:12px;opacity:.6;margin-top:2px;">JPG, PNG oder WebP · mehrere gleichzeitig</div>
                        <input type="file" wire:model="uploads.{{ $p->id }}" multiple
                               accept="image/jpeg,image/jpg,image/png,image/webp"
                               style="position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer;">
                    </div>
                    <div wire:loading wire:target="uploads.{{ $p->id }}" style="margin-top:8px;font-size:13px;color:#e35d8f;">
                        Lädt hoch und verarbeitet (WebP, Wasserzeichen) …
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
