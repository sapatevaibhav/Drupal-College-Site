<?php

namespace Drupal\vaibhav_migration_api\Plugin\migrate\process;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Password\PasswordInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Processes and hashes passwords during migration.
 *
 * @MigrateProcessPlugin(
 *   id = "hash_password"
 * )
 */
class HashPassword extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The password hasher service.
   *
   * @var \Drupal\Core\Password\PasswordInterface
   */
  protected $passwordHasher;

  /**
   * Constructs a HashPassword plugin.
   *
   * @param array $configuration
   *   A configuration array.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Password\PasswordInterface $password_hasher
   *   The password hasher service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PasswordInterface $password_hasher) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->passwordHasher = $password_hasher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('password')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    return $this->passwordHasher->hash($value);
  }

}
