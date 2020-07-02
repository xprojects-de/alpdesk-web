<?php

declare(strict_types=1);

namespace Alpdesk\AlpdeskCore\Security;

use Symfony\Component\Security\Core\User\UserInterface;

class AlpdeskcoreUser implements UserInterface {

  private $username = '';
  private $token = '';
  private $fixToken = '';

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
