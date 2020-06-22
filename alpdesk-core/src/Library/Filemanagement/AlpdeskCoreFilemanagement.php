<?php

declare(strict_types=1);

namespace Alpdesk\AlpdeskCore\Library\Filemanagement;

use Alpdesk\AlpdeskCore\Library\Exceptions\AlpdeskCoreFilemanagementException;
use Alpdesk\AlpdeskCore\Library\Auth\AlpdeskCoreAuthToken;
use Alpdesk\AlpdeskCore\Library\Filemanagement\AlpdeskCoreFileuploadResponse;
use Alpdesk\AlpdeskCore\Model\Mandant\AlpdeskcoreMandantModel;
use Contao\FilesModel;
use Alpdesk\AlpdeskCore\Library\Mandant\AlpdescCoreBaseMandantInfo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class AlpdeskCoreFilemanagement {

  protected string $rootDir;

  public function __construct(string $rootDir) {
    $this->rootDir = $rootDir;
  }

  private function verifyAndgetMandant(string $username, string $alpdesk_token): int {
    $mandantId = (new AlpdeskCoreAuthToken())->verifyTokenAndGetMandantId($username, $alpdesk_token);
    if ($mandantId > 0) {
      return $mandantId;
    } else {
      throw new AlpdeskCoreFilemanagementException("invalid token");
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
      throw new AlpdeskCoreFilemanagementException("cannot get Mandant informations");
    }
  }

  private function endsWith(string $haystack, string $needle): bool {
    return (preg_match('#' . $haystack . '$#', $needle) == 1);
  }

  private function startsWith(string $haystack, string $needle): bool {
    return ($needle[0] == $haystack);
  }

  private function copyToTarget(UploadedFile $uploadFile, string $target, AlpdescCoreBaseMandantInfo $mandantInfo, AlpdeskCoreFileuploadResponse $response): void {
    if ($this->startsWith('/', $target)) {
      $target = substr($target, 1, strlen($target));
    }
    if ($this->endsWith('/', $target)) {
      $target = substr($target, 0, strlen($target) - 1);
    }
    $pDest = $mandantInfo->getFilemount_rootpath() . '/' . $target;
    if (\file_exists($uploadFile->getPathName())) {
      $fileName = $uploadFile->getClientOriginalName();
      if (\file_exists($pDest . '/' . $fileName)) {
        $fileName = time() . '_' . $uploadFile->getClientOriginalName();
      }
      $uploadFile->move($pDest, $fileName);
      $response->setRootFileName($pDest . '/' . $fileName);
      $response->setFileName($target . '/' . $fileName);
    } else {
      throw new AlpdeskCoreFilemanagementException("Src-File not found on server");
    }
  }

  private function downloadFile(string $target, AlpdescCoreBaseMandantInfo $mandantInfo): BinaryFileResponse {
    if ($this->startsWith('/', $target)) {
      $target = substr($target, 1, strlen($target));
    }
    if ($this->endsWith('/', $target)) {
      $target = substr($target, 0, strlen($target) - 1);
    }
    if ($target == null || $target == "") {
      throw new AlpdeskCoreFilemanagementException("No valid target file");
    }
    $pDest = $mandantInfo->getFilemount_rootpath() . '/' . $target;
    if (\file_exists($pDest) && \is_file($pDest)) {
      $response = new BinaryFileResponse($pDest);
      $response->headers->set('Access-Control-Expose-Headers', 'Content-Disposition');
      $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, str_replace('/', '_', $target));
      return $response;
    } else {
      throw new AlpdeskCoreFilemanagementException("src-File not found on server");
    }
  }

  public function upload(UploadedFile $uploadFile, string $target, string $alpdesk_token): AlpdeskCoreFileuploadResponse {
    if ($uploadFile == null || $alpdesk_token == '') {
      throw new AlpdeskCoreFilemanagementException("invalid key-parameters for upload");
    }
    $username = AlpdeskCoreAuthToken::getUsernameFromToken($alpdesk_token);
    $mandantId = $this->verifyAndgetMandant($username, $alpdesk_token);
    $mandantInfo = $this->getMandantInformation($mandantId);
    $response = new AlpdeskCoreFileuploadResponse();
    $this->copyToTarget($uploadFile, $target, $mandantInfo, $response);
    $response->setUsername($username);
    $response->setAlpdesk_token($alpdesk_token);
    $response->setMandantInfo($mandantInfo);
    return $response;
  }

  public function download(string $jwtToken, array $downloadData): BinaryFileResponse {
    $username = AlpdeskCoreAuthToken::getUsernameFromToken($jwtToken);
    if (!\array_key_exists('target', $downloadData)) {
      throw new AlpdeskCoreFilemanagementException("invalid key-parameters for download");
    }
    $target = (string) $downloadData['target'];
    $mandantId = $this->verifyAndgetMandant($username, $jwtToken);
    $mandantInfo = $this->getMandantInformation($mandantId);
    return $this->downloadFile($target, $mandantInfo);
  }

}
