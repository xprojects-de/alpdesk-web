<?php

declare(strict_types=1);

namespace Alpdesk\AlpdeskAutomationPlugin\Elements;

use Alpdesk\AlpdeskCore\Elements\AlpdeskCoreElement;
use Alpdesk\AlpdeskCore\Library\Mandant\AlpdescCoreBaseMandantInfo;
use Alpdesk\AlpdeskAutomationPlugin\Model\AlpdeskautomationitemsModel;
use Alpdesk\AlpdeskAutomationPlugin\Model\AlpdeskautomationchangesModel;
use Alpdesk\AlpdeskAutomationPlugin\Model\AlpdeskautomationhistoryModel;

class AlpdeskElementAutomation extends AlpdeskCoreElement {

  public static $TYPE_INPUT = 1000;
  public static $TYPE_OUTPUT = 2000;
  public static $TYPE_TEMPERATURE = 3000;
  public static $TYPE_SCENE = 4000;
  public static $TYPE_TIME = 5000;
  public static $TYPE_DIMMERDEVICE = 6000;
  public static $TYPE_SENSOR = 7000;
  public static $TYPE_DHT22 = 8000;
  public static $TYPE_HEATINGPUMP = 9000;
  public static $TYPE_VENTILATION = 10000;
  public static $TYPE_SHADING = 11000;
  public static $TYPE_ANALOGIN = 12000;

  private function changeItem(int $mandantId, array $data, array $returnValue): array {
    if ($mandantId <= 0) {
      throw new \Exception('MandantId not found');
    }
    if (isset($data['devicehandle']) && isset($data['devicevalue'])) {
      if (isset($data['devicevalue']['devicehandle']) && isset($data['devicevalue']['propertiehandle']) && isset($data['devicevalue']['value'])) {
        $dbChange = new AlpdeskautomationchangesModel();
        $dbChange->mandant = $mandantId;
        $dbChange->devicehandle = intval($data['devicehandle']);
        $dbChange->devicevalue = json_encode($data['devicevalue']);
        $dbChange->save();
        $returnValue['error'] = false;
      }
    }
    return $returnValue;
  }

  private function listItems(int $mandantId, array $returnValue): array {
    if ($mandantId <= 0) {
      throw new \Exception('MandantId not found');
    }
    $returnValue['items'] = array();
    $dbItems = AlpdeskautomationitemsModel::findBy(array('mandant=?'), array($mandantId));
    if ($dbItems !== null) {
      foreach ($dbItems as $dbItem) {
        $date = date('d.m.Y H:i', intval($dbItem->tstamp));
        array_push($returnValue['items'], array(
            'tstamp' => $dbItem->tstamp,
            'date' => $date,
            'devicehandle' => $dbItem->devicehandle,
            'devicevalue' => json_decode($dbItem->devicevalue, true)
        ));
      }
    }
    $dbChanges = AlpdeskautomationchangesModel::findBy(array('mandant=?'), array($mandantId));
    if ($dbChanges !== null) {
      foreach ($dbChanges as $dbChange) {
        $date = date('d.m.Y H:i', intval($dbChange->tstamp));
        array_push($returnValue['changes'], array(
            'tstamp' => $dbChange->tstamp,
            'date' => $date,
            'devicehandle' => $dbChange->devicehandle,
            'devicevalue' => json_decode($dbChange->devicevalue, true)
        ));
      }
    }
    $returnValue['error'] = false;
    return $returnValue;
  }

  private function commitChanges(int $mandantId, array $data, array $returnValue): array {
    if ($mandantId <= 0) {
      throw new \Exception('MandantId not found');
    }
    foreach ($data as $deviceHandle => $value) {
      $dbItem = AlpdeskautomationitemsModel::findBy(array('mandant=?', 'devicehandle=?'), array($mandantId, $deviceHandle));
      if ($dbItem !== null) {
        $dbItem->tstamp = time();
        $dbItem->devicevalue = json_encode($value);
        $dbItem->save();
      } else {
        $dbItem = new AlpdeskautomationitemsModel();
        $dbItem->tstamp = time();
        $dbItem->mandant = $mandantId;
        $dbItem->devicehandle = $deviceHandle;
        $dbItem->devicevalue = json_encode($value);
        $dbItem->save();
      }
    }
    $dbChanges = AlpdeskautomationchangesModel::findBy(array('mandant=?'), array($mandantId));
    if ($dbChanges !== null) {
      foreach ($dbChanges as $change) {
        // {"devicehandle":-3011,"propertiehandle":4,"value":0}
        $returnValue['changes'][$change->devicehandle] = json_decode($change->devicevalue, true);
        $change->delete();
      }
    }
    $returnValue['error'] = false;
    return $returnValue;
  }

  private function history(int $mandantId, array $returnValue): array {

    if ($mandantId <= 0) {
      throw new \Exception('MandantId not found');
    }
    $returnValue['items'] = array();

    $dbItems = AlpdeskautomationhistoryModel::findBy(array('mandant=?'), array($mandantId), array('order' => 'tstamp ASC'));

    if ($dbItems !== null) {
      foreach ($dbItems as $dbItem) {

        $data = json_decode($dbItem->data, true);
        if (count($data) > 0) {
          foreach ($data as $item) {
            if (isset($item['devicevalue']['type'])) {
              $type = intval($item['devicevalue']['type']);
              if ($type == self::$TYPE_ANALOGIN ||
                      $type == self::$TYPE_SENSOR ||
                      $type == self::$TYPE_TEMPERATURE) {
                array_push($returnValue['items'], $item);
              }
            }
          }
        }
      }
    }

    $returnValue['error'] = false;

    return $returnValue;
  }

  public function execute(AlpdescCoreBaseMandantInfo $mandantInfo, array $data): array {
    $response = array(
        'error' => true,
        'changes' => array()
    );
    if (\is_array($data) && \array_key_exists('method', $data) && \array_key_exists('params', $data)) {
      try {
        switch ($data['method']) {
          case 'commit':
            $response = $this->commitChanges($mandantInfo->getId(), $data['params'], $response);
            break;
          case 'list':
            $response = $this->listItems($mandantInfo->getId(), $response);
            break;
          case 'change':
            $response = $this->changeItem($mandantInfo->getId(), $data['params'], $response);
            break;
          case 'history':
            $response = $this->history($mandantInfo->getId(), $response);
            break;
          default:
            break;
        }
      } catch (\Exception $ex) {
        $response['error'] = true;
      }
    }
    return $response;
  }

}
