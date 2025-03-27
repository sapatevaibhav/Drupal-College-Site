<?php

namespace Drupal\vaibhav_event_api\EventSubscriber;

use Drupal\vaibhav_event_api\Event\MyCustomEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Event subscriber for custom events.
 */
class MyEventSubscriber implements EventSubscriberInterface {

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  public function __construct(LoggerChannelFactoryInterface $loggerFactory) {
    // Get the logger channel dynamically.
    $this->logger = $loggerFactory->get('vaibhav_event_api');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      MyCustomEvent::EVENT_NAME => 'onCustomEvent',
    ];
  }

  /**
   * Custom event handler.
   */
  public function onCustomEvent(MyCustomEvent $event): void {
    $this->logger->info('Custom event triggered: ' . $event->getMessage());
  }

}
