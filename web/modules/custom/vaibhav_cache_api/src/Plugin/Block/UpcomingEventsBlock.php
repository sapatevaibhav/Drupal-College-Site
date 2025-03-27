<?php

namespace Drupal\vaibhav_cache_api\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an Upcoming Events block with cache timestamp and refresh button.
 */
#[Block(
  id: "upcoming_events_block",
  admin_label: new TranslatableMarkup("Upcoming Events with Cache")
)]
class UpcomingEventsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The cache backend service.
   */
  protected CacheBackendInterface $cacheBackend;

  /**
   * The entity type manager service.
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Constructs a new UpcomingEventsBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   The cache backend service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    CacheBackendInterface $cacheBackend,
    EntityTypeManagerInterface $entityTypeManager,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->cacheBackend = $cacheBackend;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('cache.default'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $cache_key = 'vaibhav_cache_api.upcoming_events';
    $cache = $this->cacheBackend->get($cache_key);

    if ($cache) {
      $events = $cache->data['events'];
      $cached_time = date('Y-m-d H:i:s', $cache->data['timestamp']);
    }
    else {
      $events = $this->fetchUpcomingEvents();
      $cached_time = date('Y-m-d H:i:s');
    }

    // Render event titles.
    $items = [];
    foreach ($events as $event) {
      $items[] = $event->label();
    }

    // Refresh button link.
    $refresh_url = Url::fromRoute('vaibhav_cache_api.refresh_cache');
    $refresh_link = Link::fromTextAndUrl($this->t('Refresh Cache'), $refresh_url)->toString();

    return [
      '#theme' => 'item_list',
      '#items' => $items,
      '#title' => $this->t('Upcoming Events'),
      '#suffix' => '<p class="text-gray-500 text-sm">Cache last updated: ' . $cached_time . '</p>
                    <p>' . $refresh_link . '</p>',
    // 10 minutes cache
      '#cache' => ['max-age' => 600],
    ];
  }

  /**
   * Fetches upcoming events and stores in cache.
   */
  private function fetchUpcomingEvents() {
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $nids = $query->condition('type', 'event')
      ->condition('status', 1)
      ->accessCheck(TRUE)
      ->sort('field_date', 'ASC')
      ->range(0, 5)
      ->execute();

    $events = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);

    // Store in cache with timestamp.
    $this->cacheBackend->set('vaibhav_cache_api.upcoming_events', [
      'events' => $events,
      'timestamp' => time(),
    ], time() + 600);

    return $events;
  }

}
