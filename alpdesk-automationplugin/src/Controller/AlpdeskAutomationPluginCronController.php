<?php

declare(strict_types=1);

namespace Alpdesk\AlpdeskAutomationPlugin\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Alpdesk\AlpdeskAutomationPlugin\Model\AlpdeskautomationitemsModel;
use Alpdesk\AlpdeskAutomationPlugin\Model\AlpdeskautomationhistoryModel;

class AlpdeskAutomationPluginCronController extends Controller {

  protected ContaoFramework $framework;

  public function __construct(ContaoFramework $framework) {
    $this->framework = $framework;
    $this->framework->initialize();
  }

  public function cron(Request $request): Response {

    $returnvalue = array(
        'error' => true,
        'msg' => ''
    );

    try {

      $dbItems = AlpdeskautomationitemsModel::findAll();
      if ($dbItems !== null) {

        $data = array();

        foreach ($dbItems as $dbItem) {

          if (!array_key_exists($dbItem->mandant, $data)) {
            $data[$dbItem->mandant] = array();
          }

          $date = date('d.m.Y H:i', intval($dbItem->tstamp));
          array_push($data[$dbItem->mandant], array(
              'tstamp' => $dbItem->tstamp,
              'date' => $date,
              'devicehandle' => $dbItem->devicehandle,
              'devicevalue' => json_decode($dbItem->devicevalue, true)
          ));
        }

        // Delete old Items
        // 1 Month => 60sec * 60min = 3600 = 1h * 24 = 1 day * 30 = 30 days
        $constTime = time() - (60 * 60 * 24 * 30);
        $deleteHistoryItems = AlpdeskautomationhistoryModel::findBy(array('tstamp<?'), array($constTime));
        if ($deleteHistoryItems !== null) {
          foreach ($deleteHistoryItems as $dItem) {
            $dItem->delete();
          }
        }

        if (count($data) > 0) {
          foreach ($data as $mandant => $data) {
            $historyItem = new AlpdeskautomationhistoryModel();
            $historyItem->tstamp = time();
            $historyItem->mandant = $mandant;
            $historyItem->data = json_encode($data);
            $historyItem->save();
          }
        }

        $returnvalue['error'] = false;
      }
    } catch (\Exception $ex) {
      $returnvalue['error'] = true;
      $returnvalue['msg'] = $ex->getMessage();
    }

    return $this->json($returnvalue);
  }

}
