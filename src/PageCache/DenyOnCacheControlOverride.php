<?php

namespace Drupal\cache_control_override\PageCache;

use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\PageCache\ResponsePolicyInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cache policy for cache control overrides .
 *
 * This policy rule denies caching of responses having max-age equal to 0 if
 * cacheability metadata cache control override is enabled.
 */
class DenyOnCacheControlOverride implements ResponsePolicyInterface {

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public function check(Response $response, Request $request) {
    if (!$response instanceof CacheableResponseInterface) {
      return NULL;
    }

    if ($this->configFactory->get('cache_control_override.settings')->get('use_cacheability_metadata')) {
      $max_age = $response->getCacheableMetadata()->getCacheMaxAge();
      if ($max_age == 0) {
        // @TODO: This will affect users using Internal Page Cache as well, find a way to document that.
        return static::DENY;
      }
    }
  }

}
