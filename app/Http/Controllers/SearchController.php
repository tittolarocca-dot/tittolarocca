<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Profile;
use App\Models\Tag;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    // Grenzen der Slider – müssen mit dem Frontend übereinstimmen
    private const AGE_MIN = 18;
    private const AGE_MAX = 80;   // 80 = "80+" → keine Obergrenze
    private const HEIGHT_MIN = 140;
    private const HEIGHT_MAX = 205; // 205 = "205+"

    public function index(Request $request)
    {
        $f = $this->parseFilters($request);
        $searched = $request->boolean('searched');

        // Vor dem ersten "Suchen"-Klick keine Profile anzeigen
        if (! $searched) {
            return inertia('Search/Index', [
                'profiles'    => Profile::whereRaw('1 = 0')->paginate(20),
                'services'    => Tag::orderBy('name')->get(['id', 'name', 'group']),
                'filters'     => $f,
                'resultCount' => 0,
                'searched'    => false,
            ]);
        }

        $query = Profile::with([
            'city', 'category', 'publicMedia',
            'listingOrders' => fn ($q) => $q
                ->where('status', 'paid')
                ->where('expires_at', '>', now())
                ->orderByDesc('paid_at'),
        ])
            ->where('status', 'active')
            ->where('listing_expires_at', '>', now());

        // ── Freitextsuche ────────────────────────────────────────────────
        if ($f['q']) {
            $term = "%{$f['q']}%";
            $query->where(function ($q) use ($term) {
                $q->where('display_name', 'like', $term)
                  ->orWhere('description', 'like', $term)
                  ->orWhereHas('city',     fn ($c) => $c->where('name', 'like', $term))
                  ->orWhereHas('category', fn ($c) => $c->where('name', 'like', $term))
                  ->orWhereHas('tags',     fn ($t) => $t->where('name', 'like', $term));
            });
        }

        // ── Region (Stadt) ──────────────────────────────────────────────
        if ($f['region']) {
            $query->whereHas('city', fn ($c) => $c->where('slug', $f['region']));
        }

        // ── Kontaktart → Kategorie ──────────────────────────────────────
        // Bildet die Kontaktart auf bestehende Kategorien ab
        $contactMap = [
            'call-out' => 'ich-komme-zu-dir',
            'call-in'  => 'ich-bin-besuchbar',
            'escort'   => 'escort',
        ];
        if ($f['contact'] && isset($contactMap[$f['contact']])) {
            $query->whereHas('category', fn ($c) => $c->where('slug', $contactMap[$f['contact']]));
        }

        // ── Alter ───────────────────────────────────────────────────────
        if ($f['age_min'] > self::AGE_MIN) {
            $query->where('age', '>=', $f['age_min']);
        }
        if ($f['age_max'] < self::AGE_MAX) {
            $query->where('age', '<=', $f['age_max']);
        }

        // ── Körpergrösse ────────────────────────────────────────────────
        if ($f['height_min'] > self::HEIGHT_MIN) {
            $query->where('height_cm', '>=', $f['height_min']);
        }
        if ($f['height_max'] < self::HEIGHT_MAX) {
            $query->where('height_cm', '<=', $f['height_max']);
        }

        // ── Intimbereich ────────────────────────────────────────────────
        if ($f['intim']) {
            $query->where('intimate_area', $f['intim']);
        }

        // ── Rauchen ─────────────────────────────────────────────────────
        if ($f['smoking'] !== null) {
            $query->where('smoking', $f['smoking']);
        }

        // ── Tattoo (aktuell nur ja/nein in der DB) ──────────────────────
        if ($f['tattoo'] !== null) {
            $query->where('tattoo', $f['tattoo']);
        }

        // ── Verifiziert (= bestätigte Fotos) ────────────────────────────
        if ($f['verified']) {
            $query->where('verification_status', 'approved');
        }

        // ── Services / Leistungen (Tags, OR-Verknüpfung) ────────────────
        if (! empty($f['services'])) {
            $query->whereHas('tags', fn ($t) => $t->whereIn('tags.id', $f['services']));
        }

        $profiles = $query
            ->orderByDesc('pushed_at')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return inertia('Search/Index', [
            'profiles'    => $profiles,
            'services'    => Tag::orderBy('name')->get(['id', 'name', 'group']),
            'filters'     => $f,
            'resultCount' => $profiles->total(),
            'searched'    => true,
        ]);
    }

    private function parseFilters(Request $request): array
    {
        $int = fn ($key, $default) => is_numeric($request->query($key))
            ? (int) $request->query($key)
            : $default;

        // Ja/Nein-Filter: '1' = ja, '0' = nein, sonst egal
        $bool = function ($key) use ($request) {
            $v = $request->query($key);
            if ($v === '1' || $v === 1) return true;
            if ($v === '0' || $v === 0) return false;
            return null;
        };

        $services = $request->query('services', []);
        if (! is_array($services)) {
            $services = array_filter(explode(',', (string) $services));
        }
        $services = array_values(array_filter(array_map('intval', $services)));

        // Region nur akzeptieren, wenn es eine echte Stadt ist
        // (z.B. "In meiner Umgebung" hat noch keine Geo-Logik → wird ignoriert)
        $region = $request->query('region');
        $region = ($region && City::where('slug', $region)->exists()) ? $region : null;

        return [
            'q'          => trim((string) $request->query('q', '')) ?: null,
            'region'     => $region,
            'contact'    => in_array($request->query('contact'), ['call-out', 'call-in', 'escort'], true)
                                ? $request->query('contact') : null,
            'age_min'    => max(self::AGE_MIN, min(self::AGE_MAX, $int('age_min', self::AGE_MIN))),
            'age_max'    => max(self::AGE_MIN, min(self::AGE_MAX, $int('age_max', self::AGE_MAX))),
            'height_min' => max(self::HEIGHT_MIN, min(self::HEIGHT_MAX, $int('height_min', self::HEIGHT_MIN))),
            'height_max' => max(self::HEIGHT_MIN, min(self::HEIGHT_MAX, $int('height_max', self::HEIGHT_MAX))),
            'intim'      => in_array($request->query('intim'), ['Glatt', 'Teilrasiert', 'Natürlich'], true)
                                ? $request->query('intim') : null,
            'smoking'    => $bool('smoking'),
            'tattoo'     => $bool('tattoo'),
            'verified'   => (bool) $request->query('verified'),
            'services'   => $services,
        ];
    }
}
