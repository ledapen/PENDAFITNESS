<?php

class CityValidator
{
    public static function existsInFrance(string $city): ?bool
    {
        $city = trim($city);

        if ($city === '') {
            return false;
        }

        $url = 'https://geo.api.gouv.fr/communes'
            . '?nom=' . rawurlencode($city)
            . '&fields=nom,code'
            . '&boost=population'
            . '&limit=10';

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 3,
                'header' => "Accept: application/json\r\n",
                'ignore_errors' => true
            ]
        ]);

        $response = @file_get_contents($url, false, $context);

        // Si l'API officielle est temporairement indisponible,
        // on ne prétend pas que la ville est inexistante.
        if ($response === false) {
            return null;
        }

        $cities = json_decode($response, true);

        if (!is_array($cities)) {
            return null;
        }

        $normalize = static function (string $value): string {
            $value = mb_strtolower(trim($value), 'UTF-8');

            $converted = iconv(
                'UTF-8',
                'ASCII//TRANSLIT//IGNORE',
                $value
            );

            $value = $converted !== false ? $converted : $value;

            $value = preg_replace('/[^a-z0-9]+/', ' ', $value);

            return trim(preg_replace('/\s+/', ' ', $value));
        };

        $searchedCity = $normalize($city);

        foreach ($cities as $result) {
            if (
                isset($result['nom']) &&
                $normalize((string) $result['nom']) === $searchedCity
            ) {
                return true;
            }
        }

        return false;
    }
}