<?php

declare(strict_types=1);

namespace Alpdesk\AlpdeskCore\Controller\Mandant;

use Contao\CoreBundle\Framework\ContaoFramework;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Alpdesk\AlpdeskCore\Library\Mandant\AlpdeskCoreMandant;
use Alpdesk\AlpdeskCore\Library\Exceptions\AlpdeskCoreMandantException;
use Alpdesk\AlpdeskCore\Library\Constants\AlpdeskCoreConstants;
use Alpdesk\AlpdeskCore\Events\AlpdeskCoreEventService;
use Alpdesk\AlpdeskCore\Library\Mandant\AlpdeskCoreMandantResponse;
use Alpdesk\AlpdeskCore\Events\Event\AlpdeskCoreMandantListEvent;
use Alpdesk\AlpdeskCore\Events\Event\AlpdeskCoreMandantEditEvent;
use Alpdesk\AlpdeskCore\Jwt\AuthorizationHeaderBearerTokenExtractor;
use Alpdesk\AlpdeskCore\Logging\AlpdeskcoreLogger;

class AlpdeskCoreMandantController extends Controller {

  protected ContaoFramework $framework;
  protected AlpdeskCoreEventService $eventService;
  protected AlpdeskcoreLogger $logger;

  public function __construct(ContaoFramework $framework, AlpdeskCoreEventService $eventService, AlpdeskcoreLogger $logger) {
    $this->framework = $framework;
    $this->framework->initialize();
    $this->eventService = $eventService;
    $this->logger = $logger;
  }

  private function output(AlpdeskCoreMandantResponse $data, int $statusCode): JsonResponse {
    return ( new JsonResponse(array(
                'username' => $data->getUsername(),
                'alpdesk_token' => $data->getAlpdesk_token(),
                'mandantId' => $data->getMandantId(),
                'plugins' => $data->getPlugins(),
                'data' => $data->getData(),
                    ), $statusCode
            ) );
  }

  private function outputError(string $data, int $statusCode): JsonResponse {
    return (new JsonResponse($data, $statusCode));
  }

  /**
   * 
   * @param Request $request 
   * ENDPOINT: /mandant
   * Authorization Bearer TOKEN in Header
   * 
   * @return JsonResponse
   * {"username":"test","alpdesk_token":"alpdesk_test_1591436687_651089","mandantId":"1","plugins":[],"data":[]} with AlpdeskCoreConstants::$STATUSCODE_OK
   * OR ErrorMessage with AlpdeskCoreConstants::$STATUSCODE_COMMONERROR
   * 
   */
  public function list(Request $request): JsonResponse {
    try {
      $jwtToken = AuthorizationHeaderBearerTokenExtractor::extract($request);
      $response = (new AlpdeskCoreMandant())->list($jwtToken);
      $event = new AlpdeskCoreMandantListEvent($response);
      $this->eventService->getDispatcher()->dispatch($event, AlpdeskCoreMandantListEvent::NAME);
      $this->logger->info('username:' . $event->getResultData()->getUsername() . ' | MandantList successfully', __METHOD__);
      return $this->output($event->getResultData(), AlpdeskCoreConstants::$STATUSCODE_OK);
    } catch (\Exception |  AlpdeskCoreMandantException $exception) {
      $this->logger->error($exception->getMessage(), __METHOD__);
      return $this->outputError($exception->getMessage(), AlpdeskCoreConstants::$STATUSCODE_COMMONERROR);
    }
  }

  /**
   * 
   * @param Request $request 
   * ENDPOINT: /mandant/edit
   * POST-JSON-PARAMS: {"username":"test","alpdesk_token":"alpdesk_test_1591436687_651089","data":[{"email":"infos@x-projects.dee"}]}
   * 
   * @return JsonResponse
   * {"username":"test","alpdesk_token":"alpdesk_test_1591436687_651089","mandantId":"1","plugins":[],"data":[]} with AlpdeskCoreConstants::$STATUSCODE_OK
   * OR ErrorMessage with AlpdeskCoreConstants::$STATUSCODE_COMMONERROR
   * 
   */
  public function edit(Request $request): JsonResponse {
    return $this->outputError('Not Supported', AlpdeskCoreConstants::$STATUSCODE_COMMONERROR);
    /* try {
      $mandantdata = (array) json_decode($request->getContent(), true);
      $response = (new AlpdeskCoreMandant())->edit($mandantdata);
      $event = new AlpdeskCoreMandantEditEvent($response);
      $this->eventService->getDispatcher()->dispatch($event, AlpdeskCoreMandantEditEvent::NAME);
      return $this->output($event->getResultData(), AlpdeskCoreConstants::$STATUSCODE_OK);
      } catch (AlpdeskCoreMandantException $exception) {
      $this->logger->error($exception->getMessage(), __METHOD__);
      return $this->outputError($exception->getMessage(), AlpdeskCoreConstants::$STATUSCODE_COMMONERROR);
      } */
  }

}
