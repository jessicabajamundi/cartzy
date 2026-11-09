<?php

namespace App\Support;

use App\Models\Address;
use App\Models\User;

/**
 * Compares two Philippine addresses by barangay, city, province and region.
 *
 * This is address matching, not GPS distance: the same barangay is treated as
 * closest, then the same city, then province, then region. Names are
 * normalised so "Sta. Cruz" matches "Santa Cruz" and "City of Calamba" matches
 * "Calamba City".
 */
class PlaceMatcher
{
    public const LEVELS = [
        'barangay' => ['score' => 100, 'label' => 'Same barangay'],
        'city' => ['score' => 70, 'label' => 'Same city'],
        'province' => ['score' => 40, 'label' => 'Same province'],
        'region' => ['score' => 20, 'label' => 'Same region'],
    ];

    /** Province => region key, using the keys of LogisticsProvider::SERVICE_REGIONS. */
    private const PROVINCE_REGION = [
        'metro manila' => 'NCR', 'ncr' => 'NCR', 'manila' => 'NCR',
        'abra' => 'CAR', 'apayao' => 'CAR', 'benguet' => 'CAR', 'ifugao' => 'CAR', 'kalinga' => 'CAR', 'mountain province' => 'CAR',
        'ilocos norte' => 'I', 'ilocos sur' => 'I', 'la union' => 'I', 'pangasinan' => 'I',
        'batanes' => 'II', 'cagayan' => 'II', 'isabela' => 'II', 'nueva vizcaya' => 'II', 'quirino' => 'II',
        'aurora' => 'III', 'bataan' => 'III', 'bulacan' => 'III', 'nueva ecija' => 'III', 'pampanga' => 'III', 'tarlac' => 'III', 'zambales' => 'III',
        'batangas' => 'IV-A', 'cavite' => 'IV-A', 'laguna' => 'IV-A', 'quezon' => 'IV-A', 'rizal' => 'IV-A',
        'marinduque' => 'MIMAROPA', 'occidental mindoro' => 'MIMAROPA', 'oriental mindoro' => 'MIMAROPA', 'palawan' => 'MIMAROPA', 'romblon' => 'MIMAROPA',
        'albay' => 'V', 'camarines norte' => 'V', 'camarines sur' => 'V', 'catanduanes' => 'V', 'masbate' => 'V', 'sorsogon' => 'V',
        'aklan' => 'VI', 'antique' => 'VI', 'capiz' => 'VI', 'guimaras' => 'VI', 'iloilo' => 'VI',
        'negros occidental' => 'NIR', 'negros oriental' => 'NIR', 'siquijor' => 'NIR',
        'bohol' => 'VII', 'cebu' => 'VII',
        'biliran' => 'VIII', 'eastern samar' => 'VIII', 'leyte' => 'VIII', 'northern samar' => 'VIII', 'samar' => 'VIII', 'southern leyte' => 'VIII',
        'zamboanga del norte' => 'IX', 'zamboanga del sur' => 'IX', 'zamboanga sibugay' => 'IX',
        'bukidnon' => 'X', 'camiguin' => 'X', 'lanao del norte' => 'X', 'misamis occidental' => 'X', 'misamis oriental' => 'X',
        'davao de oro' => 'XI', 'davao del norte' => 'XI', 'davao del sur' => 'XI', 'davao occidental' => 'XI', 'davao oriental' => 'XI',
        'cotabato' => 'XII', 'sarangani' => 'XII', 'south cotabato' => 'XII', 'sultan kudarat' => 'XII',
        'agusan del norte' => 'XIII', 'agusan del sur' => 'XIII', 'dinagat islands' => 'XIII', 'surigao del norte' => 'XIII', 'surigao del sur' => 'XIII',
        'basilan' => 'BARMM', 'lanao del sur' => 'BARMM', 'maguindanao del norte' => 'BARMM', 'maguindanao del sur' => 'BARMM', 'sulu' => 'BARMM', 'tawi-tawi' => 'BARMM', 'tawi tawi' => 'BARMM',
    ];

    public static function normalize(?string $name): string
    {
        $name = mb_strtolower(trim((string) $name));
        $name = preg_replace('/\b(sta)\.?(?=\s)/u', 'santa', $name);
        $name = preg_replace('/\b(sto)\.?(?=\s)/u', 'santo', $name);
        $name = preg_replace('/^(city|municipality) of\s+/u', '', $name);
        $name = preg_replace('/\s+(city|municipality)$/u', '', $name);
        $name = preg_replace('/^(barangay|brgy\.?|bgy\.?)\s+/u', '', $name);
        $name = preg_replace('/[^\p{L}\p{N} -]+/u', ' ', $name);

        return trim(preg_replace('/\s+/u', ' ', $name));
    }

