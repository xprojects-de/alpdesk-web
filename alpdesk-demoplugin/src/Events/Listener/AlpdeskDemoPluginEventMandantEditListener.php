<?php

declare(strict_types=1);

namespace Alpdesk\AlpdeskDemoPlugin\Events\Listener;

use Alpdesk\AlpdeskCore\Events\Event\AlpdeskCoreMandantEditEvent;
use Alpdesk\AlpdeskCore\Library\Mandant\AlpdeskCoreMandantResponse;

class AlpdeskDemoPluginEventMandantEditListener {

  public function __invoke(AlpdeskCoreMandantEditEvent $event) {
    //dump($event);
    //die;
  }

}
