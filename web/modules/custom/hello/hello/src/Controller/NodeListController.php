<?php

namespace Drupal\hello\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

class NodeListController extends ControllerBase {

  /**
   * @param string $nodetype
   * @return array
   */
  public function content($nodetype = NULL) {
    // Liste des types de contenu.
    $node_types = $this->entityTypeManager()->getStorage('node_type')->loadMultiple();
    $type_items = [];
    $config_types = [];
    foreach ($node_types as $node_type) {
      $route = new Url('hello.node_list', ['nodetype' => $node_type->id()]);
      $type_items[] = new Link($node_type->label(), $route);
      $config_types[] = 'config:node.type.' . $node_type->id();
    }
    $type_list =  [
      '#theme' => 'item_list',
      '#items' => $type_items,
      '#title' => $this->t('Select a content type'),
    ];

    // Liste des noeuds.
    $node_storage = $this->entityTypeManager()->getStorage('node');
    $query = $node_storage->getQuery();
    if ($nodetype) {
      $query->condition('type', $nodetype, '=');
    }
    $nids = $query->pager(10)->execute();
    $nodes = $node_storage->loadMultiple($nids);

    $items = [];
    foreach ($nodes as $node) {
      $items[] = $node->toLink();
    }
    // Render array pour la pagination.
    $pager = ['#type' => 'pager'];
    // Render array pour la liste à puced.
    $list =  [
      '#theme' => 'item_list',
      '#items' => $items,
      '#title' => $this->t('Nodes'),
    ];
    return [
      'type_list' => $type_list,
      'list' => $list,
      'pager' => $pager,
      '#cache' => [
        'keys' => ['hello:node_list'],
        'contexts' => ['url'],
        'tags' => array_merge($config_types, ['node_list']),
      ],
    ];
  }

}
