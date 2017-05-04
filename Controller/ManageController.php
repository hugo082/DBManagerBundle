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
use FQT\DBCoreManagerBundle\DependencyInjection\Configuration;
use FQT\DBCoreManagerBundle\Event\ActionEvent;
use FQT\DBCoreManagerBundle\Checker\EntityManager as Checker;

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

    public function listAction(Request $request, $name)
    {
        /** @var $dispatcher Dispatcher */
        $dispatcher = $this->get('event_dispatcher');
        /** @var $checker Checker */
        $checker = $this->get('fqt.dbcm.checker');
        $eInfo = $checker->getEntity($name, Configuration::PERM_LIST);

        $e = new $eInfo['fullPath']();
        $all = $checker->getEntityObject($eInfo);

        $form = NULL;
        if ($eInfo['displayElements'][Configuration::DISP_ELEM_FORM]['full']) {
            $form = $this->createForm($eInfo['fullFormType'], $e);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                return $this->addFormReception($eInfo, $e, $dispatcher, $name);
            }
            $form = $form->createView();
        }

        return $this->render($eInfo['mainView'], array(
            'name' => $name,
            'eInfo' => $eInfo,
            'all' => $all,
            'form' => $form,
            'action' => array( 'name' => Configuration::PERM_ADD, 'formType' => Configuration::PERM_ADD)
        ));
    }

    public function addAction(Request $request, $name)
    {
        /** @var $dispatcher Dispatcher */
        $dispatcher = $this->get('event_dispatcher');
        /** @var $checker Checker */
        $checker = $this->get('fqt.dbcm.checker');
        $eInfo = $checker->getEntity($name, Configuration::PERM_ADD);
        $checker->checkObjectPermission($eInfo, NULL, Configuration::PERM_ADD);

        $e = new $eInfo['fullPath']();
        $form = $this->createForm($eInfo['fullFormType'], $e);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->addFormReception($eInfo, $e, $dispatcher, $name);
        }

        return $this->render($eInfo['mainView'], array(
            'name' => $name,
            'eInfo' => $eInfo,
            'all' => NULL,
            'form' => $form->createView(),
            'action' => array( 'name' => Configuration::PERM_ADD, 'formType' => Configuration::PERM_ADD)
        ));
    }

    public function editAction(Request $request, $name, $id)
    {
        /** @var $dispatcher Dispatcher */
        $dispatcher = $this->get('event_dispatcher');
        /** @var $checker Checker */
        $checker = $this->get('fqt.dbcm.checker');
        $eInfo = $checker->getEntity($name, Configuration::PERM_EDIT);

        $all = $checker->getEntityObject($eInfo);
        $e = $checker->getEntityObject($eInfo, Configuration::PERM_EDIT, $id);
        if (!$e) {
            $this->addFlash('danger','Entity not found');
            return $this->redirectToRoute('db.manager.list', array('name' => $name));
        }
        $form = $this->createForm($eInfo['fullFormType'], $e);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $event = new ActionEvent($eInfo, $e, array('success', 'Your entity have been updated'));
            $dispatcher->dispatch(DBCMEvents::ACTION_EDIT_BEFORE, $event);

            if (!$event->isExecuted()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($e);
                $em->flush();
            }
            $this->addFlash($event->getFlashTitle(),$event->getFlashMessage());
            return $this->redirectToRoute('db.manager.list', array('name' => $name));
        }
        return $this->render($eInfo['mainView'], array(
            'name' => $name,
            'eInfo' => $eInfo,
            'all' => $all,
            'form' => $form->createView(),
            'action' => array( 'name' => Configuration::PERM_EDIT, 'formType' => Configuration::PERM_EDIT)
        ));
    }

    public function removeAction($name, $id)
    {
        /** @var $dispatcher Dispatcher */
        $dispatcher = $this->get('event_dispatcher');
        /** @var $checker Checker */
        $checker = $this->get('fqt.dbcm.checker');
        $eInfo = $checker->getEntity($name, Configuration::PERM_REMOVE);

        $e = $checker->getEntityObject($eInfo, Configuration::PERM_REMOVE, $id);
        if ($e) {
            $event = new ActionEvent($eInfo, $e, array('success', 'Your entity have been removed'));
            $dispatcher->dispatch(DBCMEvents::ACTION_REMOVE_BEFORE, $event);

            if (!$event->isExecuted()) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($e);
                $em->flush();
            }
            $this->addFlash($event->getFlashTitle(),$event->getFlashMessage());
        } else
            $this->addFlash('danger','Your entity could not be deleted');
        return $this->redirectToRoute('db.manager.list', array('name' => $name));
    }

    /**
     * Process on add form reception
     *
     * @param array $eInfo
     * @param $e
     * @param Dispatcher $dispatcher
     * @param string $name
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function addFormReception(array $eInfo, $e, Dispatcher $dispatcher, string $name){
        $event = new ActionEvent($eInfo, $e, array('success', 'Your entity have been added'));
        $dispatcher->dispatch(DBCMEvents::ACTION_ADD_BEFORE, $event);

        if (!$event->isExecuted()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($e);
            $em->flush();
        }
        $this->addFlash($event->getFlashTitle(),$event->getFlashMessage());
        return $this->redirectToRoute('db.manager.list', array('name' => $name));
    }
}
