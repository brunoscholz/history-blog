<?php

namespace Drupal\accounting\Form;

use Drupal\accounting\Entity\Account;
use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityInterface;
// use Drupal\Core\Config\ConfigFactoryInterface;
// use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class custom MagiclineAPISettingsForm form for custom settings.
 *
 * @package Drupal\accounting\Form
 */
class AccountForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'accounting_account_add_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $account = NULL) {
    /** @var \Drupal\accounting\Entity\Account $account */
    $account = \Drupal::entityTypeManager()->getStorage('accounting_account')->load($account);

    $config = \Drupal::service('config.factory')->get('accounting.settings');
    $categories =  explode(PHP_EOL, trim($config->get('account_groups') ?? ''));
    $categories = array_map(fn($item) => preg_replace('/\r/', '', (string) $item), $categories);
    $categories = array_combine($categories, $categories);

    $is_edit = !is_null($account);

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Account Name'),
      '#description' => $this->t(
        'Account name'
      ),
      '#default_value' => $is_edit ? $account->get('name') : '',
    ];

    $form['group'] = [
      '#type' => 'select',
      '#title' => $this->t('Group'),
      '#multiple' => FALSE,
      '#options' => $categories,
      '#description' => $this->t(
        'Account type/group'
      ),
      '#default_value' => $is_edit ? $account->get('group') : '',
    ];

    $form['actions'] = [
      '#type' => 'actions',
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

      // Demonstration for how to load a full user object of the current user.
      // This $full_user variable is not needed for this code,
      // but is shown for demonstration purposes.
      $full_user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());

      // Obtain values as entered into the Form.
      $nid = $form_state->getValue('nid');
      $name = $form_state->getValue('name');
      $group = $form_state->getValue('group');

      // Start to build a query builder object $query.
      // https://www.drupal.org/docs/8/api/database-api/insert-queries
      $query = \Drupal::database()->insert('accounting_account');

      // Specify the fields that the query will insert into.
      $query->fields([
        'uid',
        'name',
        'group',
        'balance',
      ]);

      // Set the values of the fields we selected.
      // Note that they must be in the same order as we defined them
      // in the $query->fields([...]) above.
      $query->values([
        $uid,
        $name,
        $group,
        0
      ]);

      // Execute the query!
      // Drupal handles the exact syntax of the query automatically!
      $query->execute();

      \Drupal::messenger()->addMessage(
        t('Account created!')
      );

      $form_state->cleanValues();
    }
    catch (\Exception $e) {
      \Drupal::messenger()->addError(
        t('Unable to save account at this time due to database error. Please try again.')
      );
    }
  }

}
