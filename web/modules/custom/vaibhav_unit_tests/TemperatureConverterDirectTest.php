<?php

/**
 * @file
 * Include the class file directly.
 */

require_once __DIR__ . "/src/Service/TemperatureConverter.php";

use Drupal\vaibhav_unit_tests\Service\TemperatureConverter;

use PHPUnit\Framework\TestCase;

// Only define the test class if we're running under PHPUnit
// This prevents errors when running the file directly.
if (class_exists('PHPUnit\Framework\TestCase')) {


  /**
   * Tests the temperature converter functionality.
   */
  class TemperatureConverterDirectTest extends TestCase {

    /**
     * Tests the celsiusToFahrenheit conversion.
     */
    public function testCelsiusToFahrenheit() {
      $converter = new TemperatureConverter();

      // Test freezing point.
      $this->assertEquals(32, $converter->celsiusToFahrenheit(0),
        "Converting 0°C should equal 32°F");

      // Test boiling point.
      $this->assertEquals(212, $converter->celsiusToFahrenheit(100),
        "Converting 100°C should equal 212°F");
    }

  }
}

/**
 * Function for direct testing without PHPUnit.
 */
function test_celsius_to_fahrenheit() {
  $converter = new TemperatureConverter();
  $result = $converter->celsiusToFahrenheit(0);

  if ($result === 32) {
    echo "✓ Test passed! 0°C converts to 32°F correctly.\n";
  }
  else {
    echo "✗ Test failed! Expected 32°F but got {$result}°F.\n";
  }

  $result = $converter->celsiusToFahrenheit(100);
  if ($result === 212) {
    echo "✓ Test passed! 100°C converts to 212°F correctly.\n";
  }
  else {
    echo "✗ Test failed! Expected 212°F but got {$result}°F.\n";
  }
}

// Only run the direct test when this file is executed directly.
if (basename($_SERVER['SCRIPT_FILENAME']) == basename(__FILE__)) {
  echo "Running Temperature Converter direct tests...\n";
  test_celsius_to_fahrenheit();
}
