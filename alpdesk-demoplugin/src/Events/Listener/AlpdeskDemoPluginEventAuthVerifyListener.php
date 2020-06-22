<?php

declare(strict_types=1);

namespace Alpdesk\AlpdeskDemoPlugin\Events\Listener;

use Alpdesk\AlpdeskCore\Events\Event\AlpdeskCoreAuthVerifyEvent;
use Alpdesk\AlpdeskCore\Library\Auth\AlpdeskCoreAuthResponse;

class AlpdeskDemoPluginEventAuthVerifyListener {

  public function __invoke(AlpdeskCoreAuthVerifyEvent $event) {
    //$tmp = $event->getResultData();
    //$tmp->setAlpdesk_token($tmp->getAlpdesk_token() . '_token');
    //$event->setResultData($tmp);
  }

}
