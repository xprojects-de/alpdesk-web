<?php

declare(strict_types=1);

namespace Alpdesk\AlpdeskBillOfferLibPlugin\Elements;

use Alpdesk\AlpdeskCore\Elements\AlpdeskCoreElement;
use Alpdesk\AlpdeskCore\Library\Mandant\AlpdescCoreBaseMandantInfo;
use Alpdesk\AlpdeskCore\Library\PDF\AlpdeskCorePDFCreator;
use Contao\FilesModel;
use Contao\File;
use Contao\System;
use Symfony\Component\Filesystem\Filesystem;

class AlpdeskElementBillOfferLib extends AlpdeskCoreElement {

  private int $billtemplate = 0;
  private int $offertemplate = 0;
  private $billmount;
  private $offermount;

  private function createBill(array $data, array $params): array {
    /*
     * $params
     * 
     * {
     *   "address":"Benjamin Hummel\nAuf der Halde 1\n87545 Oberstaufen",
     *   "subject":"Rechnungsbetreff",
     *   "billnumber":"XP-20-XX",     *   
     *   "items":[
     *     {
     *       "label": "Test",
     *       "value": 24.50
     *     },
     *     {
     *       "label": "Test 1",
     *       "value": 25.00
     *     }
     *    ]
     * }
     */
    if (!\array_key_exists('address', $params) || !\array_key_exists('subject', $params) || !\array_key_exists('billnumber', $params) || !\array_key_exists('items', $params)) {
      throw new \Exception('invalid parameters');
    }
    $mount = FilesModel::findByUuid($this->billmount);
    if ($mount === null) {
      throw new \Exception('invalid billmount');
    }

    try {
      $completePrice = 0;
      $services = '';
      $prices = '';
      foreach ($params['items'] as $item) {
        $label = $item['label'];
        $value = floatval($item['value']);
        if ($label != "" && $value > 0) {
          $completePrice += floatval($value);
          $services .= $label . '<br>';
          $prices .= number_format(floatval($value), 2) . ' € <br>';
        }
      }

      $search_array = array(
          0 => '{|adresse|}',
          1 => '{|betreff|}',
          2 => '{|leistung|}',
          3 => '{|preis|}',
          4 => '{|gesamtpreis|}',
          5 => '{|rechnungsnummer|}',
          6 => '{|datum|}'
      );
      $replace_array = array(
          0 => nl2br($params['address']),
          1 => $params['subject'],
          2 => $services,
          3 => $prices,
          4 => number_format($completePrice, 2) . ' €',
          5 => $params['billnumber'],
          6 => date('d.m.Y')
      );

      $path = $mount->path . '/' . date('Y') . '/open';
      $rootDir = System::getContainer()->getParameter('kernel.project_dir');
      $filesystem = new Filesystem();
      if (!$filesystem->exists($rootDir . '/' . $path)) {
        $filesystem->mkdir($rootDir . '/' . $path);
      }
      $filename = $params['billnumber'] . '_' . time() . '.pdf';
      $pdf = new AlpdeskCorePDFCreator();
      $pdf->setReplaceData($search_array, $replace_array);
      $billFile = $pdf->generateById($this->billtemplate, $path, $filename);
      $data['msg'] = $billFile;
      $data['error'] = false;
    } catch (\Exception $e) {
      $data['msg'] = $e->getMessage();
      $data['error'] = true;
    }
    return $data;
  }

