<?php

namespace Drupal\accounting\Controller;

use Drupal\accounting\Form\TransactionForm;
use Drupal\Component\Utility\Html;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the transaction page.
 */
class TransactionController extends ControllerBase {

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
   * Gets and returns all transactions.
   * These are returned as an associative array, with each row.
   *
   * @return array|null
   */
  protected function load() {
    try {

      // https://www.drupal.org/docs/8/api/database-api/dynamic-
      //         queries/introduction-to-dynamic-queries
      $database = \Drupal::database();
      $select_query = $database->select('accounting_transaction', 'a');

      // Join the user table, so we can get the entry creator's username.
      $select_query->join('users_field_data', 'u', 'a.uid = u.uid');

      // Join the node table, so we can get the event's name.
      // $select_query->join('node_field_data', 'n', 'a.nid = n.nid');

      // Select these specific fields for the output.
      $select_query->addField('u', 'name', 'username');
      // $select_query->addField('n', 'title');
      $select_query->addField('a', 'date');
      $select_query->addField('a', 'from');
      $select_query->addField('a', 'to');
      $select_query->addField('a', 'amount');
      $select_query->addField('a', 'part_from');
      $select_query->addField('a', 'part_to');
      $select_query->addField('a', 'note');

      // Note that fetchAll() and fetchAllAssoc() will, by default, fetch using
      // whatever fetch mode was set on the query
      // (i.e. numeric array, associative array, or object).
      // Fetches can be modified by passing in a new fetch mode constant.
      // For fetchAll(), it is the first parameter.
      // https://www.drupal.org/docs/8/api/database-api/result-sets
      // https://www.php.net/manual/en/pdostatement.fetch.php
      $entries = $select_query->execute()->fetchAll(\PDO::FETCH_ASSOC);

      // Return the associative array of transaction entries.
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
   * Outputs the transactions list.
   *
   * @return array
   *   A render array.
   */
  public function transactionsPage() {
    $build = [
      '#attached' => [
        'library' => ['accounting/dashboard'],
      ],
      '#prefix' => '<div class="accounting-transactions">',
      '#sufix' => '</div>',
    ];

    $key = Html::cleanCssIdentifier('accounting.transactions_add', [
      '.' => '-',
      '_' => '-',
    ]);
    $links[$key] = [
      'title' => 'Add Transaction',
      'description' => '',
      'url' => Url::fromRoute('accounting.transactions_add'),
      'weight' => 0,
    ];

    $transactions = $this->load();
    $build['transactions_list'] = [
      '#theme' => 'accounting_dashboard_transactions_list',
      '#transactions' => $transactions,
      '#links' => $links,
      '#weight' => 0,
    ];

    return $build;
  }

  public function addTransaction() {
    // Create the form object.
    $form = $this->formBuilder()->getForm(TransactionForm::class);
    return $form;
  }

}
