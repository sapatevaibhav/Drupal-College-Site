<?php

namespace Drupal\vaibhav_event_api\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\vaibhav_event_api\Service\EventDispatcherService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller to test event dispatching.
 */
class EventTestController extends ControllerBase {

  /**
   * The event dispatcher service.
   *
   * @var \Drupal\vaibhav_event_api\Service\EventDispatcherService
   */
  protected EventDispatcherService $eventDispatcherService;

  public function __construct(EventDispatcherService $eventDispatcherService) {
    $this->eventDispatcherService = $eventDispatcherService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('vaibhav_event_api.event_dispatcher_service'));
  }

  /**
   * Triggers a custom event.
   */
  public function triggerEvent() {
    $this->eventDispatcherService->dispatchCustomEvent('Event triggered from the browser!');
    return new Response('Event dispatched successfully.');
  }

}
