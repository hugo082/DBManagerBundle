<?php

/*
 * This file is part of the DBManagerBundle package.
 *
 * (c) FOUQUET <https://github.com/hugo082/DBManagerBundle>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Hugo Fouquet <hugo.fouquet@epita.fr>
 */

namespace DB\ManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as Dispatcher;

use FQT\DBCoreManagerBundle\FQTDBCoreManagerEvents as DBCMEvents;
use FQT\DBCoreManagerBundle\DependencyInjection\Configuration as Conf;
use FQT\DBCoreManagerBundle\Event\ActionEvent;
use FQT\DBCoreManagerBundle\Checker\EntityManager as Checker;
use DB\ManagerBundle\Core\View;
use DB\ManagerBundle\Core\ViewManager;

class ManageController extends Controller
{
    public function indexAction(Request $request)
    {
        /** @var $viewManager ViewManager */
        $viewManager = $this->get('fqt.db.manager.view.manager');
        $entities = $viewManager->processAction($request, null, null, null);

        return $this->render($viewManager->getTemplate(true), array(
            'entities' => $entities
        ));
    }

    public function processAction(Request $request, $actionID, $name, $id) {
        /** @var $viewManager ViewManager */
        $viewManager = $this->get('fqt.db.manager.view.manager');
        $execution = $viewManager->processAction($request, $actionID, $name, $id);

        foreach ($execution->getFlash() as $flash)
                $this->addFlash($flash["type"], $flash["message"]);

        if ($execution->getRedirection() != null)
            return $this->redirectToRoute($execution->getRedirection()["route_name"], $execution->getRedirection()["data"]);

        return $this->render($viewManager->getTemplate(false), array(
            'name' => $name,
            'views' => $execution->views,
            'links' => $execution->links,
            'eInfo' => $execution->entityInfo
        ));
    }

    public function listAction(Request $request, $name)
    {
        return $this->processAction($request, Conf::DEF_LIST, $name, null);
    }


    /**
     * Return value of key or null if doesn't exist
     * @param $key
     * @param $array
     * @return null
     */
    private function getKeySecure($key, $array) {
        if (key_exists($key, $array))
            return $array[$key];
        return null;
    }
}
