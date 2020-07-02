<?php

declare(strict_types=1);

namespace Alpdesk\AlpdeskCore\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class AlpdeskcoreTokenAuthenticator extends AbstractGuardAuthenticator {

  private static string $prefix = 'Bearer';
  private static string $name = 'Authorization';
  protected $framework;

  public function __construct(ContaoFrameworkInterface $framework) {
    $this->framework = $framework;
  }

  public function start(Request $request, AuthenticationException $authException = null) {
    $data = ['message' => 'Auth required'];
    return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
  }

  public function onAuthenticationFailure(Request $request, AuthenticationException $exception) {
    $data = [
        'message' => strtr($exception->getMessage(), $exception->getMessageData())
    ];
    return new JsonResponse($data, Response::HTTP_FORBIDDEN);
  }

  public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey) {
    return null;
  }

  public function supportsRememberMe() {
    return false;
  }

  public function getCredentials(Request $request) {
    if (!$request->headers->has(self::$name)) {
      throw new AuthenticationException(self::$name . ' not found in Header');
    }
    $authorizationHeader = $request->headers->get(self::$name);
    if (empty($authorizationHeader)) {
      throw new AuthenticationException(self::$name . ' empty in Header');
    }
    $headerParts = explode(' ', $authorizationHeader);
    if (!(2 === count($headerParts) && 0 === strcasecmp($headerParts[0], self::$prefix))) {
      throw new AuthenticationException('no valid value for ' . self::$name . ' in Header');
    }
    return ['token' => $headerParts[1]];
  }

  public function getUser($credentials, UserProviderInterface $userProvider) {
    try {
      $username = $userProvider->getValidatedUsernameFromToken($credentials['token']);
    } catch (\Exception $e) {
      throw new AuthenticationException($e->getMessage());
    }
    return $userProvider->loadUserByUsername($username);
  }

  public function checkCredentials($credentials, UserInterface $user) {
    if($user->getFixToken() === $credentials['token']) {
      $user->setFixTokenAuth(true);
    }
    return (($user->getToken() === $credentials['token']) || ($user->getFixToken() === $credentials['token']));
  }

  public function supports(Request $request) {
    if ('alpdeskapi' === $request->attributes->get('_scope')) {
      return true;
    }
    return false;
  }

}
