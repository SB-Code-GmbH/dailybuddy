<?php
/**
 * Module Loader Class
 * Scannt automatisch alle Module und lädt sie
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Dailybuddy_Module_Loader {
    
    private $modules = array();
    private $active_modules = array();
    
    public function __construct() {
        $this->active_modules = get_option( 'dailybuddy_modules', array() );
    }
    
    /**
     * Scannt das modules Verzeichnis und lädt alle verfügbaren Module
     */
    public function load_modules() {
        $modules_path = DAILYBUDDY_PATH . 'modules/';
        
        // Alle Kategorie-Ordner durchgehen
        $categories = $this->get_directories( $modules_path );
        
        foreach ( $categories as $category ) {
            $category_path = $modules_path . $category . '/';
            
            // Alle Module in dieser Kategorie durchgehen
            $module_folders = $this->get_directories( $category_path );
            
            foreach ( $module_folders as $module_folder ) {
                $module_path = $category_path . $module_folder . '/';
                
                // Prüfe ob config.php und module.php existieren
                if ( file_exists( $module_path . 'config.php' ) && file_exists( $module_path . 'module.php' ) ) {
                    
                    // Lade Modul-Config
                    $config = include $module_path . 'config.php';
                    
                    $module_id = $category . '/' . $module_folder;
                    
                    // Speichere Modul-Informationen
                    $this->modules[ $category ][ $module_folder ] = array(
                        'id' => $module_id,
                        'path' => $module_path,
                        'config' => $config,
                        'active' => isset( $this->active_modules[ $module_id ] ) ? $this->active_modules[ $module_id ] : false
                    );
                    
                    // Lade das Modul, wenn es aktiv ist
                    if ( isset( $this->active_modules[ $module_id ] ) && $this->active_modules[ $module_id ] ) {
                        require_once $module_path . 'module.php';
                    }
                }
            }
        }
    }
    
    /**
     * Gibt alle verfügbaren Module zurück
     */
    public function get_modules() {
        return $this->modules;
    }
    
    /**
     * Hilfsfunktion: Gibt alle Verzeichnisse in einem Pfad zurück
     */
    private function get_directories( $path ) {
        if ( ! is_dir( $path ) ) {
            return array();
        }
        
        $directories = array();
        $items = scandir( $path );
        
        foreach ( $items as $item ) {
            if ( $item !== '.' && $item !== '..' && is_dir( $path . $item ) ) {
                $directories[] = $item;
            }
        }
        
        return $directories;
    }
}
