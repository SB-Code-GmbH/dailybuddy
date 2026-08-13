<?php

/**
 * WPBuddy REST endpoints exposed to the platform side.
 *
 * All routes live under the `wpbuddy/v1` namespace. Filled in with
 * Task #22 — this stub reserves the namespace.
 *
 * @package DailyBuddy
 */

if (! defined('ABSPATH')) {
    exit;
}

class Dailybuddy_Platform_Connector_Rest
{
    const NAMESPACE_V1 = 'wpbuddy/v1';

    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes()
    {
        // Routes are added in Task #22.
        // Planned:
        //   POST /wpbuddy/v1/connect  — receive token + platform URL,
        //                                trigger the handshake against
        //                                that platform (only allowed for
        //                                users with manage_options).
    }
}
