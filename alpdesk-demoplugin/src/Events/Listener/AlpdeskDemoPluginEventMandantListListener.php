<?php

declare(strict_types=1);

namespace Alpdesk\AlpdeskDemoPlugin\Events\Listener;

use Alpdesk\AlpdeskCore\Events\Event\AlpdeskCoreMandantListEvent;
use Alpdesk\AlpdeskCore\Library\Mandant\AlpdeskCoreMandantResponse;

class AlpdeskDemoPluginEventMandantListListener {

  public function __invoke(AlpdeskCoreMandantListEvent $event) {
    //dump($event);
    //die;
  }

}
