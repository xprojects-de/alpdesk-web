<?php

declare(strict_types=1);

namespace Alpdesk\AlpdeskFinderPlugin\Elements;

use Alpdesk\AlpdeskCore\Elements\AlpdeskCoreElement;
use Alpdesk\AlpdeskCore\Library\Mandant\AlpdescCoreBaseMandantInfo;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;

class AlpdeskElementFinder extends AlpdeskCoreElement {

  private AlpdescCoreBaseMandantInfo $mandantInfo;

  private function getContentOfDir($dir) {
    $data = array();
    $finder = new Finder();
    $finder->in($dir)->sortByType();
    if ($finder->hasResults()) {
      foreach ($finder as $object) {
        $parent = $object->getRelativePath();
        if ($parent == '') {
          $parent = 'root';
        }
        array_push($data, array(
            'id' => base64_encode($object->getRelativePathname()),
            'name' => $object->getFilename(),
            'isFolder' => ($object->isFile() == false),
            'parent' => ($parent == 'root' ? $parent : base64_encode($parent))
        ));
      }
    }
    return $data;
  }

  private function list(array $response): array {
    $data = $response;
    $data['items'] = $this->getContentOfDir($this->mandantInfo->getFilemount_rootpath());
    $data['error'] = false;
    return $data;
  }

  private function createFolder(array $response, array $request): array {
    $data = $response;
    if (!\array_key_exists('name', $request) || !\array_key_exists('parent', $request)) {
      return $data;
    }
    $name = $request['name'];
    $parent = $request['parent'];
    $filesystem = new Filesystem();
    try {
      $pDir = '';
      if ($parent != 'root') {
        $pDir = base64_decode($parent) . '/';
      }
      if (!$filesystem->exists($this->mandantInfo->getFilemount_rootpath() . '/' . $pDir . $name)) {
        $filesystem->mkdir($this->mandantInfo->getFilemount_rootpath() . '/' . $pDir . $name);
      }
      $data['error'] = false;
    } catch (IOExceptionInterface $exception) {
      $data['msg'] = "An error occurred while creating your directory at " . $exception->getPath();
    }
    return $data;
  }

  private function deleteFolder(array $response, array $request): array {
    $data = $response;
    if (!\array_key_exists('name', $request)) {
      return $data;
    }
    $name = $request['name'];
    $filesystem = new Filesystem();
    try {
      $pDir = base64_decode($name);
      if ($filesystem->exists($this->mandantInfo->getFilemount_rootpath() . '/' . $pDir)) {
        $filesystem->remove($this->mandantInfo->getFilemount_rootpath() . '/' . $pDir);
      }
      $data['error'] = false;
    } catch (IOExceptionInterface $exception) {
      $data['msg'] = "An error occurred while creating your directory at " . $exception->getPath();
    }
    return $data;
  }

  private function renameFolder(array $response, array $request): array {
    $data = $response;
    if (!\array_key_exists('name', $request) || !\array_key_exists('oldname', $request) || !\array_key_exists('newname', $request)) {
      return $data;
    }
    $name = $request['name'];
    $oldname = $request['oldname'];
    $newname = $request['newname'];
    $filesystem = new Filesystem();
    try {
      $pDir = base64_decode($name);
      $pDir_new = str_replace($oldname, $newname, $this->mandantInfo->getFilemount_rootpath() . '/' . $pDir);
      $data['msg'] = $pDir_new;
      if ($filesystem->exists($this->mandantInfo->getFilemount_rootpath() . '/' . $pDir)) {
        $filesystem->rename($this->mandantInfo->getFilemount_rootpath() . '/' . $pDir, $pDir_new);
      }
      $data['error'] = false;
    } catch (IOExceptionInterface $exception) {
      $data['msg'] = "An error occurred while creating your directory at " . $exception->getPath();
    }
    return $data;
  }

