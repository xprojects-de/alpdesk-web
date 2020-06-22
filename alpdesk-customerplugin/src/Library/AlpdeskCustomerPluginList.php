<?php

declare(strict_types=1);

namespace Alpdesk\AlpdeskCustomerPlugin\Library;

use Contao\Environment;
use Contao\FrontendTemplate;
use Alpdesk\AlpdeskCore\Model\Database\AlpdeskcoreDatabasemanagerModel;
use Doctrine\DBAL\Connection;

class AlpdeskCustomerPluginList {

  private ?Connection $dbConnection;

  private function createCustomer(array $params) {
    $queryBuilder = $this->dbConnection->createQueryBuilder();
    $queryBuilder->insert('tl_xpw_kunden')
            ->setValue('firma', '?')
            ->setValue('name', '?')
            ->setValue('email', '?')
            ->setValue('telefon', '?')
            ->setValue('strasse', '?')
            ->setValue('ort', '?')
            ->setParameter(0, utf8_decode($params['firma']))
            ->setParameter(1, utf8_decode($params['name']))
            ->setParameter(2, utf8_decode($params['email']))
            ->setParameter(3, utf8_decode($params['telefon']))
            ->setParameter(4, utf8_decode($params['strasse']))
            ->setParameter(5, utf8_decode($params['ort']));
    $queryBuilder->execute();
  }

  private function updateCustomer(array $params) {
    $queryBuilder = $this->dbConnection->createQueryBuilder();
    $queryBuilder->update('tl_xpw_kunden', 'p')
            ->set('p.firma', '?')
            ->set('p.name', '?')
            ->set('p.email', '?')
            ->set('p.telefon', '?')
            ->set('p.strasse', '?')
            ->set('p.ort', '?')
            ->where('p.id=?')
            ->setParameter(0, utf8_decode($params['firma']))
            ->setParameter(1, utf8_decode($params['name']))
            ->setParameter(2, utf8_decode($params['email']))
            ->setParameter(3, utf8_decode($params['telefon']))
            ->setParameter(4, utf8_decode($params['strasse']))
            ->setParameter(5, utf8_decode($params['ort']))
            ->setParameter(6, intval($params['id'])
    );
    $queryBuilder->execute();
  }

  private function getCustomerData(): array {
    $queryBuilder = $this->dbConnection->createQueryBuilder();
    $queryBuilder->select('*')->from('tl_xpw_kunden')->orderBy('firma', 'ASC');
    return $queryBuilder->execute()->fetchAll();
  }

  private function deleteCustomer(int $id) {
    $queryBuilder = $this->dbConnection->createQueryBuilder();
    $queryBuilder->delete('tl_xpw_kunden')
            ->where('id=?')
            ->setParameter(0, intval($id));
    $queryBuilder->execute();
    // Also delete Projects
    $queryBuilderP = $this->dbConnection->createQueryBuilder();
    $queryBuilderP->delete('tl_xpw_projekte')
            ->where('pid=?')
            ->setParameter(0, intval($id));
    $queryBuilderP->execute();
  }

  private function getData(array $params): string {
    if ($this->dbConnection == null) {
      throw new \Exception('Error connecting to Database');
    }
    if ($params['type'] == 'newCustomer') {
      $template = new FrontendTemplate('alpdeskcustomerplugin_new_customer');
      $template->title = 'New Customer';
      return $template->parse();
    } else if ($params['type'] == 'createCustomer') {
      $this->createCustomer($params);
      $template = new FrontendTemplate('alpdeskcustomerplugin_list');
      $template->title = 'Customer';
      $template->data = $this->getCustomerData();
      return $template->parse();
    } else if ($params['type'] == 'deleteCustomer') {
      $this->deleteCustomer(intval($params['id']));
      $template = new FrontendTemplate('alpdeskcustomerplugin_list');
      $template->title = 'Customer';
      $template->data = $this->getCustomerData();
      return $template->parse();
    } else if ($params['type'] == 'editCustomer') {
      $template = new FrontendTemplate('alpdeskcustomerplugin_edit_customer');
      $template->title = 'Customer Edit';
      $template->data = $this->getCustomerData(intval($params['id']));
      return $template->parse();
    } else if ($params['type'] == 'editsaveCustomer') {
      $this->updateCustomer($params);
      $template = new FrontendTemplate('alpdeskcustomerplugin_list');
      $template->title = 'Customer';
      $template->data = $this->getCustomerData();
      return $template->parse();
    }
    $template = new FrontendTemplate('alpdeskcustomerplugin_list');
    $template->title = 'Customer';
    $template->data = $this->getCustomerData();
    return $template->parse();
  }

  public function render(int $customerdb, array $params): array {
    $returnValue = 'error loading Data';
    try {
      $databaseModel = new AlpdeskcoreDatabasemanagerModel();
      $this->dbConnection = $databaseModel->connectionById(intval($customerdb));
      $returnValue = $this->getData($params);
    } catch (\Exception $ex) {
      $returnValue = $ex->getMessage();
    }
    return array(
        'ngContent' => $returnValue,
        'ngStylesheetUrl' => array(
            0 => Environment::get('base') . 'bundles/alpdeskcustomerplugin/list/customerlist.css'
        ),
        'ngScriptUrl' => array(
            0 => Environment::get('base') . 'assets/jquery/js/jquery.js',
            1 => Environment::get('base') . 'bundles/alpdeskcustomerplugin/list/customerlist.js'
        )
    );
  }

}
