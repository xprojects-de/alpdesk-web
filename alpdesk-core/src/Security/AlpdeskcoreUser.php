<?php

declare(strict_types=1);

namespace Alpdesk\AlpdeskCore\Security;

use Symfony\Component\Security\Core\User\UserInterface;

class AlpdeskcoreUser implements UserInterface {

  private $mandantid = 0;
  private $username = '';
  private $token = '';
  private $fixToken = '';
  private $fixTokenAuth = false;

  public function getMandantid() {
    return $this->mandantid;
  }

  public function setMandantid($mandantid): void {
    $this->mandantid = $mandantid;
  }

  public function getToken() {
    return $this->token;
  }

  public function setToken($token): void {
    $this->token = $token;
  }

  public function setUsername($username): void {
    $this->username = $username;
  }

  public function getUsername() {
    return $this->username;
  }

  public function getFixToken() {
    return $this->fixToken;
  }

  public function setFixToken($fixToken): void {
    $this->fixToken = $fixToken;
  }

  public function getFixTokenAuth(): bool {
    return $this->fixTokenAuth;
  }

  public function setFixTokenAuth(bool $fixTokenAuth): void {
    $this->fixTokenAuth = $fixTokenAuth;
  }

  public function getUsedToken(): string {
    if ($this->getFixTokenAuth() == true) {
      return $this->getFixToken();
    }
    return $this->getToken();
  }

  public function getRoles() {
    return array('ROLE_USER');
  }

  public function getPassword() {
    
  }

  public function getSalt() {
    
  }

  public function eraseCredentials() {
    
  }

}
