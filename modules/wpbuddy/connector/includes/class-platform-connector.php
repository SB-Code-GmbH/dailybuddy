<?php

/**
 * Main orchestrator for the Platform Connector module.
 *
 * Composes the smaller concerns (Keypair storage, Connection state,
 * REST endpoints, Admin page) and wires them into WordPress. Each
 * sub-class registers its own hooks in its constructor, so the
 * orchestrator just instantiates them.
 *
 * @package DailyBuddy
 */

if (! defined('ABSPATH')) {
    exit;
}

class Dailybuddy_Platform_Connector
{

    /**
     * Option keys used across the module. Kept in one place so it's
     * obvious what state we persist in wp_options.
     */
    // The WP site has ONE identity keypair, shared across all pairings.
    const OPT_PRIVATE_KEY = 'dailybuddy_pc_private_key';
    const OPT_PUBLIC_KEY  = 'dailybuddy_pc_public_key';

    // Multi-pair store: JSON array of pairings, each with its own site
    // UUID + platform pubkey. Replaces the old single-value options
    // below (kept as constants only for the one-time migration).
    const OPT_PAIRINGS = 'dailybuddy_pc_pairings';

    // Legacy single-connection options — used only to migrate existing
    // sites into OPT_PAIRINGS on first read.
    const OPT_PLATFORM_URL      = 'dailybuddy_pc_platform_url';
    const OPT_PLATFORM_PUB_KEY  = 'dailybuddy_pc_platform_public_key';
    const OPT_SITE_UUID         = 'dailybuddy_pc_site_uuid';
    const OPT_CONNECTED_AT      = 'dailybuddy_pc_connected_at';
    const OPT_LAST_HEARTBEAT_AT = 'dailybuddy_pc_last_heartbeat_at';
    const OPT_LAST_ERROR        = 'dailybuddy_pc_last_error';

    public function __construct()
    {
        // Each of these is self-contained: adds its own filters/actions
        // to the WordPress lifecycle in its constructor.
        new Dailybuddy_Platform_Connector_Keypair();
        new Dailybuddy_Platform_Connector_Connection();
        new Dailybuddy_Platform_Connector_Traffic();
        new Dailybuddy_Platform_Connector_Rest();

        // Admin page hooks are separate — they're registered when the
        // callback is invoked from the plugin's settings screen.
    }

    /**
     * Convenience: is this site paired with at least one platform?
     */
    public static function is_connected()
    {
        $conn = new Dailybuddy_Platform_Connector_Connection();
        return count($conn->get_pairings()) > 0;
    }
}
