<?php

namespace Drupal\social_auth_clyde\Settings;

use Drupal\social_auth\Settings\SettingsInterface;

/**
 * Defines an interface for Social Auth Clyde settings.
 */
interface ClydeAuthSettingsInterface extends SettingsInterface {
  /**
   * Gets the restricted domain.
   *
   * @return string|null
   *   The restricted domain.
   */
  public function getRestrictedDomain(): ?string;
}
