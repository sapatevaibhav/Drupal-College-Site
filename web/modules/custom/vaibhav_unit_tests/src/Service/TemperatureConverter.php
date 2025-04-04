<?php

namespace Drupal\vaibhav_unit_tests\Service;

/**
 * Temperature conversion service.
 */
class TemperatureConverter {

  /**
   * Convert Celsius to Fahrenheit.
   */
  public function celsiusToFahrenheit($celsius) {
    return ($celsius * 9 / 5) + 32;
  }

}
