<?php

namespace Drupal\cache_control_override\EventSubscriber;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Cache Control Override.
 */
class CacheControlOverrideSubscriber implements EventSubscriberInterface {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new CacheControlOverrideSubscriber.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * Overrides cache control header if any of override methods are enabled.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The event to process.
   */
  public function onRespond(FilterResponseEvent $event) {
    if (!$event->isMasterRequest()) {
      return;
    }

    $response = $event->getResponse();

    // If the current response isn't an implementation of the
    // CacheableResponseInterface, then there is nothing we can override.
    if (!$response instanceof CacheableResponseInterface) {
      return;
    }

    // If FinishResponseSubscriber didn't set the response as cacheable, then
    // don't override anything.
    if (!$response->headers->hasCacheControlDirective('max-age') || !$response->headers->hasCacheControlDirective('public')) {
      return;
    }

    $max_age = $response->getCacheableMetadata()->getCacheMaxAge();

    // We treat permanent cache max-age as default therefore we don't override
    // the max-age.
    if ($max_age != Cache::PERMANENT) {
      // If max-age is not uncacheable (0), check if max-age should be changed.
      if ($max_age > 0) {
        // Force minimum max-age if configured.
        $minimum = $this->getMaxAgeMinimum();
        if (isset($minimum)) {
          $max_age = max($minimum, $max_age);
        }

        // Force maximum max-age if configured.
        $maximum = $this->getMaxAgeMaximum();
        if (isset($maximum)) {
          $max_age = min($maximum, $max_age);
        }
      }
      $response->headers->set('Cache-Control', 'public, max-age=' . $max_age);
    }
  }

  /**
   * Get the minimum max-age.
   *
   * @return int|null
   *   The minimum max-age, or null if no minimum.
   */
  protected function getMaxAgeMinimum() {
    return $this->configFactory->get('cache_control_override.settings')->get('max_age.minimum');
  }

  /**
   * Get the maximum max-age.
   *
   * @return int|null
   *   The maximum max-age, or null if no maximum.
   */
  protected function getMaxAgeMaximum() {
    return $this->configFactory->get('cache_control_override.settings')->get('max_age.maximum');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['onRespond'];
    return $events;
  }

}
