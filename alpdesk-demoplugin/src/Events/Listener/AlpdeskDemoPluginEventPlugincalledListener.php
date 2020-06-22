<?php

declare(strict_types=1);

namespace Alpdesk\AlpdeskDemoPlugin\Events\Listener;

use Alpdesk\AlpdeskCore\Events\Event\AlpdeskCorePlugincallEvent;
use Alpdesk\AlpdeskCore\Library\Plugin\AlpdeskCorePlugincallResponse;

class AlpdeskDemoPluginEventPlugincalledListener {

  public function __invoke(AlpdeskCorePlugincallEvent $event) {
    //dump($event);
    //die;
  }

}
