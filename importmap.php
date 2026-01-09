<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    'edit' => [
        'path' => './assets/edit.js',
        'entrypoint' => true,
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    'bootstrap' => [
        'version' => '5.3.8',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'tom-select/dist/css/tom-select.bootstrap5.css' => [
        'version' => '2.4.3',
        'type' => 'css',
    ],
    'php-date-formatter' => [
        'version' => '1.3.7',
    ],
    'luxon' => [
        'version' => '3.7.2',
    ],
    'jquery' => [
        'version' => '3.7.1',
    ],
    'jquery-datetimepicker' => [
        'version' => '2.5.21',
    ],
    'tom-select/dist/css/tom-select.default.css' => [
        'version' => '2.4.3',
        'type' => 'css',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.8',
        'type' => 'css',
    ],
    'visavail' => [
        'version' => '1.5.1',
    ],
    'd3' => [
        'version' => '5.9.2',
    ],
    'd3-array' => [
        'version' =>'1.2.4'
    ],
    'd3-axis' => [
        'version' =>'1.0.12'
    ],
    'd3-brush' => [
        'version' =>'1.0.6'
    ],
    'd3-chord' => [
        'version' =>'1.0.6'
    ],
    'd3-collection' => [
        'version' =>'1.0.7'
    ],
    'd3-color' => [
        'version' =>'1.2.3'
    ],
    'd3-contour' => [
        'version' =>'1.3.2'
    ],
    'd3-dispatch' => [
        'version' =>'1.0.5'
    ],
    'd3-drag' => [
        'version' =>'1.2.3'
    ],
    'd3-dsv' => [
        'version' =>'1.1.1'
    ],
    'd3-ease' => [
        'version' =>'1.0.5'
    ],
    'd3-fetch' => [
        'version' =>'1.1.2'
    ],
    'd3-force' => [
        'version' =>'1.2.1'
    ],
    'd3-format' => [
        'version' =>'1.3.2'
    ],
    'd3-geo' => [
        'version' =>'1.11.3'
    ],
    'd3-hierarchy' => [
        'version' =>'1.1.8'
    ],
    'd3-interpolate' => [
        'version' =>'1.3.2'
    ],
    'd3-path' => [
        'version' =>'1.0.7'
    ],
    'd3-polygon' => [
        'version' =>'1.0.5'
    ],
    'd3-quadtree' => [
        'version' =>'1.0.6'
    ],
    'd3-random' => [
        'version' =>'1.1.2'
    ],
    'd3-scale' => [
        'version' =>'2.2.2'
    ],
    'd3-scale-chromatic' => [
        'version' =>'1.3.3'
    ],
    'd3-selection' => [
        'version' =>'1.4.0'
    ],
    'd3-shape' => [
        'version' =>'1.3.4'
    ],
    'd3-time' => [
        'version' =>'1.0.11'
    ],
    'd3-time-format' => [
        'version' =>'2.1.3'
    ],
    'd3-timer' => [
        'version' =>'1.0.9'
    ],
    'd3-transition' => [
        'version' =>'1.2.0'
    ],
    'd3-voronoi' => [
        'version' =>'1.1.4'
    ],
    'd3-zoom' => [
        'version' =>'1.7.3'
    ],
    'moment' => [
        'version' => '2.30.1',
    ],
    'internmap' => [
        'version' => '2.0.3',
    ],
    'delaunator' => [
        'version' => '5.0.1',
    ],
    'robust-predicates' => [
        'version' => '3.0.2',
    ],
    'jquery-datetimepicker/build/jquery.datetimepicker.min.css' => [
        'version' => '2.5.21',
        'type' => 'css',
    ],
    'visavail/visavail.css' => [
        'version' => '1.5.1',
        'type' => 'css',
    ],
];
