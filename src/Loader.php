<?php

namespace Innocode\WPThemeModule;

use ArrayAccess;
use IteratorAggregate;
use Countable;
use DirectoryIterator;
use Traversable;
use ArrayIterator;

/**
 * Class Loader
 * @package Innocode\WPThemeModule
 */
final class Loader implements ArrayAccess, IteratorAggregate, Countable
{
    /**
     * @var array
     */
    private $_modules = [];

    public function __construct( array $modules = null )
    {
        if ( is_null( $modules ) ) {
            $this->_modules = $this->scan_modules_dir();
        } else {
            foreach ( $modules as $key => $module ) {
                $this[ $key ] = $module;
            }
        }
    }

    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists( $offset )
    {
        return isset( $this->_modules[ $offset ] );
    }

    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet( $offset )
    {
        return isset( $this->_modules[ $offset ] ) ? $this->_modules[ $offset ] : null;
    }

    /**
     * Offset to set
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet( $offset, $value )
    {
        $path = $this->get_modules_dir() . "/$value";
        $module = new Module( $path, $value );

        if ( is_null( $offset ) ) {
            $this->_modules[] = $module;
        } else {
            $this->_modules[ $offset ] = $module;
        }
    }

    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset( $offset )
    {
        unset( $this->_modules[ $offset ] );
    }

    /**
     * Retrieve an external iterator
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new ArrayIterator( $this->_modules );
    }

    /**
     * Count elements of an object
     * @link https://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return count( $this->_modules );
    }

    /**
     * @return string
     */
    public function get_modules_dir()
    {
        return get_stylesheet_directory() . '/modules';
    }

    public function run()
    {
        foreach ( $this as $module ) {
            $this->load( $module );
        }
    }

    /**
     * @return array
     */
    public function scan_modules_dir()
    {
        $modules_dir = $this->get_modules_dir();
        $dir_iterator = new DirectoryIterator( $modules_dir );
        $modules = [];

        foreach ( $dir_iterator as $file ) {
            if ( $file->isDot() || ! $file->isDir() ) {
                continue;
            }

            $modules[] = new Module( $file->getRealPath(), $file->getFilename() );
        }

        return $modules;
    }

    /**
     * @param Module $module
     */
    public function load( Module $module )
    {
        $loaded = $module->load_required_classes();

        if ( defined( 'WP_DEBUG' ) && WP_DEBUG && is_wp_error( $loaded ) ) {
            wp_die( $loaded );
        }

        $initialized = $module->init();

        if ( defined( 'WP_DEBUG' ) && WP_DEBUG && is_wp_error( $initialized ) ) {
            wp_die( $initialized );
        }
    }
}
