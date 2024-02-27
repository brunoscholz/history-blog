<?php

namespace Drupal\accounting\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides the store dashboard page.
 */
class DashboardController extends ControllerBase {

  /**
   * The menu tree.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected MenuLinkTreeInterface $menuTree;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->menuTree = $container->get('menu.link_tree');
    $instance->moduleHandler = $container->get('module_handler');
    return $instance;
  }

  /**
   * Outputs the store dashboard.
   *
   * @return array
   *   A render array.
   */
  public function dashboardPage() {
    $build = [
      '#attached' => [
        'library' => ['accounting/dashboard'],
      ],
      '#prefix' => '<div class="accounting-dashboard">',
      '#sufix' => '</div>',
    ];

    if ($links = $this->getManagementLinks()) {
      $build['management_links'] = [
        '#theme' => 'accounting_dashboard_management_links',
        '#links' => $links,
        '#weight' => 0,
      ];
    }
    $this->moduleHandler->alter('accounting_dashboard_page_build', $build);

    return $build;
  }

  /**
   * Gets the first level of management links from the accounting admin menu.
   *
   * @return array
   *   The associative array.
   *
   * @see \Drupal\System\SystemManager::getAdminBlock()
   */
  protected function getManagementLinks() {
    $links = [];

    // Only get first level children of the accounting administration link.
    $params = new MenuTreeParameters();
    $params->setRoot('accounting.admin_accounting')
      ->excludeRoot()
      ->setTopLevelOnly()
      ->onlyEnabledLinks();
    $tree = $this->menuTree->load('admin', $params);
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $this->menuTree->transform($tree, $manipulators);
    foreach ($tree as $key => $element) {
      // Only render accessible links.
      if (!$element->access->isAllowed()) {
        continue;
      }

      /** @var \Drupal\Core\Menu\MenuLinkInterface $link */
      $link = $element->link;
      $key = Html::cleanCssIdentifier($link->getRouteName(), [
        '.' => '-',
        '_' => '-',
      ]);
      $links[$key] = [
        'title' => $link->getTitle(),
        'description' => $link->getDescription(),
        'url' => Url::fromRoute($link->getRouteName()),
        'weight' => $link->getWeight(),
      ];
    }

    return $links;
  }

}
