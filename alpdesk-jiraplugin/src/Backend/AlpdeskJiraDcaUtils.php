<?php

declare(strict_types=1);

namespace Alpdesk\AlpdeskJiraPlugin\Backend;

use Contao\DataContainer;
use Contao\Backend;
use Alpdesk\AlpdeskCore\Library\Cryption\Cryption;

class AlpdeskJiraDcaUtils extends Backend {

  public function generateEncryptPassword($varValue, DataContainer $dc) {
    if ($varValue === '') {
      return $varValue;
    }
    $cryption = new Cryption(true);
    return $cryption->safeEncrypt($varValue);
  }

  public function regenerateEncryptPassword($varValue, DataContainer $dc) {
    if ($varValue === '') {
      return $varValue;
    }
    if ($dc->activeRecord) {
      $cryption = new Cryption(true);
      $varValue = $cryption->safeDecrypt($varValue);
    }
    return $varValue;
  }

}
