<?php

namespace Drupal\accounting\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class custom MagiclineAPISettingsForm form for custom settings.
 *
 * @package Drupal\accounting\Form
 */
class AccountForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'accounting_account_add_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['accounting.settings'];
  }

  /**
   * Symfony container class.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * Drupal config factory interface.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * CustomSettingsForm constructor.
   */
  final public function __construct(
    ConfigFactoryInterface $configFactory,
    ContainerInterface $container
  ) {
    $this->configFactory = $configFactory;
    $this->container = $container;
    parent::__construct($configFactory);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $configFactory */
    $configFactory = $container->get('config.factory');
    return new static(
      $configFactory,
      $container
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->configFactory->get('accounting.settings');

    $form['openapi_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Base URL of the Open API'),
      '#default_value' => $config->get('openapi_url'),
      '#description' => $this->t(
        'This will set the Magicline Open API page url available in accounting.settings.'
      ),
    ];

    $form['connectapi_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Base URL of the Connect API'),
      '#default_value' => $config->get('connectapi_url'),
      '#description' => $this->t(
        'This will set the Magicline Connect API page url available in accounting.settings.'
      ),
    ];

    $form['errors'] = [
      '#type' => 'details',
      '#title' => $this->t('Custom Error Messages for Join Online'),
    ];

    $form['errors']['error_user_has_contract'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Error Message for User already has a contract'),
      '#default_value' => $this->getDefaultValue('error_user_has_contract', $config),
    ];

    $form['errors']['error_iban_invalid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Error Message for invalid IBAN'),
      '#default_value' => $this->getDefaultValue('error_iban_invalid', $config),
    ];

    $form['errors']['error_voucher_invalid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Error Message for invalid Voucher'),
      '#default_value' => $this->getDefaultValue('error_voucher_invalid', $config),
    ];

    $form['other_errors'] = [
      '#type' => 'details',
      '#title' => $this->t('Other Custom Error Messages'),
    ];

    $form['other_errors']['error_trial_slot_booked'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Error Message for Slot already in use in Trial Training'),
      '#default_value' => $this->getDefaultValue('error_trial_slot_booked', $config),
    ];

    $form['other_errors']['error_trial_session_booked'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Error Message for Session already booked in Trial Training'),
      '#default_value' => $this->getDefaultValue('error_trial_session_booked', $config),
    ];

    $form['other_errors']['error_trial_minimum_age'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Error Message for Minimum age in Trial Training'),
      '#default_value' => $this->getDefaultValue('error_trial_minimum_age', $config),
    ];

    $form['other_errors']['error_minimum_age_general'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Error Message for Minimum age general'),
      '#default_value' => $this->getDefaultValue('error_minimum_age_general', $config),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Default values mapping.
   *
   * @var array
   */
  protected $defaultValuesMap = [
    'error_user_has_contract' => [
      'type' => 'string',
      'value' => '',
    ],
    'error_iban_invalid' => [
      'type' => 'string',
      'value' => '',
    ],
    'error_voucher_invalid' => [
      'type' => 'string',
      'value' => '',
    ],
    'error_trial_slot_booked' => [
      'type' => 'string',
      'value' => '',
    ],
    'error_trial_session_booked' => [
      'type' => 'string',
      'value' => '',
    ],
    'error_trial_minimum_age' => [
      'type' => 'string',
      'value' => '',
    ],

    'error_minimum_age_general' => [
      'type' => 'string',
      'value' => 'Du musst mind. 18 Jahre alt sein, um einen Vertrag abschließen zu können',
    ],
  ];

  /**
   * Get default progress' steps headlines.
   *
   * @param string $element
   *   The name of the field/setting.
   * @param \Drupal\Core\Config\ImmutableConfig $config
   *   Config object.
   *
   * @return mixed
   *   Return the type of the map.
   */
  public function getDefaultValue($element, ImmutableConfig &$config) {
    $default_value = $config->get($element);
    $map = $this->defaultValuesMap[$element];

    if (empty($default_value)) {
      $default_value = $map['value'];
    }

    switch ($map['type']) {
      case 'int':
        $default_value = (int) $default_value;
        break;

      case 'html':
        $default_value = htmlspecialchars_decode((string) $default_value);
        break;

      default:
        break;
    }

    return $default_value;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('accounting.settings');
    $form_state->cleanValues();

    foreach ($form_state->getValues() as $key => $value) {
      $config->set($key, Html::escape($value));
    }
    $config->save();
  }

}
