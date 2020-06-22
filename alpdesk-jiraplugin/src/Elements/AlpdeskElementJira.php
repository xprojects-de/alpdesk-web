<?php

declare(strict_types=1);

namespace Alpdesk\AlpdeskJiraPlugin\Elements;

use Alpdesk\AlpdeskCore\Elements\AlpdeskCoreElement;
use Alpdesk\AlpdeskCore\Library\Mandant\AlpdescCoreBaseMandantInfo;
use JiraRestApi\Issue\IssueService;
use JiraRestApi\JiraException;
use JiraRestApi\Configuration\ArrayConfiguration;
use JiraRestApi\Issue\Worklog;
use Alpdesk\AlpdeskCore\Library\Cryption\Cryption;

class AlpdeskElementJira extends AlpdeskCoreElement {

  private ArrayConfiguration $jiraConfig;
  private string $jiraHost = '';
  private string $jiraProject = '';
  private float $jiraMoneyPerHour = 50.00;

  private function getData(array $data, string $statusFilter = ''): array {
    // Status:
    // Done = Fertig
    // Kontrolle = Kontrolle
    // Rechnung = Rechnung
    // To Do = To Do
    // Open = Open
    try {
      $jql = 'project = ' . $this->jiraProject;
      if ($statusFilter != "") {
        $jql .= ' AND status="' . $statusFilter . '"';
      }
      $issueService = new IssueService($this->jiraConfig);
      $ret = $issueService->search($jql, 0, 1000, array('summary', 'issuetype', 'status', 'duedate'));
      if ($ret != null) {
        if ($ret->issues != null) {
          foreach ($ret->issues as $issue) {
            array_push($data['items'], array(
                'id' => $issue->id,
                'key' => $issue->key,
                'summary' => $issue->fields->summary,
                'issuetype' => array(
                    'icon' => $issue->fields->issuetype->iconUrl,
                    'name' => $issue->fields->issuetype->name
                ),
                'status' => array(
                    'name' => $issue->fields->status->name,
                    'key' => $issue->fields->status->statuscategory->key,
                    'color' => $issue->fields->status->statuscategory->colorName,
                ),
                'duedatetime' => strtotime($issue->fields->duedate),
                'duedate' => $issue->fields->duedate,
                'link' => $this->jiraHost . '/browse/' . $issue->key
            ));
          }
          $data['error'] = false;
        }
      }
    } catch (JiraException $e) {
      $data['msg'] = $e->getMessage();
      $data['error'] = true;
    }
    return $data;
  }

  private function getIssue(array $data, string $issueKey): array {
    try {
      $jql = 'issuekey = ' . $issueKey;
      $issueService = new IssueService($this->jiraConfig);
      $ret = $issueService->search($jql, 0, 1000, array('summary', 'issuetype', 'status', 'duedate'));
      if ($ret != null) {
        if ($ret->issues != null) {
          foreach ($ret->issues as $issue) {
            array_push($data['items'], array(
                'id' => $issue->id,
                'key' => $issue->key,
                'summary' => $issue->fields->summary,
                'issuetype' => array(
                    'icon' => $issue->fields->issuetype->iconUrl,
                    'name' => $issue->fields->issuetype->name
                ),
                'status' => array(
                    'name' => $issue->fields->status->name,
                    'key' => $issue->fields->status->statuscategory->key,
                    'color' => $issue->fields->status->statuscategory->colorName,
                ),
                'duedatetime' => strtotime($issue->fields->duedate),
                'duedate' => $issue->fields->duedate,
                'link' => $this->jiraHost . '/browse/' . $issue->key
            ));
          }
          $data['error'] = false;
        }
      }
    } catch (JiraException $e) {
      $data['msg'] = $e->getMessage();
      $data['error'] = true;
    }
    return $data;
  }

  private function getWorklog(array $data, string $issueKey): array {
    try {
      $data = $this->getIssue($data, $issueKey);
      $data['items'][0]['worklogs'] = array();
      $issueService = new IssueService($this->jiraConfig);
      $worklogs = $issueService->getWorklog($issueKey)->getWorklogs();
      if ($worklogs != null) {
        $completeHours = floatval(0);
        $completeprices = floatval(0);
        foreach ($worklogs as $worklog) {
          array_push($data['items'][0]['worklogs'], array(
              'id' => $worklog->id,
              'name' => $worklog->author['name'],
              'displayName' => $worklog->author['displayName'],
              'hours' => number_format(floatval($worklog->timeSpentSeconds / 3600), 2),
              'comment' => $worklog->comment,
              'updatedTime' => strtotime($worklog->updated),
              'updated' => date('Y-m-d', strtotime($worklog->updated))
          ));
          $completeHours += floatval($worklog->timeSpentSeconds / 3600);
          $completeprices += floatval($worklog->timeSpentSeconds / 3600) * floatval($this->jiraMoneyPerHour);
        }
        $data['items'][0]['completeHours'] = number_format($completeHours, 2);
        $data['items'][0]['completeprices'] = number_format($completeprices, 2);
        $data['items'][0]['jiraMoneyPerHour'] = number_format(floatval($this->jiraMoneyPerHour), 2);
        $data['error'] = false;
      }
    } catch (JiraException $e) {
      $data['msg'] = $e->getMessage();
      $data['error'] = true;
    }
    return $data;
  }

  public function execute(AlpdescCoreBaseMandantInfo $mandantInfo, array $data): array {
    $mandantInfoData = $mandantInfo->getAdditionalDatabaseInformation();
    $cryption = new Cryption(true);
    $this->jiraConfig = new ArrayConfiguration(array(
        'jiraHost' => $mandantInfoData['jirahost'],
        'jiraUser' => $mandantInfoData['jirauser'],
        'jiraPassword' => $cryption->safeDecrypt($mandantInfoData['jirapassword']),
        'cookieAuthEnabled' => false
    ));
    $this->jiraHost = (string) $mandantInfoData['jirahost'];
    $this->jiraProject = (string) $mandantInfoData['jiraproject'];
    $this->jiraMoneyPerHour = (float) $mandantInfoData['jiramoneyperhour'];
    $response = array(
        'error' => true,
        'msg' => '',
        'count' => 0,
        'items' => array());
    if (\is_array($data) && \array_key_exists('method', $data) && \array_key_exists('param', $data)) {
      try {
        switch ($data['method']) {
          case 'list':
            $response = $this->getData($response);
            break;
          case 'status':
            $response = $this->getData($response, $data['param']);
            break;
          case 'issue':
            $response = $this->getIssue($response, $data['param']);
            break;
          case 'worklog':
            $response = $this->getWorklog($response, $data['param']);
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
