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
use FQT\DBCoreManagerBundle\Checker\ActionManager;

class ManageController extends Controller
{
    public function indexAction()
    {
        /** @var $checker Checker */
        $checker = $this->get('fqt.dbcm.checker');
        $entities = $checker->getEntities();

        return $this->render($checker->getSettings()['indexView'], array(
            'entities' => $entities
        ));
    }

    public function processAction(Request$request, $method, $name, $id) {
        /** @var $checker ActionManager */
        $actionManager = $this->get('fqt.dbcm.manager.action');
        $execution = $actionManager->customAction($request, $method, $name, $id);

        if ($execution["data"] != null && key_exists('flash', $execution["data"])) {
            foreach ($execution["data"]["flash"] as $flash)
                $this->addFlash($flash["type"], $flash["message"]);
        }

        return $this->redirectToRoute('db.manager.list', array('name' => $name));
    }

    public function listAction(Request $request, $name)
    {
        /** @var $checker ActionManager */
        $actionManager = $this->get('fqt.dbcm.manager.action');
        $execution = $actionManager->listAction($name);

        var_dump($execution["entityInfo"]);
        $execution["entityInfo"]["displayElements"] = $this->getDisplayPermissions($execution["entityInfo"], "null");

        $form = null;
        if ($execution["entityInfo"]['displayElements'][Conf::DISP_ELEM_FORM]['full']) {
            $process = $actionManager->processAddForm($request, $execution["entityInfo"]);
            if ($process["success"])
                return $this->redirectToRoute('db.manager.list', array('name' => $name));
            $form = $process["form"]->createView();
        }

        return $this->render($execution["entityInfo"]['mainView'], array(
            'name' => $name,
            'eInfo' => $execution["entityInfo"],
            'all' => $execution["data"],
            'form' => $form,
            'action' => array( 'name' => Conf::PERM_ADD, 'formType' => Conf::PERM_ADD)
        ));
    }

    public function addAction(Request $request, $name)
    {
        /** @var $checker ActionManager */
        $actionManager = $this->get('fqt.dbcm.manager.action');
        $execution = $actionManager->addAction($request, $name);
        if ($execution["success"])
            return $this->redirectToRoute('db.manager.list', array('name' => $name));

        return $this->render($execution["entityInfo"]['mainView'], array(
            'name' => $name,
            'eInfo' => $execution["entityInfo"],
            'all' => $execution["data"],
            'form' => $execution["form"]->createView(),
            'action' => array( 'name' => Conf::PERM_ADD, 'formType' => Conf::PERM_ADD)
        ));
    }

    public function editAction(Request $request, $name, $id)
    {
        /** @var $checker ActionManager */
        $actionManager = $this->get('fqt.dbcm.manager.action');
        $execution = $actionManager->editAction($request, $name, $id);

        if ($execution == NULL) {
            $this->addFlash('danger','Entity not found');
            return $this->redirectToRoute('db.manager.list', array('name' => $name));
        }

        return $this->render($execution["entityInfo"]['mainView'], array(
            'name' => $name,
            'eInfo' => $execution["entityInfo"],
            'all' => $execution["data"],
            'form' => $execution["form"]->createView(),
            'action' => array( 'name' => Conf::PERM_ADD, 'formType' => Conf::PERM_ADD)
        ));
    }

    public function removeAction($name, $id)
    {
        /** @var $checker ActionManager */
        $actionManager = $this->get('fqt.dbcm.manager.action');
        $execution = $actionManager->removeAction($name, $id);
        if ($execution["success"]) {
            $event = $execution["data"];
            $this->addFlash($event->getFlashTitle(), $event->getFlashMessage());
        }
        else
            $this->addFlash('danger','Your entity could not be deleted');
        return $this->redirectToRoute('db.manager.list', array('name' => $name));
    }


    /**
     * Compute current displayable elements for entity / user / action
     * WARNING : don't check $eInfo['permissions'][$action]
     * @param array $entity
     * @param string $action
     * @return array
     */
    private function getDisplayPermissions(array $entity, string $action) {
        #$formAction = ($action == Conf::PERM_LIST) ? Conf::PERM_ADD : $action;
        return array(
            Conf::DISP_ELEM_FORM => array(
                'full' => true, #$full = $this->settings[$action][Conf::DISP_ELEM_FORM] && $this->entityAccess($entity, $formAction),
                Conf::DISP_ELEM_ADDLINK => true, #!$full and $entity['permissions'][$formAction] && $formAction != $action && $this->entityAccess($entity, $formAction)
            ),
            Conf::DISP_ELEM_LIST => array(
                'full' => true, #$full = $this->settings[$action][Conf::DISP_ELEM_LIST] and $this->entityAccess($entity, Conf::PERM_LIST),
                Conf::DISP_ELEM_EDITLINK => true, #$full and $entity['permissions'][Conf::PERM_EDIT] and $this->entityAccess($entity, Conf::PERM_EDIT),
                Conf::DISP_ELEM_REMOVELINK => true, #$full and $entity['permissions'][Conf::PERM_REMOVE] and $this->entityAccess($entity, Conf::PERM_REMOVE)
            )
        );
    }
}
