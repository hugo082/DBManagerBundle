<?php

namespace DB\ManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use DB\ManagerBundle\DependencyInjection\Configuration;

class ManageController extends Controller
{
    public function indexAction()
    {
        $checker = $this->get('db.manager.checker');
        $entities = $checker->getEntities();

        return $this->render($checker->getSettings()['indexView'], array(
            'entities' => $entities
        ));
    }

    public function listAction(Request $request, $name)
    {
        $checker = $this->get('db.manager.checker');
        $eInfo = $checker->getEntity($name, Configuration::PERM_LIST);

        $e = new $eInfo['fullPath']();
        $all = $this->getEntity($eInfo);

        $form = NULL;
        if ($eInfo['displayElements'][Configuration::DISP_ELEM_FORM]) {
            $form = $this->createForm($eInfo['fullFormType'], $e);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($e);
                $em->flush();
                $this->addFlash('success','Your entity have been added');
                return $this->redirectToRoute('db.manager.list', array('name' => $name));
            }
            $form = $form->createView();
        }

        return $this->render($eInfo['mainView'], array(
            'name' => $name,
            'eInfo' => $eInfo,
            'all' => $all,
            'form' => $form,
            'action' => array( 'name' => 'list', 'formType' => 'add'),
            'settings' => $checker->getSettings()
        ));
    }

    public function addAction(Request $request, $name)
    {
        $checker = $this->get('db.manager.checker');
        $eInfo = $checker->getEntity($name, Configuration::PERM_ADD);

        $e = new $eInfo['fullPath']();
        $form = $this->createForm($eInfo['fullFormType'], $e);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($e);
            $em->flush();
            $this->addFlash('success','Your entity have been added');
            return $this->redirectToRoute('db.manager.list', array('name' => $name));
        }

        return $this->render($eInfo['mainView'], array(
            'name' => $name,
            'eInfo' => $eInfo,
            'all' => NULL,
            'form' => $form->createView(),
            'action' => array( 'name' => 'add', 'formType' => 'add'),
            'settings' => $checker->getSettings()
        ));
    }

    public function editAction(Request $request, $name, $id)
    {
        $checker = $this->get('db.manager.checker');
        $eInfo = $checker->getEntity($name, Configuration::PERM_EDIT);

        $all = $this->getEntity($eInfo);
        $e = $this->getEntity($eInfo, $id);
        if (!$e) {
            $this->addFlash('danger','Entity not found');
            return $this->redirectToRoute('db.manager.list', array('name' => $name));
        }
        $form = $this->createForm($eInfo['fullFormType'], $e);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($e);
            $em->flush();
            $this->addFlash('success','Your entity have updated');
            return $this->redirectToRoute('db.manager.list', array('name' => $name));
        }
        return $this->render($eInfo['mainView'], array(
            'name' => $name,
            'eInfo' => $eInfo,
            'all' => $all,
            'form' => $form->createView(),
            'action' => array( 'name' => 'edit', 'formType' => 'edit'),
            'settings' => $checker->getSettings()
        ));
    }

    public function removeAction($name, $id)
    {
        $checker = $this->get('db.manager.checker');
        $eInfo = $checker->getEntity($name, Configuration::PERM_REMOVE);

        $e = $this->getEntity($eInfo, $id);
        if ($e) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($e);
            $em->flush();
            $this->addFlash('success','Your entity have been removed');
        } else
            $this->addFlash('danger','Your entity could not be deleted');
        return $this->redirectToRoute('db.manager.list', array('name' => $name));
    }

    /**
     * Get
     * @param $eInfo
     * @param null $id
     * @return null|object|array
     */
    private function getEntity($eInfo, $id = NULL) {
        $repo = $this->getDoctrine()->getRepository($eInfo['bundle'].':'.$eInfo['name']);
        if ($id)
            return $repo->find($id);
        return $repo->findAll();
    }
}
