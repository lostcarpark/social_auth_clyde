<?php

namespace Drupal\social_auth_clyde;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\social_auth\AuthManager\OAuth2Manager;
use Drupal\social_auth\User\SocialAuthUser;
use Drupal\social_auth\User\SocialAuthUserInterface;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Contains all the logic for Clyde OAuth2 authentication.
 */
class ClydeAuthManager extends OAuth2Manager {

  /**
   * ClydeAuthManager constructor.
   */
  public function __construct(ConfigFactory $configFactory,
                              LoggerChannelFactoryInterface $logger_factory,
                              RequestStack $request_stack) {

    parent::__construct($configFactory->get('social_auth_clyde.settings'),
                        $logger_factory,
                        $request_stack->getCurrentRequest());
  }

  /**
   * {@inheritdoc}
   */
  public function authenticate(): void {
    try {
      $this->setAccessToken($this->client->getAccessToken('authorization_code',
        ['code' => $this->request->query->get('code')]));
    }
    catch (IdentityProviderException $e) {
      $this->loggerFactory->get('social_auth_clyde')
        ->error('There was an error during authentication. Exception: ' . $e->getMessage());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getUserInfo(): SocialAuthUserInterface {
    if (!$this->user) {
      /** @var \League\OAuth2\Client\Provider\GoogleUser $owner */
      $owner = $this->client->getResourceOwner($this->getAccessToken());
      $this->user = new SocialAuthUser(
        $owner->getName(),
        $owner->getId(),
        $this->getAccessToken(),
        $owner->getEmail(),
        $owner->getAvatar(),
        $this->getExtraDetails()
      );
      $this->user->setFirstName($owner->getFirstName());
      $this->user->setLastName($owner->getLastName());
    }
    return $this->user;
  }

  /**
   * {@inheritdoc}
   */
  public function getAuthorizationUrl(): string {
    $scopes = [
      'email',
      'profile',
    ];

    $extra_scopes = $this->getScopes();
    if ($extra_scopes) {
      $scopes = array_merge($scopes, explode(',', $extra_scopes));
    }

    // Returns the URL where user will be redirected.
    return $this->client->getAuthorizationUrl([
      'scope' => $scopes,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function requestEndPoint(string $method, string $path, ?string $domain = NULL, array $options = []): mixed {
    if (!$domain) {
      $domain = 'https://www.googleapis.com';
    }

    $url = $domain . $path;

    $request = $this->client->getAuthenticatedRequest($method, $url, $this->getAccessToken(), $options);

    try {
      return $this->client->getParsedResponse($request);
    }
    catch (IdentityProviderException $e) {
      $this->loggerFactory->get('social_auth_clyde')
        ->error('There was an error when requesting ' . $url . '. Exception: ' . $e->getMessage());
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getState(): string {
    return $this->client->getState();
  }

}
