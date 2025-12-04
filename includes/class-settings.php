<?php
/**
 * Settings Handler Class
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Dailybuddy_Settings {
    
    /**
     * Speichert die Modul-Einstellungen
     */
    public static function save_modules( $modules ) {
        return update_option( 'dailybuddy_modules', $modules );
    }
    
    /**
     * Gibt die Modul-Einstellungen zurück
     */
    public static function get_modules() {
        return get_option( 'dailybuddy_modules', array() );
    }
    
    /**
     * Aktiviert ein Modul
     */
    public static function activate_module( $module_id ) {
        $modules = self::get_modules();
        $modules[ $module_id ] = true;
        return self::save_modules( $modules );
    }
    
    /**
     * Deaktiviert ein Modul
     */
    public static function deactivate_module( $module_id ) {
        $modules = self::get_modules();
        $modules[ $module_id ] = false;
        return self::save_modules( $modules );
    }
    
    /**
     * Prüft ob ein Modul aktiv ist
     */
    public static function is_module_active( $module_id ) {
        $modules = self::get_modules();
        return isset( $modules[ $module_id ] ) && $modules[ $module_id ];
    }
}
