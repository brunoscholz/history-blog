<?php

namespace Drupal\accounting\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class custom MagiclineAPISettingsForm form for custom settings.
 *
 * @package Drupal\accounting\Form
 */
class TransactionForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'accounting_transaction_add_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $database = \Drupal::database();
    $select_query = $database->select('accounting_account', 'a');
    // Join the user table, so we can get the entry creator's username.
    $select_query->join('users_field_data', 'u', 'a.uid = u.uid');
    // Select these specific fields for the output.
    $select_query->addField('u', 'name', 'username');
    $select_query->addField('a', 'name');
    $select_query->addField('a', 'group');
    $select_query->addField('a', 'balance');

    $entries = $select_query->execute()->fetchAll(\PDO::FETCH_ASSOC);
    $accounts = [];
    foreach ($entries as $entry) {
      $accounts[$entry['group']][] = $entry['name'];
    }
    // $accounts = array_map(fn($item) => $item['name'], $entries);

    $form['amount'] = [
      '#type' => 'number',
      '#title' => $this->t('Amount'),
      '#description' => $this->t(
        'Transaction amount'
      ),
    ];

    $form['from'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Account FROM'),
      '#description' => $this->t(
        'From witch account'
      ),
      '#attributes' => [
        'class' => ['account-autocomplete'],
      ],
    ];

    $form['to'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Account TO'),
      '#description' => $this->t(
        'To witch account'
      ),
      '#attributes' => [
        'class' => ['account-autocomplete'],
      ],
    ];

    $form['note'] = [
      '#type' => 'textarea',
      '#maxlength' => 255,
      '#title' => $this->t('Note'),
      '#description' => $this->t(
        'Transaction note. Max: 255 chars.'
      ),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['#attached'] = [
      'library' => [
        'accounting/autocomplete',
      ],
      'drupalSettings' => [
        'accounts' => [
          'list' => $accounts,
        ],
      ],
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    try {
      // Get current user ID.
      $uid = \Drupal::currentUser()->id();

      $date = \Drupal::time()->getRequestTime();
      $from = $form_state->getValue('from');
      $to = $form_state->getValue('to');
      $amount = $form_state->getValue('amount');
      $note = $form_state->getValue('note');

      // Start to build a query builder object $query.
      // https://www.drupal.org/docs/8/api/database-api/insert-queries
      $query = \Drupal::database()->insert('accounting_transaction');

      // Specify the fields that the query will insert into.
      $query->fields([
        'uid',
        'date',
        'from',
        'to',
        'amount',
        'note',
      ]);

      // Set the values of the fields we selected.
      // Note that they must be in the same order as we defined them
      // in the $query->fields([...]) above.
      $query->values([
        $uid,
        $date,
        $from,
        $to,
        $amount,
        $note,
      ]);

      // Execute the query!
      // Drupal handles the exact syntax of the query automatically!
      $query->execute();

      \Drupal::messenger()->addMessage(
        t('Transaction created!')
      );

      $form_state->cleanValues();
    }
    catch (\Exception $e) {
      \Drupal::messenger()->addError(
        t('Unable to save transaction at this time due to database error. Please try again.')
      );
    }
  }

}
