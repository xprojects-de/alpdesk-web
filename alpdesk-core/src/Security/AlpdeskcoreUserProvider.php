<?php

declare(strict_types=1);

namespace Alpdesk\AlpdeskCore\Security;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Alpdesk\AlpdeskCore\Jwt\JwtToken;
use Alpdesk\AlpdeskCore\Security\AlpdeskcoreInputSecurity;
use Alpdesk\AlpdeskCore\Model\Auth\AlpdeskcoreSessionsModel;
use Alpdesk\AlpdeskCore\Model\Mandant\AlpdeskcoreMandantModel;
use Alpdesk\AlpdeskCore\Security\AlpdeskcoreUser;

class AlpdeskcoreUserProvider implements UserProviderInterface {

  private $framework;

  public function __construct(ContaoFrameworkInterface $framework) {
    $this->framework = $framework;
    $this->framework->initialize();
  }

  public static function createJti($username) {
    return base64_encode('alpdesk_' . $username);
  }

  public static function createToken(string $username, int $ttl): string {
    return JwtToken::generate(self::createJti($username), $ttl, array('username' => $username));
  }

  public function getValidatedUsernameFromToken(string $token): string {
    $username = JwtToken::parse($token)->getClaim('username');
    if ($username == null || $username == '') {
      throw new AuthenticationException('invalid username');
    }
    $validateAndVerify = JwtToken::validateAndVerify($token, self::createJti($username));
    if ($validateAndVerify == false) {
      throw new AuthenticationException('invalid JWT-Token for username:' . $username . ' at verification and validation');
    }
    return AlpdeskcoreInputSecurity::secureValue($username);
  }

  /**
   * Override from UserProviderInterface
   * @param string $username
   * @return type
   * @throws AuthenticationException
   */
  public function loadUserByUsername($username) {
    $alpdeskUser = new AlpdeskcoreUser();
    $alpdeskUser->setUsername($username);
    $sessionModel = AlpdeskcoreSessionsModel::findByUsername($username);
    if ($sessionModel !== null) {
      if (JwtToken::validateAndVerify($sessionModel->token, self::createJti($username))) {
        $alpdeskUser->setToken($sessionModel->token);
      }
    }
    $userData = AlpdeskcoreMandantModel::findByAuthUsername($username);
    if ($userData !== null) {
      $alpdeskUser->setFixToken($userData->fixtoken);
    }
    return $alpdeskUser;
  }

  public function refreshUser(\Symfony\Component\Security\Core\User\UserInterface $user) {
    throw new UnsupportedUserException('Refresh not possible');
  }

  public function supportsClass($class) {
    return $class === AlpdeskcoreUser::class;
  }

}