  private function move(array $response, array $request): array {
    $data = $response;
    if (!\array_key_exists('name', $request) || !\array_key_exists('destination', $request)) {
      return $data;
    }
    $name = $request['name'];
    $dest = $request['destination'];
    $filesystem = new Filesystem();
    try {
      $pName = base64_decode($name);
      $pDest = base64_decode($dest);
      if ($filesystem->exists($this->mandantInfo->getFilemount_rootpath() . '/' . $pName)) {
        if (is_file($this->mandantInfo->getFilemount_rootpath() . '/' . $pName)) {
          $path_parts = pathinfo($this->mandantInfo->getFilemount_rootpath() . '/' . $pName);
          $newFile = $this->mandantInfo->getFilemount_rootpath() . '/' . $pDest . '/' . $path_parts['basename'];
          if ($this->mandantInfo->getFilemount_rootpath() . '/' . $pName != $newFile) {
            $filesystem->copy($this->mandantInfo->getFilemount_rootpath() . '/' . $pName, $newFile, true);
            $data['msg'] = $pDest . '/' . $path_parts['basename'];
            $valid = true;
          }
        } else if (is_dir($this->mandantInfo->getFilemount_rootpath() . '/' . $pName)) {
          $dirname = basename($this->mandantInfo->getFilemount_rootpath() . '/' . $pName);
          if ($this->mandantInfo->getFilemount_rootpath() . '/' . $pName != $this->mandantInfo->getFilemount_rootpath() . '/' . $pDest . '/' . $dirname) {
            $filesystem->mirror($this->mandantInfo->getFilemount_rootpath() . '/' . $pName, $this->mandantInfo->getFilemount_rootpath() . '/' . $pDest . '/' . $dirname);
            $data['msg'] = $pDest . '/' . $dirname;
            $valid = true;
          }
        }
        if ($valid) {
          $filesystem->remove($this->mandantInfo->getFilemount_rootpath() . '/' . $pName);
          $data['error'] = false;
        }
      } else {
        $data['msg'] = "src-File does not exists";
      }
    } catch (Exception $exception) {
      $data['msg'] = "An error occurred";
    }
    return $data;
  }

  public function decodeHash(array $response, array $request): array {
    $data = $response;
    if (!\array_key_exists('name', $request)) {
      return $data;
    }
    $name = base64_decode($request['name']);
    $data['error'] = false;
    $data['msg'] = $name;
    return $data;
  }

  /**
   * 
   * @param AlpdescCoreBaseMandantInfo $mandantInfo
   * @param array $data
   * - list "data":{"method":"list"}
   * - createFolder "data":{"method":"createFolder","name":"test1","parent":"VGVzdA=="}
   * - deleteFolder "data":{"method":"deleteFolder","name":"VGVzdC90ZXN0Mg=="}
   * - renameFolder "data":{"method":"renameFolder","name":"VGVzdC90ZXN0MQ==","oldname":"test1","newname":"test123"}
   * - move "data":{"method":"move","name":"VGVzdC90ZXN0MTIz","destination":"VGVzdC9TdWI="}
   * - decodeHash "data":{"method":"decodeHash","name":"VGVzdC90ZXN0MTIz"}
   * @return array
   */
  public function execute(AlpdescCoreBaseMandantInfo $mandantInfo, array $data): array {
    $this->mandantInfo = $mandantInfo;
    $response = array(
        'error' => true,
        'msg' => '',
        'count' => 0,
        'items' => array());
    if (\is_array($data) && \array_key_exists('method', $data)) {
      switch ($data['method']) {
        case 'list':
          $response = $this->list($response);
          break;
        case 'createFolder':
          $response = $this->createFolder($response, $data);
          break;
        case 'deleteFolder':
          $response = $this->deleteFolder($response, $data);
          break;
        case 'renameFolder':
          $response = $this->renameFolder($response, $data);
          break;
        case 'move':
          $response = $this->move($response, $data);
          break;
        case 'decodeHash':
          $response = $this->decodeHash($response, $data);
          break;
        default:
          break;
      }
    }
    return $response;
  }

}