    public static function regionOf(?string $province, ?string $region = null): ?string
    {
        $region = trim((string) $region);
        if ($region !== '') {
            // "Region IV-A (CALABARZON)", "IV-A", "NCR"...
            foreach (array_keys(\App\Models\LogisticsProvider::SERVICE_REGIONS) as $key) {
                if (preg_match('/(^|[\s(])'.preg_quote($key, '/').'($|[\s)])/i', $region) || strcasecmp($region, $key) === 0) {
                    return $key;
                }
            }
            if (stripos($region, 'calabarzon') !== false) {
                return 'IV-A';
            }
        }

        return self::PROVINCE_REGION[self::normalize($province)] ?? null;
    }

    /** @return array{barangay: string, city: string, province: string, region: ?string} */
    public static function placeOf(Address|User|array|null $place): array
    {
        $get = fn (string $key) => match (true) {
            $place instanceof Address, $place instanceof User => $place->{$key} ?? null,
            is_array($place) => $place[$key] ?? null,
            default => null,
        };

        return [
            'barangay' => self::normalize($get('barangay')),
            'city' => self::normalize($get('city')),
            'province' => self::normalize($get('province')),
            'region' => self::regionOf($get('province'), $get('region')),
        ];
    }

    /** Whether the place has anything to compare. */
    public static function known(array $place): bool
    {
        return $place['city'] !== '' || $place['province'] !== '' || $place['region'] !== null;
    }

    /**
     * How close two places are, by name.
     *
     * @return array{level: ?string, score: int, label: ?string}
     */
    public static function compare(array $a, array $b): array
    {
        $sameProvince = $a['province'] !== '' && $a['province'] === $b['province'];
        // City names repeat across provinces (San Jose, Santa Cruz), so a city only counts when the province agrees or is missing.
        $provincesAgree = $sameProvince || $a['province'] === '' || $b['province'] === '';
        $sameCity = $provincesAgree && $a['city'] !== '' && $a['city'] === $b['city'];
        $level = match (true) {
            $sameCity && $a['barangay'] !== '' && $a['barangay'] === $b['barangay'] => 'barangay',
            $sameCity => 'city',
            $sameProvince => 'province',
            $a['region'] !== null && $a['region'] === $b['region'] => 'region',
            default => null,
        };

        return ['level' => $level, 'score' => $level ? self::LEVELS[$level]['score'] : 0, 'label' => $level ? self::LEVELS[$level]['label'] : null];
    }

    /**
     * How close a place is to an address that is only a line of text, such as
     * a buyer's "45 Magsaysay St, Bubukal, Santa Cruz, Laguna". The place's
     * names must appear in the text as whole words.
     *
     * @return array{level: ?string, score: int, label: ?string}
     */
    public static function compareToText(array $place, string $text): array
    {
        $text = ' '.self::normalize(str_replace(',', ' , ', $text)).' ';
        $has = fn (string $name) => $name !== '' && str_contains($text, ' '.$name.' ');

        $mentionsProvince = $has($place['province']);
        $mentionsOtherProvince = !$mentionsProvince && collect(array_keys(self::PROVINCE_REGION))
            ->contains(fn ($p) => mb_strlen($p) > 4 && str_contains($text, ' '.$p.' '));
        $sameCity = $has($place['city']) && !$mentionsOtherProvince;
        $level = match (true) {
            $sameCity && $has($place['barangay']) => 'barangay',
            $sameCity => 'city',
            $mentionsProvince => 'province',
            default => null,
        };

        return ['level' => $level, 'score' => $level ? self::LEVELS[$level]['score'] : 0, 'label' => $level ? self::LEVELS[$level]['label'] : null];
    }

    /** "Bubukal, Santa Cruz, Laguna" from the original (not normalised) fields. */
    public static function describe(Address|User|null $place, bool $withStreet = false): ?string
    {
        if (!$place) {
            return null;
        }
        $street = $withStreet ? ($place instanceof Address ? $place->line1 : $place->street_address) : null;
        $parts = array_filter(array_map(fn ($v) => trim((string) $v), [$street, $place->barangay ?? null, $place->city ?? null, $place->province ?? null]));

        return $parts ? implode(', ', $parts) : null;
    }
}
