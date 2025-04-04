<?php

namespace Drupal\Tests\vaibhav_unit_tests\Unit;

use Drupal\vaibhav_unit_tests\Service\TemperatureConverter;
use PHPUnit\Framework\TestCase;

/**
 * Test the TemperatureConverter service.
 */
class TemperatureConverterTest extends TestCase {

  /**
   * The temperature converter service.
   *
   * @var \Drupal\vaibhav_unit_tests\Service\TemperatureConverter
   */
  protected $converter;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->converter = new TemperatureConverter();
  }

  /**
   * Test the freezing point conversion.
   */
  public function testFreezingPoint() {
    $this->assertEquals(32, $this->converter->celsiusToFahrenheit(0), 'Freezing point (0°C) should be 32°F');
  }

  /**
   * Test the boiling point conversion.
   */
  public function testBoilingPoint() {
    $this->assertEquals(212, $this->converter->celsiusToFahrenheit(100), 'Boiling point (100°C) should be 212°F');
  }

  /**
   * Test body temperature conversion.
   */
  public function testBodyTemperature() {
    $this->assertEquals(98.6, $this->converter->celsiusToFahrenheit(37), 'Body temperature (37°C) should be 98.6°F', 0.1);
  }

  /**
   * Test negative temperature conversion.
   */
  public function testNegativeTemperature() {
    $this->assertEquals(-4, $this->converter->celsiusToFahrenheit(-20), 'Extreme cold (-20°C) should be -4°F');
  }

  /**
   * Test with decimal values.
   */
  public function testDecimalTemperature() {
    $this->assertEquals(68.9, $this->converter->celsiusToFahrenheit(20.5), 'Decimal values should convert properly', 0.1);
  }

  /**
   * Test data provider approach with multiple test cases.
   *
   * @dataProvider temperatureConversionProvider
   */
  public function testMultipleConversions($celsius, $expectedFahrenheit, $delta = 0.01) {
    $this->assertEquals($expectedFahrenheit, $this->converter->celsiusToFahrenheit($celsius), '', $delta);
  }

  /**
   * Data provider for temperature conversion tests.
   */
  public function temperatureConversionProvider(): array {
    return [
      'Absolute zero' => [-273.15, -459.66999999999996, 0.01],
      'Room temperature' => [22, 71.6, 0.01],
      'Hot summer day' => [35, 95, 0.01],
      'Desert high' => [50, 122, 0.01],
    ];
  }

}
