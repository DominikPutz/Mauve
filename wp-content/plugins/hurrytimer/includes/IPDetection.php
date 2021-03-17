<?php

namespace Hurrytimer;

class IPDetection
{
    /**
     * Evergreen IP entries table name.
     *
     * @var string
     */
    private $table;

    /**
     * Cleanup idle IP entries action name.
     */
    const CLEANUP_ACTION = "hurrytimer_evergreen_daily_cleanup";

    public function __construct()
    {
        global $wpdb;
        $this->table = "{$wpdb->prefix}hurrytimer_evergreen";
        add_action(self::CLEANUP_ACTION, [$this, 'cleanup_idle_entries']);
    }

    /**
     * Find IP log.
     *
     * @param int    $countdown_id
     * @param string $client_ip_address
     *
     * @return array|null
     */
    public function find($countdown_id, $client_ip_address = null)
    {
        global $wpdb;
        if (is_null($client_ip_address)) {
            $client_ip_address = Utils\Helpers::ip_address();
        }

        $sql = $wpdb->prepare(
            "SELECT * FROM {$this->table}
            WHERE countdown_id = %d
            AND client_ip_address = %s",
            $countdown_id,
            $client_ip_address
        );

        $result = $wpdb->get_row($sql, ARRAY_A);

        if ( ! $result) {
            $result = $this->legacy_find($countdown_id, $client_ip_address);
        }

        return $result;
    }

    /**
     * This provide compatibility for prior versions.
     *
     * @param int    $countdown_id
     * @param string $client_id_address
     *
     * @return array|null
     *
     */
    private function legacy_find($countdown_id, $client_id_address)
    {
        $client_id_address_key = sanitize_key($client_id_address);

        $transient = sprintf(
            'ht_cdt_%d_%s',
            $countdown_id,
            $client_id_address_key
        );

        $expires_at_transient = get_transient($transient);

        if ( ! $expires_at_transient) {
            return null;
        }

        $result = $this->create(
            $countdown_id,
            $expires_at_transient,
            $client_id_address
        );
        if ($result) {
            // Clear transients.
            delete_transient($transient);
            delete_transient("{$transient}_status");

            return $this->find($countdown_id, $client_id_address);
        }

        return null;
    }

    /**
     * Create a new IP log.
     *
     * @param int    $countdown_id
     * @param int    $client_expires_at
     * @param string $client_ip_address
     *
     * @return bool
     */
    public function create(
        $countdown_id,
        $client_expires_at,
        $client_ip_address = null
    ) {
        global $wpdb;

        if (is_null($client_ip_address)) {
            $client_ip_address = Utils\Helpers::ip_address();
        }

        // Auto-destroy after one month.
        $destroy_at = Utils\Helpers::date_later(MONTH_IN_SECONDS);

        $result = $wpdb->insert(
            $this->table,
            compact(
                'countdown_id',
                'client_ip_address',
                'client_expires_at',
                'destroy_at'
            )
        );

        if ($result !== false) {
            $this->maybe_schedule_destroy();

            return true;
        }

        return false;
    }

    /**
     *
     * Update client expiration time.
     *
     * @param $id
     * @param $client_expires_at
     *
     * @return false|int
     */
    public function update($id, $client_expires_at)
    {
        global $wpdb;

        $destroy_at = Utils\Helpers::date_later(MONTH_IN_SECONDS);

        return $wpdb->update(
            $this->table,
            compact('client_expires_at', 'destroy_at'),
            compact('id')
        );
    }

    /**
     * Delete given countdown and IP entry.
     *
     * @param             $campaignId
     * @param null|string $ipAddress
     *
     * @return void
     */
    public function forget($campaignId, $ipAddress = null)
    {
        global $wpdb;
        $where = [
            'countdown_id' => $campaignId,
        ];
        if ($ipAddress !== null) {
            $where['client_ip_address'] = $ipAddress;
        }
        $wpdb->delete($this->table, $where);
        if ($ipAddress === null) {
            if ( ! $this->has_entries()) {
                $this->clear_scheduled_destroy();
            }
        }

    }

     function forgetAll($ipAddress = null)
    {
        global $wpdb;
        if ($ipAddress !== null) {
            $where['client_ip_address'] = $ipAddress;
            $wpdb->delete($this->table, $where);
        }
        else{
            $wpdb->query("delete from {$this->table}");
        }
        if ( ! $this->has_entries()) {
            $this->clear_scheduled_destroy();
        }
    }

    /**
     * Clean up IP entries with passed `destroy_at`.
     *
     * @return void
     */
    private function cleanup_idle_entries()
    {
        global $wpdb;
        $now = current_time('mysql');
        $sql = "DELETE FROM {$this->table} WHERE destroy_at < $now";
        $wpdb->query($sql);
    }

    /**
     * Check if there is at least one IP entry.
     *
     * @return bool
     */
    private function has_entries()
    {
        global $wpdb;

        $count = $wpdb->get_var("SELECT count(*) FROM {$this->table}");

        if (is_null($count)) {
            return false;
        }

        return $count > 0;
    }

    /**
     * Schedule cleanup of IP entries with passed `destroy_at`.
     *
     * @return void
     */
    private function maybe_schedule_destroy()
    {
        if ( ! wp_next_scheduled(self::CLEANUP_ACTION)) {
            wp_schedule_event(
                current_time('timestamp'),
                'daily',
                self::CLEANUP_ACTION
            );
        }
    }

    /**
     * Clear sheduled destroy event.
     *
     * @return void
     */
    private function clear_scheduled_destroy()
    {
        wp_clear_scheduled_hook(self::CLEANUP_ACTION);
    }
}
