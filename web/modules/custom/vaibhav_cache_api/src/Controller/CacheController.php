<?php

namespace Drupal\vaibhav_cache_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Cache\CacheBackendInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Controller to handle cache refresh actions.
 */
class CacheController extends ControllerBase {

  /**
   * The cache backend service.
   */
  protected CacheBackendInterface $cacheBackend;

  /**
   * The request stack.
   */
  protected RequestStack $requestStack;

  /**
   * Constructs a CacheController.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   The cache backend service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   */
  public function __construct(
    CacheBackendInterface $cacheBackend,
    RequestStack $requestStack,
  ) {
    $this->cacheBackend = $cacheBackend;
    $this->requestStack = $requestStack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('cache.default'),
      $container->get('request_stack')
    );
  }

  /**
   * Clears cache and redirects.
   */
  public function refreshCache(): RedirectResponse {
    $this->cacheBackend->invalidate('vaibhav_cache_api.upcoming_events');
    $this->messenger()->addStatus($this->t('Cache has been refreshed.'));

    // Get referer from current request instead of using \Drupal static call.
    $referer = $this->requestStack->getCurrentRequest()->headers->get('referer') ?: '/';
    return new RedirectResponse($referer);
  }

}
