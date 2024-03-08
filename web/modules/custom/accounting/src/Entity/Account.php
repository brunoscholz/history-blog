<?php

namespace Drupal\accounting\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Defines the accounting_account entity.
 *
 * @ContentEntityType(
 *   id = "accounting_account",
 *   label = @Translation("Account", context = "Accounting"),
 *   label_collection = @Translation("Accounts", context = "Accounting"),
 *   label_singular = @Translation("account", context = "Accounting"),
 *   label_plural = @Translation("accounts", context = "Accounting"),
 *   label_count = @PluralTranslation(
 *     singular = "@count account",
 *     plural = "@count accounts",
 *     context = "Accounting",
 *   ),
 *   bundle_label = @Translation("Account type", context = "Accounting"),
 *   base_table = "accounting_account",
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\accounting\Form\AccountForm",
 *       "add" = "Drupal\accounting\Form\AccountForm",
 *       "edit" = "Drupal\accounting\Form\AccountForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   field_indexes = {
 *     "name",
 *     "group"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "uid" = "uid",
 *     "name" = "name",
 *     "group" = "group",
 *     "balance" = "balance",
 *   },
 *   links = {
 *     "canonical" = "/admin/accounting/accounts/{accounting_account}",
 *     "edit-form" = "/admin/accounting/accounts/{accounting_account}/edit",
 *     "add-form" = "/admin/accounting/accounts/{accounting_account}/add",
 *     "delete-form" = "/admin/accounting/accounts/{accounting_account}/delete",
 *     "delete-multiple-form" = "/admin/accounting/accounts/delete",
 *     "collection" = "/admin/accounting/accounts",
 *   },
 *   admin_permission = "administer accounting_account",
 *   field_ui_base_route = "entity.accounting_account.settings",
 *   allow_number_patterns = TRUE,
 *   log_version_mismatch = TRUE,
 *   fieldable = TRUE,
 * )
 */

class Account extends ContentEntityBase implements ContentEntityInterface {

  /**
   * {@inheritdoc}
   */
  public function getGroup() {
    return $this->get('account_group')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setGroup($group) {
    $this->set('account_group', $group);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('account_name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('account_name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getBalance() {
    return $this->get('balance')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setBalance($amount) {
    $this->set('balance', $amount);
    return $this;
  }

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the MyEntity entity.'))
      ->setReadOnly(TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User ID'))
      ->setDescription(t('The user ID of the entity author.'))
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback('Drupal\my_module\Entity\MyEntity::getCurrentUserId')
      ->setReadOnly(TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the entity.'))
      ->setDefaultValue('')
      ->setRequired(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['group'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Group'))
      ->setDescription(t('The group of the entity.'))
      ->setDefaultValue('')
      ->setRequired(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['balance'] = BaseFieldDefinition::create('decimal')
      ->setLabel(t('Balance'))
      ->setDescription(t('The balance of the entity.'))
      ->setSettings([
        'precision' => 10,
        'scale' => 2,
      ])
      ->setDefaultValue('0.00')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'number',
        'settings' => [
          'placeholder' => '',
        ],
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

  /**
   * Gets the current user's ID.
   */
  public static function getCurrentUserId() {
    $user = \Drupal::currentUser();
    return $user->id();
  }
}
