<?php

namespace Drupal\location_time\Services;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Security\TrustedCallbackInterface;

/**
 * Service for timezone related operations.
 */
class TimezoneUtility implements TrustedCallbackInterface {

  /**
   * Convert the date to a specified timezone and format.
   *
   * @param string $date
   *   A date/input_time_adjusted string.
   * @param string $format
   *   The format of the output date.
   * @param string $input_timezone
   *   The timezone the date is right now.
   * @param string $output_timezone
   *   The timezone the date should be converted to.
   *
   * @return string
   *   The date in the desired timezone and format.
   */
  public static function convertDateToDesiredTimezoneAndFormat($date, $format, $input_timezone, $output_timezone) {
    if (!$date) {
      return NULL;
    }

    $new_date = new DrupalDateTime($date, $input_timezone);
    $new_date->setTimezone(new \DateTimeZone($output_timezone));

    return [
      '#markup' => $new_date->format($format),
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return [
      'convertDateToDesiredTimezoneAndFormat',
    ];
  }

}
