<?php

declare(strict_types=1);

namespace Alpdesk\AlpdeskCustomerPlugin\Library;

use Contao\Environment;
use Contao\StringUtil;
use Contao\FrontendTemplate;
use Alpdesk\AlpdeskCore\Model\Database\AlpdeskcoreDatabasemanagerModel;
use Doctrine\DBAL\Connection;

class AlpdeskCustomerPluginDetail {

  private ?Connection $dbConnection;

  private function getCustomerData(int $id): array {
    $queryBuilder = $this->dbConnection->createQueryBuilder();
    $queryBuilder->select('*')->from('tl_xpw_kunden')->where('id=?')->setParameter(0, intval($id));
    return $queryBuilder->execute()->fetchAll();
  }

  private function getProjectData(int $id): array {
    $queryBuilder = $this->dbConnection->createQueryBuilder();
    $queryBuilder->select('*')->from('tl_xpw_projekte')->where('id=?')->setParameter(0, intval($id));
    return $queryBuilder->execute()->fetchAll();
  }

  private function getProjectsDataByPid(int $pid): array {
    $queryBuilder = $this->dbConnection->createQueryBuilder();
    $queryBuilder->select('*')->from('tl_xpw_projekte')->where('pid=?')->setParameter(0, intval($pid))->orderBy('title', 'ASC');
    return $queryBuilder->execute()->fetchAll();
  }

  private function updateProject(array $params) {
    $queryBuilder = $this->dbConnection->createQueryBuilder();
    $queryBuilder->update('tl_xpw_projekte', 'p')
            ->set('p.title', '?')
            ->set('p.domain', '?')
            ->set('p.ftp', '?')
            ->set('p.datenbank', '?')
            ->set('p.beschreibung', '?')
            ->where('p.id=?')
            ->setParameter(0, StringUtil::convertEncoding($params['title'], 'UTF-8'))
            ->setParameter(1, StringUtil::convertEncoding($params['domain'], 'UTF-8'))
            ->setParameter(2, StringUtil::convertEncoding($params['ftp'], 'UTF-8'))
            ->setParameter(3, StringUtil::convertEncoding($params['datenbank'], 'UTF-8'))
            ->setParameter(4, StringUtil::convertEncoding($params['beschreibung'], 'UTF-8'))
            ->setParameter(5, intval($params['id'])
    );
    $queryBuilder->execute();
  }

  private function createProject(array $params) {
    $queryBuilder = $this->dbConnection->createQueryBuilder();
    $queryBuilder->insert('tl_xpw_projekte')
            ->setValue('title', '?')
            ->setValue('domain', '?')
            ->setValue('ftp', '?')
            ->setValue('datenbank', '?')
            ->setValue('beschreibung', '?')
            ->setValue('pid', '?')
            ->setParameter(0, StringUtil::convertEncoding($params['title'], 'UTF-8'))
            ->setParameter(1, StringUtil::convertEncoding($params['domain'], 'UTF-8'))
            ->setParameter(2, StringUtil::convertEncoding($params['ftp'], 'UTF-8'))
            ->setParameter(3, StringUtil::convertEncoding($params['datenbank'], 'UTF-8'))
            ->setParameter(4, StringUtil::convertEncoding($params['beschreibung'], 'UTF-8'))
            ->setParameter(5, intval($params['id']));
    $queryBuilder->execute();
  }

  private function deleteProject(int $id) {
    $queryBuilder = $this->dbConnection->createQueryBuilder();
    $queryBuilder->delete('tl_xpw_projekte')
            ->where('id=?')
            ->setParameter(0, intval($id));
    $queryBuilder->execute();
  }

  private function getData(array $params): string {
    if ($this->dbConnection == null) {
      throw new \Exception('Error connecting to Database');
    }
    if (!\array_key_exists('id', $params)) {
      throw new \Exception('invalid params in paramsarray');
    }

    if (\array_key_exists('type', $params)) {
      if ($params['type'] == 'edit') {
        $template = new FrontendTemplate('alpdeskcustomerplugin_edit_project');
        $template->title = 'Customer Edit';
        $template->data = $this->getProjectData(intval($params['id']));
        return $template->parse();
      } else if ($params['type'] == 'editsave') {
        $this->updateProject($params);
        $projectdataUpdate = $this->getProjectData(intval($params['id']));
        $customerdata = $this->getCustomerData(intval($projectdataUpdate[0]['pid']));
        $projectdata = $this->getProjectsDataByPid(intval($projectdataUpdate[0]['pid']));
        $template = new FrontendTemplate('alpdeskcustomerplugin_detail');
        $template->title = 'Customerdetail';
        $template->data = array(
            'customerdata' => $customerdata[0],
            'projectdata' => $projectdata
        );
        return $template->parse();
      } else if ($params['type'] == 'new') {
        $template = new FrontendTemplate('alpdeskcustomerplugin_new_project');
        $template->title = 'New Customer';
        $template->data = intval($params['id']);
        return $template->parse();
      } else if ($params['type'] == 'createsave') {
        $this->createProject($params);
        $customerdata = $this->getCustomerData(intval($params['id']));
        $projectdata = $this->getProjectsDataByPid(intval($params['id']));
        $template = new FrontendTemplate('alpdeskcustomerplugin_detail');
        $template->title = 'Customerdetail';
        $template->data = array(
            'customerdata' => $customerdata[0],
            'projectdata' => $projectdata
        );
        return $template->parse();
      } else if ($params['type'] == 'delete') {
        $projectdataDelete = $this->getProjectData(intval($params['id']));
        $this->deleteProject(intval($params['id']));
        $customerdata = $this->getCustomerData(intval($projectdataDelete[0]['pid']));
        $projectdata = $this->getProjectsDataByPid(intval($projectdataDelete[0]['pid']));
        $template = new FrontendTemplate('alpdeskcustomerplugin_detail');
        $template->title = 'Customerdetail';
        $template->data = array(
            'customerdata' => $customerdata[0],
            'projectdata' => $projectdata
        );
        return $template->parse();
      }
    } else {
      $customerdata = $this->getCustomerData(intval($params['id']));
      $projectdata = $this->getProjectsDataByPid(intval($params['id']));
      $template = new FrontendTemplate('alpdeskcustomerplugin_detail');
      $template->title = 'Customerdetail';
      $template->data = array(
          'customerdata' => $customerdata[0],
          'projectdata' => $projectdata
      );
      return $template->parse();
    }
    return '';
  }

  public function render(int $customerdb, array $params): array {
    $returnValue = 'error loading Data';
    try {
      $this->dbConnection = AlpdeskcoreDatabasemanagerModel::connectionById(intval($customerdb));
      $returnValue = $this->getData($params);
    } catch (\Exception $ex) {
      $returnValue = $ex->getMessage();
    }
    return array(
        'ngContent' => $returnValue,
        'ngStylesheetUrl' => array(
            0 => Environment::get('base') . 'bundles/alpdeskcustomerplugin/detail/customerdetail.css'
        ),
        'ngScriptUrl' => array(
            0 => Environment::get('base') . 'assets/jquery/js/jquery.js',
            1 => Environment::get('base') . 'bundles/alpdeskcustomerplugin/detail/customerdetail.js'
        )
    );
  }

}
