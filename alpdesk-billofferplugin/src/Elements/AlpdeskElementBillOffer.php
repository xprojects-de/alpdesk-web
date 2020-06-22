<?php

declare(strict_types=1);

namespace Alpdesk\AlpdeskBillOfferLibPlugin\Elements;

use Alpdesk\AlpdeskCore\Elements\AlpdeskCoreElement;
use Alpdesk\AlpdeskCore\Library\Mandant\AlpdescCoreBaseMandantInfo;

class AlpdeskElementBillOffer extends AlpdeskCoreElement {

  public function execute(AlpdescCoreBaseMandantInfo $mandantInfo, array $data): array {
    return array();
  }

}
