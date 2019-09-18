<?php

namespace Innocode\WPThemeModule\Abstracts;

use Innocode\WPThemeModule\Interfaces\ThemeInitializationInterface;

/**
 * Class AbstractThemeInitialization
 * @package Innocode\WPThemeModule
 */
abstract class AbstractThemeInitialization extends AbstractRegistrar implements ThemeInitializationInterface
{
	/**
	 * Initializes module
	 */
    public function run()
    {
        parent::run();

        add_action( 'after_setup_theme', function () {
            $this->_load_theme_text_domain();
            $this->_add_image_sizes();
            $this->_register_nav_menus();
        }, 0 );

        add_action( 'wp_enqueue_scripts', function () {
            $this->enqueue_styles();
            $this->enqueue_scripts();
        } );
    }

	/**
	 * Loads theme text domain depending on TEXT_DOMAIN constant existing and value
	 *
	 * @uses load_theme_textdomain()
	 */
    private function _load_theme_text_domain()
    {
        if ( defined( 'TEXT_DOMAIN' ) && TEXT_DOMAIN ) {
            load_theme_textdomain( TEXT_DOMAIN, get_template_directory() . '/languages' );
        }
    }

	/**
	 * Adds image sizes for attachments
	 *
	 * @uses add_image_size()
	 */
    private function _add_image_sizes()
    {
        foreach ( $this->get_image_sizes() as $key => $data ) {
            $width = isset( $data[ 0 ] ) ? $data[ 0 ] : 0;
            $height = isset( $data[ 1 ] ) ? $data[ 1 ] : 0;
            $crop = isset( $data[ 2 ] ) ? $data[ 2 ] : false;

            add_image_size( $key, $width, $height, $crop );
        }
    }

	/**
	 * Registers WordPress navigation menus
	 *
	 * @uses register_nav_menus()
	 */
    private function _register_nav_menus()
    {
        register_nav_menus( $this->get_nav_menus_locations() );
    }
}
