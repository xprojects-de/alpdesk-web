<?php

declare(strict_types=1);

namespace Alpdesk\AlpdeskDemoPlugin\Events\Listener;

use Alpdesk\AlpdeskCore\Events\Event\AlpdeskCoreAuthSuccessEvent;
use Alpdesk\AlpdeskCore\Library\Auth\AlpdeskCoreAuthResponse;

class AlpdeskDemoPluginEventAuthSuccessListener {

  public function __invoke(AlpdeskCoreAuthSuccessEvent $event) {
    //$tmp = $event->getResultData();
    //$tmp->setAlpdesk_token($tmp->getAlpdesk_token() . '_token');
    //$event->setResultData($tmp);
  }

}
