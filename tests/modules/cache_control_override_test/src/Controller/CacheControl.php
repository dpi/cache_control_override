<?php

namespace Drupal\cache_control_override_test\Controller;

/**
 * Controllers for testing the cache control override.
 */
class CacheControl {

  /**
   * Content for testing.
   */
  const RESPONSE = 'Max age test content';

  /**
   * Controller callback: Test content with a specified max age.
   *
   * @param int|null $max_age
   *   Max age value to be used in the response.
   *
   * @return array
   *   Render array of page output.
   */
  public function maxAge($max_age = NULL) {
    $build = ['#plain_text' => static::RESPONSE];
    if (isset($max_age)) {
      $build['#cache']['max-age'] = $max_age;
    }
    return $build;
  }

}
