<?php

namespace Drupal\vaibhav_event_api\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Defines a custom event.
 */
class MyCustomEvent extends Event {

  public const EVENT_NAME = 'vaibhav_event_api.custom_event';

  /**
   * Declare the message.
   *
   * @var string
   */
  protected string $message;

  public function __construct(string $message) {
    $this->message = $message;
  }

  /**
   * Returns the message.
   */
  public function getMessage(): string {
    return $this->message;
  }

}
