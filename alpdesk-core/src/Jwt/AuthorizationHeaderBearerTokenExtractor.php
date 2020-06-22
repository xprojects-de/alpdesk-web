<?php

declare(strict_types=1);

namespace Alpdesk\AlpdeskCore\Jwt;

use Symfony\Component\HttpFoundation\Request;

class AuthorizationHeaderBearerTokenExtractor {

  // Use Package "lcobucci/jwt": "^3.2" in composer.json, but still in Contao-Core-Bundle composer.json
  private static string $prefix = 'Bearer';
  private static string $name = 'Authorization';

  public static function extract(Request $request): string {
    if (!$request->headers->has(self::$name)) {
      throw new \Exception(self::$name . ' not found in Header');
    }
    $authorizationHeader = $request->headers->get(self::$name);
    if (empty($authorizationHeader)) {
      throw new \Exception(self::$name . ' empty in Header');
    }
    $headerParts = explode(' ', $authorizationHeader);
    if (!(2 === count($headerParts) && 0 === strcasecmp($headerParts[0], self::$prefix))) {
      throw new \Exception('no valid value for ' . self::$name . ' in Header');
    }
    return (string) $headerParts[1];
  }

}
