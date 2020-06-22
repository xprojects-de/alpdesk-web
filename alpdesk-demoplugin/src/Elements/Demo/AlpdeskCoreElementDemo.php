<?php

declare(strict_types=1);

namespace Alpdesk\AlpdeskDemoPlugin\Elements\Demo;

use Alpdesk\AlpdeskCore\Elements\AlpdeskCoreElement;
use Alpdesk\AlpdeskCore\Library\Mandant\AlpdescCoreBaseMandantInfo;

class AlpdeskCoreElementDemo extends AlpdeskCoreElement {

  public function execute(AlpdescCoreBaseMandantInfo $mandantInfo, array $data): array {
    return array('Mandant' => $mandantInfo->getMandant(), 'Value' => 'Hello Demoplugin');
  }

}
