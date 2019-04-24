<?php

namespace Drupal\hello\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Provides a sessions count block.
 *
 * @Block(
 *  id = "hello_session_count_block",
 *  admin_label = @Translation("Hello session count")
 * )
 */
class SessionCountBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var Connection
   */
  protected $database;

  /**
   * {@inheritdoc}.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              Connection $database) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->database = $database;
  }

  /**
   * {@inheritdoc}.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}.
   */
  public function build() {
    $number = $this->database->select('sessions')
      ->countQuery()
      ->execute()
      ->fetchField();

    return [
      '#markup'  => $this->t('Session number: %number', ['%number' => $number]),
      '#cache' => [
        'keys' => ['hello:sessions'],
        'max-age' => '60',
      ],
    ];
  }

  /**
   * {@inheritdoc}.
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access hello');
  }

}
