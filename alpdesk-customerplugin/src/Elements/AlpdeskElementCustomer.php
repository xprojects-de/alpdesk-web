<?php

declare(strict_types=1);

namespace Alpdesk\AlpdeskCustomerPlugin\Elements;

use Alpdesk\AlpdeskCore\Elements\AlpdeskCoreElement;
use Alpdesk\AlpdeskCore\Library\Mandant\AlpdescCoreBaseMandantInfo;
use Alpdesk\AlpdeskCustomerPlugin\Library\AlpdeskCustomerPluginList;
use Alpdesk\AlpdeskCustomerPlugin\Library\AlpdeskCustomerPluginDetail;

class AlpdeskElementCustomer extends AlpdeskCoreElement {

  protected bool $customTemplate = true;

  public function execute(AlpdescCoreBaseMandantInfo $mandantInfo, array $data): array {
    $mandantInfoData = $mandantInfo->getAdditionalDatabaseInformation();
    $customerdb = (int) $mandantInfoData['customerdb'];
    $response = array(
        'ngContent' => 'error loading data',
        'ngStylesheetUrl' => array(),
        'ngScriptUrl' => array()
    );
    if (\is_array($data)) {
      try {
        if (count($data) > 0 && $data['subtarget'] == 'detail') {
          $response = (new AlpdeskCustomerPluginDetail())->render($customerdb, $data);
        } else {
          $response = (new AlpdeskCustomerPluginList())->render($customerdb, $data);
        }
      } catch (\Exception $ex) {
        $response['ngContent'] = $ex->getMessage();
      }
    }
    return $response;
  }

}
