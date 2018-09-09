<?php

namespace Drupal\Tests\cache_control_override\Functional;

use Drupal\cache_control_override_test\Controller\CacheControl;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the cache control override.
 *
 * @group cache_control_override
 */
class CacheControlOverrideMaxAgeTest extends BrowserTestBase {

  /**
   * The max age set by Drupal when page caching is enabled.
   */
  const DEFAULT_MAX_AGE = 1800;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'cache_control_override',
    'cache_control_override_test',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $config = $this->config('system.performance');
    $config->set('cache.page.max_age', static::DEFAULT_MAX_AGE);
    $config->save();
  }

  /**
   * Test the cache properties in response header data.
   */
  public function testMaxAge() {
    // Max age not set.
    $this->drupalGet('cco');
    $this->assertSession()->pageTextContains(CacheControl::RESPONSE);
    $this->assertSession()->responseHeaderContains('Cache-Control', 'max-age=' . static::DEFAULT_MAX_AGE . ', public');

    // Permanent.
    $this->drupalGet('cco/-1');
    $this->assertSession()->pageTextContains(CacheControl::RESPONSE);
    $this->assertSession()->responseHeaderContains('Cache-Control', 'max-age=' . static::DEFAULT_MAX_AGE . ', public');

    // Max age set.
    $this->drupalGet('cco/333');
    $this->assertSession()->pageTextContains(CacheControl::RESPONSE);
    $this->assertSession()->responseHeaderContains('Cache-Control', 'max-age=333, public');

    // Uncacheable.
    $this->drupalGet('cco/0');
    $this->assertSession()->pageTextContains(CacheControl::RESPONSE);
    $this->assertSession()->responseHeaderContains('Cache-Control', 'max-age=0, public');
  }

  /**
   * Test the max age is coerced to minimum age.
   */
  public function testMaxAgeMinimum() {
    $assertMinimum = 100;
    $this->config('cache_control_override.settings')
      ->set('max_age.minimum', $assertMinimum)
      ->save();

    // Max-age must not be changed if not over minimum.
    $this->drupalGet('cco/150');
    $this->assertSession()->pageTextContains(CacheControl::RESPONSE);
    $this->assertSession()->responseHeaderContains('Cache-Control', 'max-age=150, public');

    // Max-age must be changed if under minimum.
    $this->drupalGet('cco/50');
    $this->assertSession()->pageTextContains(CacheControl::RESPONSE);
    $this->assertSession()->responseHeaderContains('Cache-Control', 'max-age=' . $assertMinimum . ', public');

    // Permanent or uncacheable must not be coerced.
    $this->drupalGet('cco/-1');
    $this->assertSession()->pageTextContains(CacheControl::RESPONSE);
    $this->assertSession()->responseHeaderContains('Cache-Control', 'max-age=' . static::DEFAULT_MAX_AGE . ', public');
    $this->drupalGet('cco/0');
    $this->assertSession()->pageTextContains(CacheControl::RESPONSE);
    $this->assertSession()->responseHeaderContains('Cache-Control', 'max-age=0, public');
  }

  /**
   * Test the max age is coerced to maximum age.
   */
  public function testMaxAgeMaximum() {
    $assertMaximum = 100;
    $this->config('cache_control_override.settings')
      ->set('max_age.maximum', $assertMaximum)
      ->save();

    // Max-age must not be changed if not under maximum.
    $this->drupalGet('cco/50');
    $this->assertSession()->pageTextContains(CacheControl::RESPONSE);
    $this->assertSession()->responseHeaderContains('Cache-Control', 'max-age=50, public');

    // Max-age must be changed if over maximum.
    $this->drupalGet('cco/150');
    $this->assertSession()->pageTextContains(CacheControl::RESPONSE);
    $this->assertSession()->responseHeaderContains('Cache-Control', 'max-age=' . $assertMaximum . ', public');

    // Permanent or uncacheable must not be coerced.
    $this->drupalGet('cco/-1');
    $this->assertSession()->pageTextContains(CacheControl::RESPONSE);
    $this->assertSession()->responseHeaderContains('Cache-Control', 'max-age=' . static::DEFAULT_MAX_AGE . ', public');
    $this->drupalGet('cco/0');
    $this->assertSession()->pageTextContains(CacheControl::RESPONSE);
    $this->assertSession()->responseHeaderContains('Cache-Control', 'max-age=0, public');
  }

}
