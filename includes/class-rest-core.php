<?php

/**
 * Plugin-core REST endpoints.
 *
 * Lives OUTSIDE any single module so it's available even when the target
 * module is off — that's the point: the platform needs a way to turn a
 * module ON without the module itself being loaded (chicken-and-egg).
 *
 * Namespace: dailybuddy/v1
 *   GET  /modules         — list available/active modules
 *   POST /module/toggle   — enable or disable a single module
 *
 * Auth: manage_options, so Basic Auth with an Application Password works
 * out of the box.
 *
 * @package DailyBuddy
 */

if (! defined('ABSPATH')) {
    exit;
}

class Dailybuddy_Rest_Core
{
    const NAMESPACE_V1 = 'dailybuddy/v1';

    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes()
    {
        $perm = function () {
            return current_user_can('manage_options');
        };

        register_rest_route(self::NAMESPACE_V1, '/modules', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'list_modules'),
            'permission_callback' => $perm,
        ));

        register_rest_route(self::NAMESPACE_V1, '/module/toggle', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'toggle_module'),
            'permission_callback' => $perm,
            'args'                => array(
                'module_id' => array(
                    'required' => true,
                    'type'     => 'string',
                ),
                'active' => array(
                    'required' => true,
                    'type'     => 'boolean',
                ),
            ),
        ));
    }

    /**
     * List every module the loader finds, with its active state.
     * Handy for the platform to know what's available on a site.
     */
    public function list_modules(WP_REST_Request $request)
    {
        $loader = new Dailybuddy_Module_Loader();
        $loader->load_modules();
        $modules = $loader->get_modules();
        $active  = Dailybuddy_Settings::get_modules();

        $out = array();
        foreach ($modules as $category => $entries) {
            foreach ($entries as $folder => $data) {
                $id = $data['id']; // "<category>/<folder>"
                $out[] = array(
                    'id'       => $id,
                    'category' => $category,
                    'name'     => $data['config']['name']    ?? $folder,
                    'version'  => $data['config']['version'] ?? null,
                    'active'   => !empty($active[$id]),
                );
            }
        }

        return new WP_REST_Response(array('modules' => $out), 200);
    }

    /**
     * Flip a module on or off. Same code path the admin toggle uses —
     * saves to the "dailybuddy_modules" option. The change takes effect
     * on the NEXT request (WP has to re-load the module).
     */
    public function toggle_module(WP_REST_Request $request)
    {
        $module_id = (string) $request->get_param('module_id');
        $active    = (bool)   $request->get_param('active');

        if ($module_id === '') {
            return new WP_REST_Response(array(
                'code'    => 'missing_module_id',
                'message' => __('module_id is required.', 'dailybuddy'),
            ), 400);
        }

        // Only allow toggling modules that actually exist on disk. Prevents
        // the platform (or anyone) from writing arbitrary keys into the
        // dailybuddy_modules option.
        $loader = new Dailybuddy_Module_Loader();
        $loader->load_modules();
        $known = array();
        foreach ($loader->get_modules() as $category => $entries) {
            foreach ($entries as $folder => $data) {
                $known[$data['id']] = true;
            }
        }
        if (empty($known[$module_id])) {
            return new WP_REST_Response(array(
                'code'    => 'unknown_module',
                /* translators: %s: module identifier (e.g. "wpbuddy/connector") */
                'message' => sprintf(__('Module %s is not installed.', 'dailybuddy'), $module_id),
            ), 404);
        }

        $modules = Dailybuddy_Settings::get_modules();
        $modules[$module_id] = $active;
        $saved = Dailybuddy_Settings::save_modules($modules);

        // update_option returns false when the value is unchanged — that's
        // not an error, so also check whether the current stored state now
        // matches what we asked for.
        $now = Dailybuddy_Settings::get_modules();
        $ok  = isset($now[$module_id]) && (bool) $now[$module_id] === $active;

        if (!$ok) {
            return new WP_REST_Response(array(
                'code'    => 'save_failed',
                'message' => __('Could not save module state.', 'dailybuddy'),
            ), 500);
        }

        return new WP_REST_Response(array(
            'module_id' => $module_id,
            'active'    => $active,
            'saved'     => (bool) $saved,
        ), 200);
    }
}
