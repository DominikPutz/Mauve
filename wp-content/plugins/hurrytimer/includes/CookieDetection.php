<?php
namespace Hurrytimer;

class CookieDetection
{
    const COOKIE_PREFIX = '_ht_CDT-';

    /**
     * Find Campaign timer cookie.
     *
     * @param int $compaignId
     *
     * @return int|null
     */
    public function find( $compaignId)
    {

        $cookie_name = self::cookieName( $compaignId);
        if (!isset($_COOKIE[$cookie_name]) || empty($_COOKIE[$cookie_name])) {
            return null;
        }

        return $_COOKIE[$cookie_name];
    }

    /**
     * Cookie name with given Campaign id.
     *
     * @param int $compaignId
     *
     * @return string
     */
    public static function cookieName( $compaignId)
    {
        return self::COOKIE_PREFIX . $compaignId;
    }
}
