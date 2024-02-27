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
class AccountingSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'accounting_account_config_form';
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

    $form['currency'] = [
      '#type' => 'select',
      '#title' => $this->t('Currency'),
      '#multiple' => FALSE,
      '#options' => [
        '' => $this->t('- none -'),
        'Real' => $this->t('BRL'),
        'Dollar' => $this->t('USD'),
        'Bitcoin' => $this->t('BTC'),
      ],
      '#description' => $this->t('Which type to get from configs.'),
    ];

    $form['accounts'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Accounts Settings'),
    ];

    $form['accounts']['account_groups'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Accounts Groups'),
      '#default_value' => $this->getDefaultValue('account_groups', $config),
      '#description' => $this->t(
        'This provides a list of the possible groups for accounts in accounting.settings.'
      ),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Default values mapping.
   *
   * @var array
   */
  protected $defaultValuesMap = [
    'account_groups' => [
      'type' => 'string',
      'value' => "Assets\nLiabilities\nEquity\nRevenue\nExpenses\nOther",
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
