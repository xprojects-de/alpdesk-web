<?php

declare(strict_types=1);

namespace Alpdesk\AlpdeskCore\Library\Plugin;

use Alpdesk\AlpdeskCore\Library\Exceptions\AlpdeskCorePluginException;
use Alpdesk\AlpdeskCore\Library\Auth\AlpdeskCoreAuthToken;
use Alpdesk\AlpdeskCore\Model\Mandant\AlpdeskcoreMandantElementsModel;
use Alpdesk\AlpdeskCore\Library\Plugin\AlpdeskCorePlugincallResponse;
use Alpdesk\AlpdeskCore\Model\Mandant\AlpdeskcoreMandantModel;
use Contao\FilesModel;
use Contao\StringUtil;
use Alpdesk\AlpdeskCore\Library\Mandant\AlpdescCoreBaseMandantInfo;
use Alpdesk\AlpdeskCore\Security\AlpdeskcoreInputSecurity;
use Alpdesk\AlpdeskCore\Elements\AlpdeskCoreElement;

class AlpdeskCorePlugin {

  protected string $rootDir;

  public function __construct(string $rootDir) {
    $this->rootDir = $rootDir;
  }

  private function verifyAndgetMandant(string $username, string $alpdesk_token, string $plugin): int {
    $mandantId = (new AlpdeskCoreAuthToken())->verifyTokenAndGetMandantId($username, $alpdesk_token);
    if ($mandantId > 0) {
      $plugins = AlpdeskcoreMandantElementsModel::findEnabledByPid($mandantId);
      if ($plugins !== null) {
        $validPlugin = false;
        foreach ($plugins as $pluginElement) {
          if ($pluginElement->type == $plugin) {
            $validPlugin = true;
            break;
          }
        }
        if ($validPlugin == false) {
          $msg = 'error loading plugin for username:' . $username;
          throw new AlpdeskCorePluginException($msg);
        }
        return $mandantId;
      } else {
        $msg = 'error loading plugin because null for username:' . $username;
        throw new AlpdeskCorePluginException($msg);
      }
    } else {
      throw new AlpdeskCorePluginException("invalid token");
    }
  }

  private function getMandantInformation($id): AlpdescCoreBaseMandantInfo {
    $mandantInfo = AlpdeskcoreMandantModel::findById($id);
    if ($mandantInfo !== null) {
      $rootPath = FilesModel::findByUuid($mandantInfo->filemount);
      $mInfo = new AlpdescCoreBaseMandantInfo();
      $mInfo->setId(intval($mandantInfo->id));
      $mInfo->setMandant($mandantInfo->mandant);
      $mInfo->setFilemount_uuid($mandantInfo->filemount);
      $mInfo->setFilemount_path($rootPath->path);
      $mInfo->setFilemount_rootpath($this->rootDir . '/' . $rootPath->path);
      $mInfo->setAdditionalDatabaseInformation($mandantInfo->row());
      return $mInfo;
    } else {
      $msg = 'cannot get Mandantinformations for username:' . $username;
      throw new AlpdeskCorePluginException($msg);
    }
  }

  public function call(string $jwtToken, array $plugindata): AlpdeskCorePlugincallResponse {
    $username = AlpdeskCoreAuthToken::getUsernameFromToken($jwtToken);
    if (!\array_key_exists('plugin', $plugindata) || !\array_key_exists('data', $plugindata)) {
      $msg = 'invalid key-parameters for plugin';
      throw new AlpdeskCorePluginException($msg);
    }
    $plugin = (string) AlpdeskcoreInputSecurity::secureValue($plugindata['plugin']);
    $data = (array) $plugindata['data'];
    $mandantId = $this->verifyAndgetMandant($username, $jwtToken, $plugin);
    $mandantInfo = $this->getMandantInformation($mandantId);
    $response = new AlpdeskCorePlugincallResponse();
    $response->setUsername($username);
    $response->setAlpdesk_token($jwtToken);
    $response->setMandantInfo($mandantInfo);
    $response->setPlugin($plugin);
    if (isset($GLOBALS['TL_ADME'][$plugin])) {
      $c = new $GLOBALS['TL_ADME'][$plugin]();
      if ($c instanceof AlpdeskCoreElement) {
        $tmp = $c->execute($mandantInfo, $data);
        if ($c->getCustomTemplate() == true) {
          if (!\array_key_exists('ngContent', $tmp) ||
                  !\array_key_exists('ngStylesheetUrl', $tmp) ||
                  !\array_key_exists('ngScriptUrl', $tmp)) {
            $msg = 'plugin use customTemplate but keys not defined in resultArray for plugin:' . $plugin . ' and username:' . $username;
            throw new AlpdeskCorePluginException($msg);
          }
          $tmp['ngContent'] = StringUtil::convertEncoding($tmp['ngContent'], 'UTF-8');
        }
        $response->setData($tmp);
      } else {
        $msg = 'plugin entrypoint wrong classtype for plugin:' . $plugin . ' and username:' . $username;
        throw new AlpdeskCorePluginException($msg);
      }
    } else {
      $msg = 'plugin not installed for plugin:' . $plugin . ' and username:' . $username;
      throw new AlpdeskCorePluginException($msg);
    }
    return $response;
  }

}
