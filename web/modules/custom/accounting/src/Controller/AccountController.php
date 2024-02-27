<?php

namespace Drupal\accounting\Controller;

use Drupal\accounting\Form\AccountForm;
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
 * Provides the account page.
 */
class AccountController extends ControllerBase {

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
   * Gets and returns all accounts.
   * These are returned as an associative array, with each row.
   *
   * @return array|null
   */
  protected function load() {
    try {

      // https://www.drupal.org/docs/8/api/database-api/dynamic-
      //         queries/introduction-to-dynamic-queries
      $database = \Drupal::database();
      $select_query = $database->select('accounting_account', 'a');

      // Join the user table, so we can get the entry creator's username.
      $select_query->join('users_field_data', 'u', 'a.uid = u.uid');

      // Join the node table, so we can get the event's name.
      $select_query->join('node_field_data', 'n', 'a.nid = n.nid');

      // Select these specific fields for the output.
      $select_query->addField('u', 'name', 'username');
      $select_query->addField('n', 'title');
      $select_query->addField('a', 'name');
      $select_query->addField('a', 'group');
      $select_query->addField('a', 'balance');

      // Note that fetchAll() and fetchAllAssoc() will, by default, fetch using
      // whatever fetch mode was set on the query
      // (i.e. numeric array, associative array, or object).
      // Fetches can be modified by passing in a new fetch mode constant.
      // For fetchAll(), it is the first parameter.
      // https://www.drupal.org/docs/8/api/database-api/result-sets
      // https://www.php.net/manual/en/pdostatement.fetch.php
      $entries = $select_query->execute()->fetchAll(\PDO::FETCH_ASSOC);

      // Return the associative array of RSVPList entries.
      return $entries;
    }
    catch (\Exception $e) {
      // Display a user-friendly error.
      \Drupal::messenger()->addStatus(
        t('Unable to access the database at this time. Please try again later.')
      );
      return NULL;
    }
  }

  /**
   * Outputs the accounts list.
   *
   * @return array
   *   A render array.
   */
  public function accountsPage() {
    $build = [
      '#attached' => [
        'library' => ['accounting/dashboard'],
      ],
      '#prefix' => '<div class="accounting-accounts">',
      '#sufix' => '</div>',
    ];

    $key = Html::cleanCssIdentifier('accounting.accounts_add', [
      '.' => '-',
      '_' => '-',
    ]);
    $links[$key] = [
      'title' => 'Add Account',
      'description' => '',
      'url' => Url::fromRoute('accounting.accounts_add'),
      'weight' => 0,
    ];

    $accounts = $this->load();
    $build['accounts_list'] = [
      '#theme' => 'accounting_dashboard_accounts_list',
      '#accounts' => $accounts,
      '#links' => $links,
      '#weight' => 0,
    ];

    // $build['account_form'] = [
    //   '#theme' => 'accounting_dashboard_accounts_form',
    //   '#form' => $form,
    // ];

    // $this->moduleHandler->alter('accounting_dashboard_page_build', $build);

    return $build;
  }

  public function addAccount() {
    // Create the form object.
    $form = $this->formBuilder()->getForm(AccountForm::class);
    // $build['accounts_form'] = [
    //   '#theme' => 'accounting_dashboard_accounts_form',
    //   '#form' => $form,
    //   '#weight' => 0,
    // ];

    return $form;
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
