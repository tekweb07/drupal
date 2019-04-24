<?php

namespace Drupal\hello\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UserStatisticsController extends ControllerBase {

  /**
   * @var Connection
   */
  protected $database;

  /**
   * @var DateFormatter
   */
  protected $dateFormatter;

  /**
   * UserStatisticsController constructor.
   * @param Connection $database
   * @param DateFormatter $dateFormatter
   */
  public function __construct(Connection $database, DateFormatterInterface $dateFormatter) {
    $this->database = $database;
    $this->dateFormatter = $dateFormatter;
  }

  /**
   * {@inheritdoc}.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('date.formatter')
    );
  }

  /**
   * @param \Drupal\user\UserInterface $user
   * @return array
   */
  public function content(UserInterface $user) {
    /** @var \Drupal\Core\Database\Query\SelectInterface $query */
    $query = $this->database->select('hello_user_statistics', 'hus')
      ->fields('hus', ['action', 'time'])
      ->condition('uid', $user->id())
      ->orderBy('time', 'ASC');
    $result = $query->execute();

    // Tableau des statistics.
    $rows = [];
    $connexions = 0;
    foreach ($result as $record) {
      $rows[] = [
        $record->action == '1' ? $this->t('Login') : $this->t('Logout'),
        $this->dateFormatter->format($record->time),
      ];
      $connexions += $record->action;
    }
    $table = [
      '#type' => 'table',
      '#header' => [$this->t('Action'), $this->t('Time')],
      '#rows' => $rows,
      '#empty' => $this->t('No connections yet.'),
    ];

    // Message en en-tête.
    $message = [
      '#theme' => 'hello_user_connexion',
      '#user' => $user,
      '#count' => $connexions,
    ];

    // On renvoie les render arrays.
    return [
      'message' => $message,
      'table' => $table,
      '#cache' => [
        'max-age' => '0',
      ],
    ];
  }

}
