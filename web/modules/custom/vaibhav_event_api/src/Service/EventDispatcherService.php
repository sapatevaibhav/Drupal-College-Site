<?php

namespace Drupal\vaibhav_event_api\Service;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Drupal\vaibhav_event_api\Event\MyCustomEvent;

/**
 * Service to dispatch custom events.
 */
class EventDispatcherService {
  /**
   * The event dispatcher.
   *
   * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
   */
  protected EventDispatcherInterface $eventDispatcher;

  public function __construct(EventDispatcherInterface $event_dispatcher) {
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * Dispatches a custom event.
   */
  public function dispatchCustomEvent(string $message) {
    $event = new MyCustomEvent($message);
    $this->eventDispatcher->dispatch($event, MyCustomEvent::EVENT_NAME);
  }

}
