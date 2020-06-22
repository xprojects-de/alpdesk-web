<?php

declare(strict_types=1);

namespace Alpdesk\AlpdeskCore\Library\Mandant;

use Alpdesk\AlpdeskCore\Library\Exceptions\AlpdeskCoreMandantException;
use Alpdesk\AlpdeskCore\Library\Auth\AlpdeskCoreAuthToken;
use Alpdesk\AlpdeskCore\Model\Mandant\AlpdeskcoreMandantElementsModel;
use Alpdesk\AlpdeskCore\Model\Mandant\AlpdeskcoreMandantModel;
use Alpdesk\AlpdeskCore\Library\Mandant\AlpdeskCoreMandantResponse;
use Contao\StringUtil;
use Alpdesk\AlpdeskCore\Elements\AlpdeskCoreElement;

class AlpdeskCoreMandant {

  private function verifyAndgetMandant(string $username, string $alpdesk_token): int {
    $mandantId = (new AlpdeskCoreAuthToken())->verifyTokenAndGetMandantId($username, $alpdesk_token);
    if ($mandantId > 0) {
      return $mandantId;
    } else {
      throw new AlpdeskCoreMandantException("invalid token");
    }
  }

  private function getPlugins(int $mandantId): array {
    $data = array();
    $plugins = AlpdeskcoreMandantElementsModel::findEnabledAndVisibleByPid($mandantId);
    if ($plugins !== null) {
      // @ToDo load for other languages
      \System::loadLanguageFile('modules', 'de');
      foreach ($plugins as $pluginElement) {
        $type = (string) $pluginElement->type;
        if (isset($GLOBALS['TL_ADME'][$type])) {
          $c = new $GLOBALS['TL_ADME'][$type]();
          if ($c instanceof AlpdeskCoreElement) {
            $customTemplate = false;
            if ($c->getCustomTemplate() == true) {
              $customTemplate = true;
            }
            \array_push($data, array(
                'value' => $pluginElement->type,
                'label' => $GLOBALS['TL_LANG']['ADME'][$pluginElement->type],
                'customTemplate' => $customTemplate
            ));
          }
        }
      }
    } else {
      throw new AlpdeskCoreMandantException("error loading plugins for Mandant");
    }
    return $data;
  }

  private function getData(int $mandantId): array {
    $mData = AlpdeskcoreMandantModel::findById($mandantId);
    if ($mData !== null) {
      $data = $mData->row();
      unset($data['id']);
      unset($data['tstamp']);
      $returnData = array();
      // @ToDo load for other languages
      \System::loadLanguageFile('tl_alpdeskcore_mandant', 'de');
      \Controller::loadDataContainer('tl_alpdeskcore_mandant');
      foreach ($data as $key => $value) {
        if (isset($GLOBALS['TL_DCA']['tl_alpdeskcore_mandant']['fields'][$key]['eval']['alpdesk_apishow']) && $GLOBALS['TL_DCA']['tl_alpdeskcore_mandant']['fields'][$key]['eval']['alpdesk_apishow'] == true) {
          $returnData[$key] = array(
              'value' => StringUtil::convertEncoding(StringUtil::deserialize($value), 'UTF-8'),
              'label' => $GLOBALS['TL_LANG']['tl_alpdeskcore_mandant'][$key][0],
          );
        }
      }
      return $returnData;
    } else {
      throw new AlpdeskCoreMandantException("error loading plugins for Mandant");
    }
  }

  private function modifyData(int $mandantId, array $data): void {
    $mData = AlpdeskcoreMandantModel::findById($mandantId);
    if ($mData !== null) {
      foreach ($data as $element) {
        if (\is_array($element)) {
          foreach ($element as $key => $value) {
            if ($mData->__isset($key)) {
              $mData->__set($key, $value);
            }
          }
        }
      }
      if ($mData->isModified()) {
        $mData->save();
      }
    } else {
      throw new AlpdeskCoreMandantException("error modify Mandant Data");
    }
  }

  public function list(string $jwtToken): AlpdeskCoreMandantResponse {
    $username = AlpdeskCoreAuthToken::getUsernameFromToken($jwtToken);
    $mandantId = $this->verifyAndgetMandant($username, $jwtToken);
    $pluginData = $this->getPlugins($mandantId);
    $dataData = $this->getData($mandantId);
    $response = new AlpdeskCoreMandantResponse();
    $response->setUsername($username);
    $response->setAlpdesk_token($jwtToken);
    $response->setMandantId($mandantId);
    $response->setPlugins($pluginData);
    $response->setData($dataData);
    return $response;
  }

  public function edit(array $mandantdata): AlpdeskCoreMandantResponse {
    // @ ToDo Currently not supported
    if (!\array_key_exists('username', $mandantdata) || !\array_key_exists('alpdesk_token', $mandantdata) || !\array_key_exists('data', $mandantdata)) {
      throw new AlpdeskCoreMandantException("invalid key-parameters for mandant edit");
    }
    $username = (string) $mandantdata['username'];
    $alpdesk_token = (string) $mandantdata['alpdesk_token'];
    $data = (array) $mandantdata['data'];
    $mandantId = $this->verifyAndgetMandant($username, $alpdesk_token);
    $this->modifyData($mandantId, $data);
    $pluginData = $this->getPlugins($mandantId);
    $dataData = $this->getData($mandantId);
    $response = new AlpdeskCoreMandantResponse();
    $response->setUsername($username);
    $response->setAlpdesk_token($alpdesk_token);
    $response->setMandantId($mandantId);
    $response->setPlugins($pluginData);
    $response->setData($dataData);
    return $response;
  }

}
