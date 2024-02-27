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
 *   label = @Translation("Account"),
 *   base_table = "accounting_account",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *   },
 *   handlers = {
 *     "form" = {
 *       "default" = "Drupal\Core\Entity\ContentEntityForm",
 *       "add" = "Drupal\Core\Entity\ContentEntityForm",
 *       "edit" = "Drupal\Core\Entity\ContentEntityForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   links = {
 *     "canonical" = "/accounting_account/{accounting_account}",
 *     "add-page" = "/accounting_account/add",
 *     "add-form" = "/accounting_account/add/{accounting_account_type}",
 *     "edit-form" = "/accounting_account/{accounting_account}/edit",
 *     "delete-form" = "/accounting_account/{accounting_account}/delete",
 *     "collection" = "/admin/content/accounting_accounts",
 *   },
 *   admin_permission = "administer site configuration",
 * )
 */

// *   field_ui_base_route = "entity.accounting_account_type.edit_form",
class Account extends ContentEntityBase implements ContentEntityInterface {

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    // Standard field, used as unique if primary index.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the accounting_account entity.'))
      ->setReadOnly(TRUE);

    // Standard field, unique outside of the scope of the current project.
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the accounting_account entity.'))
      ->setReadOnly(TRUE);

    return $fields;
  }
}
