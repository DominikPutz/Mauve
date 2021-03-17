<?php

namespace Hurrytimer;

use http\Cookie;
use Hurrytimer\Utils\Helpers;

class EvergreenCampaign extends Campaign
{
    /**
     * @var CookieDetection
     */
    private $cookieDetection;

    /**
     * @var IPDetection
     */
    private $IPDetection;

    const RESET_FLAG = '_hurrytimer_reset_compaign_flag';

    public function __construct($id, $cookieDetection, $IPDetection)
    {
        parent::__construct($id);
        $this->IPDetection     = $IPDetection;
        $this->cookieDetection = $cookieDetection;
    }

    /**
     * Reset timer.
     *
     * @param string $scope
     */

    public function reset($scope = 'admin')
    {
        if ($scope === 'admin') {
            $this->IPDetection->forget($this->get_id(), Helpers::ip_address());
        } else {
            $this->IPDetection->forget($this->get_id());
            $this->resetBrowserCookie();
        }
    }

    private function resetBrowserCookie()
    {
        $_cookieKey = CookieDetection::cookieName($this->get_id());
        if (isset($_COOKIE[$_cookieKey])) {
            unset($_COOKIE[$_cookieKey]);
        }

        setcookie($_cookieKey, '', time() - YEAR_IN_SECONDS);
        // Force cookie deletion on next request.
        update_post_meta($this->get_id(), self::RESET_FLAG, 1);
    }

     function resetAll($scope = 'admin')
    {
        if ($scope === 'admin') {
           $this->IPDetection->forgetAll(Helpers::ip_address());
        } else {
            $this->IPDetection->forgetAll();
            // Delete cookies
            $campaigns = Helpers::getCampaigns();
            foreach ($campaigns as $campaign) {
                $_cookieKey = CookieDetection::cookieName($campaign->ID);
                if (isset($_COOKIE[$_cookieKey])) {
                    unset($_COOKIE[$_cookieKey]);
                }

                setcookie($_cookieKey, '', time() - YEAR_IN_SECONDS);
                // Force cookie deletion on next request.
                update_post_meta($campaign->ID, self::RESET_FLAG, 1);
            }
        }
    }

    /**
     * Returns client expiration time.
     *
     * @return int
     */
    public function getEndDate()
    {
        // Get expire timestamp if cookie exists.
        $clientEndTimestamp = $this->cookieDetection->find($this->get_id());

        // If cookie doesn't exist.
        if (is_null($clientEndTimestamp)) {
            // Fallback to IP detection
            $result = $this->IPDetection->find($this->get_id());

            if ($result) {
                // Return IP `client_expires_at`.
                return $result['client_expires_at'];
            }

            // A new cookie will be created from client side
            // containing the expiration timestamp.
            return null;
        }

        $result = $this->IPDetection->find($this->get_id());

        if ($result) {
            // Update IP expiration timestamp.
            $this->IPDetection->update($result['id'], $clientEndTimestamp);
        } else {
            // We create an IP entry.
            $this->IPDetection->create($this->get_id(), $clientEndTimestamp);
        }

        return $clientEndTimestamp;
    }

    function setEndDate($timestamp)
    {
        setcookie(CookieDetection::cookieName($this->get_id()), $timestamp,
            time() + YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN);

        return $this->getEndDate();
    }

    /**
     * Returns true to force reset timer.
     *
     * @return bool
     */
    public function reseting()
    {
        $reset = filter_var(
            get_post_meta($this->get_id(), self::RESET_FLAG, true),
            FILTER_VALIDATE_BOOLEAN
        );

        delete_post_meta($this->get_id(), self::RESET_FLAG);

        return $reset;
    }

    /**
     * Returns given timer's cookie name.
     *
     * @return string
     */
    public function cookieName()
    {
        return CookieDetection::cookieName($this->get_id());
    }
}
