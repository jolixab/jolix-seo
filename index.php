<?php
/**
 * Security file to prevent direct access to the plugin directory
 * 
 * @package JolixSEOMetaManager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access denied.');
}