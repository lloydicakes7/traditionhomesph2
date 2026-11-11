<?php

namespace App\Utility;

/**
 * Domain-agnostic Google Maps URL builder.
 *
 * Turns a place query — an address, a landmark, a set of coordinates — into
 * the URLs the public site embeds and links to, so no view or controller has
 * to assemble Maps URLs itself.
 *
 * Both endpoints are the keyless ones: they need no API key and no billing
 * account, which is all the association's public "where are we" panel needs.
 */
class MapHelper
{
    /** @var string Keyless Maps endpoint the embedded map is served from */
    const EMBED_ENDPOINT = 'https://www.google.com/maps';

    /** @var string Keyless Maps endpoint the directions link opens */
    const DIRECTIONS_ENDPOINT = 'https://www.google.com/maps/dir/';

    /**
     * URL for the map to put in an <iframe> for the given place query.
     *
     * @param  string  $query
     * @return string
     */
    public static function embedUrl(string $query): string
    {
        return self::EMBED_ENDPOINT . '?' . http_build_query([
            'q'      => trim($query),
            'output' => 'embed',
        ]);
    }

    /**
     * URL that opens Google Maps with directions to the given place query.
     *
     * @param  string  $query
     * @return string
     */
    public static function directionsUrl(string $query): string
    {
        return self::DIRECTIONS_ENDPOINT . '?' . http_build_query([
            'api'         => 1,
            'destination' => trim($query),
        ]);
    }
}
