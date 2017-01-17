<?php
/**
 * Groups configuration for default Minify implementation
 * @package Minify
 */

/** 
 * You may wish to use the Minify URI Builder app to suggest
 * changes. http://yourdomain/min/builder/
 **/

return array(
    // custom source example
    'general' => array(
     	$min_documentRoot . '/engine/classes/js/jquery.js',
     	$min_documentRoot . '/engine/classes/js/jqueryui.js',
     	$min_documentRoot . '/engine/classes/js/dle_js.js',
    ),

    'admin' => array(
     	$min_documentRoot . '/engine/skins/javascripts/application.js', 
    ),
);