<?php

declare(strict_types=1);

namespace Alpdesk\AlpdeskDemoPlugin\Events\Listener;

use Alpdesk\AlpdeskCore\Events\Event\AlpdeskCoreAuthInvalidEvent;
use Alpdesk\AlpdeskCore\Library\Auth\AlpdeskCoreAuthResponse;

class AlpdeskDemoPluginEventAuthInvalidListener {

  public function __invoke(AlpdeskCoreAuthInvalidEvent $event) {
    //$tmp = $event->getResultData();
    //$tmp->setAlpdesk_token($tmp->getAlpdesk_token() . '_token');
    //$event->setResultData($tmp);
  }

}
