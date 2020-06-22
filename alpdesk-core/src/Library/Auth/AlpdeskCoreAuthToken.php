<?php

declare(strict_types=1);

namespace Alpdesk\AlpdeskCore\Library\Auth;

use Alpdesk\AlpdeskCore\Library\Exceptions\AlpdeskCoreAuthException;
use Alpdesk\AlpdeskCore\Model\Auth\AlpdeskcoreSessionsModel;
use Alpdesk\AlpdeskCore\Library\Constants\AlpdeskCoreConstants;
use Alpdesk\AlpdeskCore\Library\Auth\AlpdeskCoreMandantAuth;
use Contao\Model\Collection;
use Alpdesk\AlpdeskCore\Library\Auth\AlpdeskCoreAuthResponse;
use Alpdesk\AlpdeskCore\Jwt\JwtToken;
use Alpdesk\AlpdeskCore\Security\AlpdeskcoreInputSecurity;

class AlpdeskCoreAuthToken {

  public static function createJti($username) {
    return base64_encode('alpdesk_' . $username);
  }

  public static function createToken(string $username, int $ttl): string {
    return JwtToken::generate(self::createJti($username), $ttl, array('username' => $username));
  }

  public static function getUsernameFromToken(string $jwtToken): string {
    $username = JwtToken::parse($jwtToken)->getClaim('username');
    $validateAndVerify = JwtToken::validateAndVerify($jwtToken, self::createJti($username));
    if ($validateAndVerify == false) {
      $msg = 'invalid JWT-Token for username:' . $username . ' at verification and validation';
      throw new \Exception($msg);
    }
    return AlpdeskcoreInputSecurity::secureValue($username);
  }

  private function getAuthSession(string $username, bool $renew = false): Collection {
    $sessionModel = AlpdeskcoreSessionsModel::findByUsername($username);
    if ($sessionModel !== null) {
      if ($renew) {
        $sessionModel->tstamp = time();
        $sessionModel->token = self::createToken($username, AlpdeskCoreConstants::$TOKENTTL);
        $sessionModel->save();
      }
      $validateAndVerify = JwtToken::validateAndVerify($sessionModel->token, self::createJti($username));
      if ($validateAndVerify == false) {
        $msg = 'invalid JWT-Token for username:' . $username . ' at verification and validation';
        throw new AlpdeskCoreAuthException($msg);
      }
      return $sessionModel;
    } else {
      $msg = 'Auth-Session not found for username:' . $username;
      throw new AlpdeskCoreAuthException($msg);
    }
  }

  private function invalidTokenData(string $username, string $token): void {
    $sessionModel = AlpdeskcoreSessionsModel::findBy(array('username=?', 'token=?'), array($username, $token));
    if ($sessionModel !== null) {
      // Create new Token with 1 sec validity as workaround
      // @ToDo mabe create other invalid JWT
      $sessionModel->token = self::createToken($username, 1);
      $sessionModel->save();
    } else {
      $msg = 'Auth-Session not found for username:' . $username;
      throw new AlpdeskCoreAuthException($msg);
    }
  }

  public function generateToken(array $authdata): AlpdeskCoreAuthResponse {
    if (!\array_key_exists('username', $authdata) || !\array_key_exists('password', $authdata)) {
      $msg = 'invalid key-parameters for auth';
      throw new AlpdeskCoreAuthException($msg);
    }
    $username = (string) AlpdeskcoreInputSecurity::secureValue($authdata['username']);
    $password = (string) AlpdeskcoreInputSecurity::secureValue($authdata['password']);
    try {
      (new AlpdeskCoreMandantAuth())->login($username, $password);
    } catch (AlpdeskCoreAuthException $ex) {
      throw new AlpdeskCoreAuthException($ex->getMessage());
    }
    $response = new AlpdeskCoreAuthResponse();
    $response->setUsername($username);
    $response->setInvalid(false);
    $response->setVerify(true);
    try {
      $tokenData = $this->getAuthSession($username, true);
      $response->setAlpdesk_token($tokenData->token);
    } catch (AlpdeskCoreAuthException $ex) {
      $sessionModelC = new AlpdeskcoreSessionsModel();
      $sessionModelC->tstamp = time();
      $sessionModelC->username = $username;
      $sessionModelC->token = self::createToken($username, AlpdeskCoreConstants::$TOKENTTL);
      $sessionModelC->save();
      $response->setAlpdesk_token($sessionModelC->token);
    }
    return $response;
  }

  public function verifyTokenAndGetMandantId(string $username, string $alpdesk_token): int {
    try {
      $sessionInfo = $this->getAuthSession($username);
      if ($sessionInfo->token == $alpdesk_token && $sessionInfo->username == $username) {
        $userinfo = (new AlpdeskCoreMandantAuth())->getMandantByUsername($sessionInfo->username);
        return intval($userinfo->pid);
      }
    } catch (\Exception | AlpdeskCoreAuthException $ex) {
      // If Exeption is thrown before we also want do check the FixToken
    }
    try {
      $userinfo = (new AlpdeskCoreMandantAuth())->loginByFixtoken($username, $alpdesk_token);
      $validateAndVerify = JwtToken::validateAndVerify($userinfo->fixtoken, self::createJti($userinfo->username));
      if ($validateAndVerify == true && $userinfo->fixtoken == $alpdesk_token && $userinfo->username == $username) {
        return intval($userinfo->pid);
      }
    } catch (\Exception | AlpdeskCoreAuthException $ex) {
      
    }
    return 0;
  }

  public function verifyToken(string $jwtToken): AlpdeskCoreAuthResponse {
    $username = self::getUsernameFromToken($jwtToken);
    $response = new AlpdeskCoreAuthResponse();
    $response->setUsername($username);
    $response->setAlpdesk_token($jwtToken);
    $response->setInvalid(false);
    try {
      $authSession = $this->getAuthSession($username);
      if ($authSession->token == $response->getAlpdesk_token()) {
        $response->setVerify(true);
        return $response;
      }
    } catch (\Exception | AlpdeskCoreAuthException $ex) {
      // If Exeption is thrown before we also want do check the FixToken
    }
    $response->setVerify(false);
    try {
      (new AlpdeskCoreMandantAuth())->loginByFixtoken($username, $response->getAlpdesk_token());
      $validateAndVerify = JwtToken::validateAndVerify($response->getAlpdesk_token(), self::createJti($username));
      $response->setVerify($validateAndVerify);
    } catch (\Exception | AlpdeskCoreAuthException $ex) {
      
    }
    return $response;
  }

  public function invalidToken(string $jwtToken): AlpdeskCoreAuthResponse {
    $username = self::getUsernameFromToken($jwtToken);
    $response = new AlpdeskCoreAuthResponse();
    $response->setUsername($username);
    $response->setAlpdesk_token($jwtToken);
    $response->setVerify(false);
    try {
      $this->invalidTokenData($response->getUsername(), $response->getAlpdesk_token());
      $response->setInvalid(true);
    } catch (AlpdeskCoreAuthException $ex) {
      $response->setInvalid(false);
    }
    return $response;
  }

}
