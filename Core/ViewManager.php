<?php

/*
 * This file is part of the FQTDBCoreManagerBundle package.
 *
 * (c) FOUQUET <https://github.com/hugo082/DBManagerBundle>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Hugo Fouquet <hugo.fouquet@epita.fr>
 */

namespace DB\ManagerBundle\Core;

use function PHPSTORM_META\type;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

use DB\ManagerBundle\Core\View;
use DB\ManagerBundle\DependencyInjection\Configuration as Conf;
use FQT\DBCoreManagerBundle\Core\EntityInfo;
use FQT\DBCoreManagerBundle\Core\Action;
use FQT\DBCoreManagerBundle\Core\Execution as CoreExecution;
use DB\ManagerBundle\Core\Execution;
use FQT\DBCoreManagerBundle\Checker\ActionManager;

class ViewManager
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var array
     */
    private $views;

    /**
     * @var array
     */
    private $templates;

    /**
     * @var ActionManager
     */
    private $actionManager;

    public function __construct(Container $container, array $views, array $templates) {
        $this->views = self::arrayToViewsMetaData($views);
        $this->templates = $templates;
        $this->container = $container;
    }

    /**
     * Process main action
     * @param Request $request
     * @param $actionID
     * @param $name
     * @param $id
     * @return Execution
     */
    public function processAction(Request $request, $actionID, $name, $id) {
        $this->actionManager = $this->container->get('fqt.dbcm.manager.action');
        if ($actionID == null)
            return $this->actionManager->indexAction();
        $execution = new Execution($this->actionManager->customAction($request, $actionID, $name, $id));
        $this->loadViews($execution, $request, $id);
        return $execution;
    }

    /**
     * Load and process container views
     * @param Execution $execution
     * @param Request $request
     * @param $id
     */
    public function loadViews(Execution $execution, Request $request, $id) {
        $this->computeMainActionView($execution);
        if ($execution->isDisplayable()) {
            foreach ($execution->getContainer() as $childActionID) {
                $childAction = $execution->entityInfo->getActionWithID($childActionID, true);
                if (!$childAction->isFullAuthorize())
                    continue;
                $data = $this->actionManager->processAction($request, $childAction, $id);
                $viewMeta = $this->getViewMetaDataForAction($childAction, true);
                $execution->pushView(new View($childAction, $viewMeta, $data));
            }
        }
    }

    /**
     * Compute execution $execution of main action and create view if necessary
     * @param Execution $execution
     * @return ViewMetaData|null
     */
    public function computeMainActionView(Execution $execution) {
        $viewMeta = $this->getViewMetaDataForAction($execution->mainView->getAction(), true);
        if ($viewMeta->getView() != null)
            $execution->pushMainView($viewMeta);
        return $viewMeta;
    }

    /**
     * Get the view information for action $action
     * @param Action $action
     * @param bool $throw
     * @return ViewMetaData|null
     * @throws \Exception
     */
    public function getViewMetaDataForAction(Action $action, bool $throw = false) {
        if (!key_exists($action->id, $this->views)) {
            if ($action->isDefault)
                return Conf::getDefaultViewInfoForAction($action);
            if ($throw)
                throw new \Exception("Impossible to find view information for " . $action->fullName);
            return null;
        }
        return $this->views[$action->id];
    }

    /**
     * Get the template of main or index
     * @param bool $isIndex
     * @return mixed
     */
    public function getTemplate(bool $isIndex) {
        if ($isIndex)
            return $this->templates["index"];
        return $this->templates["main"];
    }

    public static function arrayToViewsMetaData(array $data) {
        $viewsMeta = array();
        foreach ($data as $dataViewMeta) {
            $viewsMeta[$dataViewMeta["action"]] = new ViewMetaData($dataViewMeta);
        }
        return $viewsMeta;
    }

    // TODO : indexView Action (default list)
    // TODO : getGlobalAction - getObjectAction
    // TODO : Link Global Action - Execute Global Action (db_manager.links - db_manager.views)
    // TODO : Tester les action par defaut
}