  public function createOffer(array $data, array $params): array {

    /*
     * $params
     * 
     * {
     *   "address":"Benjamin Hummel\nAuf der Halde 1\n87545 Oberstaufen",
     *   "subject":"Rechnungsbetreff",
     *   "items":[
     *     {
     *       "label": "Test",
     *       "value": 24.50,
     *       "optional": false
     *     },
     *     {
     *       "label": "Test 1",
     *       "value": 25.00,
     *       "optional": true
     *     }
     *    ]
     * }
     */
    if (!\array_key_exists('address', $params) || !\array_key_exists('subject', $params) || !\array_key_exists('items', $params)) {
      throw new \Exception('invalid parameters');
    }
    $mount = FilesModel::findByUuid($this->offermount);
    if ($mount === null) {
      throw new \Exception('invalid offermount');
    }

    try {
      $completePrice = 0.0;
      $positions = '<h4>Positionen</h4><table cellpadding="3" cellspacing="3">';
      $positions_optional = '<table cellpadding="3" cellspacing="3">';
      $found = false;
      $foundOptional = false;
      foreach ($params['items'] as $item) {
        $price = floatval($item['value']);
        $text = nl2br($item['label']);
        if ($item['optional'] != true) {
          $completePrice = floatval($completePrice + $price);
          $positions .= '<tr>';
          $positions .= '<td>' . $text . '</td>';
          $positions .= '<td>' . number_format($price, 2) . ' €</td>';
          $positions .= '</tr>';
          $found = true;
        } else {
          $positions_optional .= '<tr>';
          $positions_optional .= '<td>' . $text . '</td>';
          $positions_optional .= '<td>' . number_format($price, 2) . ' €</td>';
          $positions_optional .= '</tr>';
          $foundOptional = true;
        }
      }
      $completePrice = number_format($completePrice, 2);
      if ($found == true) {
        $positions .= '<tr>';
        $positions .= '<td></td>';
        $positions .= '<td><br><strong>---------------<br>' . $completePrice . ' €</strong></td>';
        $positions .= '</tr>';
      }
      $positions_optional .= '<table>';
      $positions .= '<table>';
      if ($foundOptional == true) {
        $positions .= '<p><br/><br/></p><h4>Optionale Positionen</h4>' . $positions_optional;
      }

      $search_array = array(
          0 => '{|address|}',
          1 => '{|subject|}',
          2 => '{|body|}',
          3 => '{|date|}'
      );
      $replace_array = array(
          0 => nl2br($params['address']),
          1 => $params['subject'],
          2 => $positions,
          3 => date('d.m.Y')
      );

      $path = $mount->path . '/' . date('Y') . '/open';
      $rootDir = System::getContainer()->getParameter('kernel.project_dir');
      $filesystem = new Filesystem();
      if (!$filesystem->exists($rootDir . '/' . $path)) {
        $filesystem->mkdir($rootDir . '/' . $path);
      }
      $filename = 'offer_' . date('d-m-Y') . '_' . time() . '.pdf';
      $pdf = new AlpdeskCorePDFCreator();
      $pdf->setReplaceData($search_array, $replace_array);
      $offerFile = $pdf->generateById($this->offertemplate, $path, $filename);
      $data['msg'] = $offerFile;
      $data['error'] = false;
    } catch (\Exception $e) {
      $data['msg'] = $e->getMessage();
      $data['error'] = true;
    }
    return $data;
  }

  public function execute(AlpdescCoreBaseMandantInfo $mandantInfo, array $data): array {
    $mandantInfoData = $mandantInfo->getAdditionalDatabaseInformation();
    $this->billtemplate = (int) $mandantInfoData['billtemplate'];
    $this->offertemplate = (int) $mandantInfoData['offertemplate'];
    $this->billmount = $mandantInfoData['billmount'];
    $this->offermount = $mandantInfoData['offermount'];
    $response = array(
        'error' => true,
        'msg' => ''
    );
    if (\is_array($data) && \array_key_exists('method', $data) && \array_key_exists('param', $data)) {
      try {
        switch ($data['method']) {
          case 'bill':
            $response = $this->createBill($response, $data['param']);
            break;
          case 'offer':
            $response = $this->createOffer($response, $data['param']);
            break;
          default:
            break;
        }
      } catch (\Exception $ex) {
        $response['error'] = true;
        $response['msg'] = $ex->getMessage();
      }
    }
    return $response;
  }

}
