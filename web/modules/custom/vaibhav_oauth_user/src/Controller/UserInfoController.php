<?php

namespace Drupal\vaibhav_oauth_user\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides a controller for the User Info endpoint.
 */
class UserInfoController extends ControllerBase {

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * UserInfoController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(
    RequestStack $request_stack,
    AccountProxyInterface $current_user,
    EntityTypeManagerInterface $entity_type_manager,
  ) {
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('current_user'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Returns the current user's information.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response containing user information.
   */
  public function me(): JsonResponse {
    if ($this->currentUser->isAnonymous()) {
      return new JsonResponse(['error' => 'Unauthorized'], 401);
    }

    try {
      /** @var \Drupal\user\UserInterface $user */
      $user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());

      return new JsonResponse([
        'uid' => $user->id(),
        'name' => $user->getAccountName(),
        'mail' => $user->getEmail(),
        'roles' => $user->getRoles(),
      ]);
    }
    catch (\Exception $e) {
      return new JsonResponse(['error' => 'Error loading user data'], 500);
    }
  }

}
