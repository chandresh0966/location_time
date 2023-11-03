<?php

namespace Drupal\location_time\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\location_time\Services\TimezoneUtility;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Location Time' Block.
 *
 * @Block(
 *   id = "location_time_block",
 *   admin_label = @Translation("Location Time"),
 *   category = @Translation("Custom"),
 * )
 */
class LocationTime extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The timezone utility service.
   *
   * @var \Drupal\location_time\Services\TimezoneUtility
   */
  protected $timezoneUtility;

  /**
   * {@inheritdoc}
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\location_time\Services\TimezoneUtility $timezone_utility
   *   The timezone utility service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, TimezoneUtility $timezone_utility) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->configFactory = $config_factory;
    $this->timezoneUtility = $timezone_utility;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('location_time.timezone')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $locationTimeConfigs = $this->configFactory->get('location_time.adminsettings');

    // Get timezone value.
    $configured_timezone = $locationTimeConfigs->get('timezone') ?? 'America/New_York';

    // Get current UTC datetime.
    $gmdate = gmdate('Y-m-d\TH:i:s');

    // Convert current datetime to desired timezone and format.
    $current_date = $this->timezoneUtility->convertDateToDesiredTimezoneAndFormat($gmdate, 'l, d F Y', 'UTC', $configured_timezone);

    $build = [
      '#theme' => 'location_time',
      '#country' => $locationTimeConfigs->get('country') ?? '',
      '#city' => $locationTimeConfigs->get('city') ?? '',
      '#timezone' => $configured_timezone,
      '#current_date' => $current_date,
      '#attached' => [
        'library' => ['location_time/location-time'],
      ],
    ];

    $build['#current_time'] = [
      '#lazy_builder' => [
        'location_time.timezone:convertDateToDesiredTimezoneAndFormat',
        [
          $gmdate,
          'h:i a',
          'UTC',
          $configured_timezone,
        ],
      ],
    ];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    // Set config cache tag so when config will update
    // our block cache will be rebuild.
    return Cache::mergeTags(parent::getCacheTags(), ['config:location_time.adminsettings']);
  }

}
