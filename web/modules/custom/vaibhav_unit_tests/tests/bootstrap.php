<?php

/**
 * @file
 * Bootstrap file for unit tests.
 */

require_once dirname(__DIR__, 5) . "/vendor/autoload.php";

// Explicitly register our module namespace.
spl_autoload_register(function ($class) {
  if (strpos($class, "Drupal\\vaibhav_unit_tests\\") === 0) {
    $path = str_replace("\\", "/", $class);
    $path = str_replace("Drupal/vaibhav_unit_tests/", "", $path);
    $file = dirname(__DIR__) . "/src/" . $path . ".php";
    if (file_exists($file)) {
      require_once $file;
    }
  }
});
