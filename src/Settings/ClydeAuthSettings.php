<?php

namespace Drupal\social_auth_clyde\Settings;

use Drupal\social_auth\Settings\SettingsBase;

/**
 * Defines methods to get Social Auth Clyde settings.
 */
class ClydeAuthSettings extends SettingsBase implements ClydeAuthSettingsInterface {

  /**
   * Restricted domain.
   *
   * @var string|null
   */
  protected ?string $restrictedDomain = NULL;

  /**
   * {@inheritdoc}
   */
  public function getRestrictedDomain(): ?string {
    if (!$this->restrictedDomain) {
      $this->restrictedDomain = $this->config->get('restricted_domain');
    }
    return $this->restrictedDomain;
  }

}
