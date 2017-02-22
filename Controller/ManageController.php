<?php

namespace DB\ManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ManageController extends Controller
{
    public function indexAction()
    {
        $entities = $this->container->getParameter( 'db_manager.entities' );
        $settings = $this->container->getParameter('db_manager.views');
        return $this->render($settings['indexView'], array(
            'entities' => $entities
        ));
    }

    public function listAction(Request $request, $name)
    {
        $array = $this->container->getParameter( 'db_manager.entities' );
        $settings = $this->container->getParameter('db_manager.views');
        $eInfo = $this->get('db.manager.checker')->getEntity($array, $name);

        $e = new $eInfo['fullPath']();
        $all = $this->getEntity($eInfo);

        $form = NULL;
        if ($settings['list']['add'] && $eInfo['permission']['add']) {
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
            'settings' => $settings
        ));
    }

    public function addAction(Request $request, $name)
    {
        $array = $this->container->getParameter( 'db_manager.entities' );
        $settings = $this->container->getParameter('db_manager.views');
        $eInfo = $this->get('db.manager.checker')->getEntity($array, $name);

        if ($settings['list']['add'] || !$eInfo['permission']['add'])
            return $this->redirectToRoute('db.manager.list', array('name' => $name));

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
            'settings' => $settings
        ));
    }

    public function editAction(Request $request, $name, $id)
    {
        $array = $this->container->getParameter('db_manager.entities');
        $settings = $this->container->getParameter('db_manager.views');
        $eInfo = $this->get('db.manager.checker')->getEntity($array, $name);
        $this->get('db.manager.checker')->edit($eInfo);

        $all = ($settings['edit']['list']) ? $this->getEntity($eInfo) : NULL;
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
            'settings' => $settings
        ));
    }

    public function removeAction(Request $request, $name, $id)
    {
        $array = $this->container->getParameter( 'db_manager.entities' );
        $eInfo = $this->get('db.manager.checker')->getEntity($array, $name);
        $this->get('db.manager.checker')->remove($eInfo);

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

    private function getEntity($eInfo, $id = NULL) {
        $repo = $this->getDoctrine()->getRepository($eInfo['bundle'].':'.$eInfo['name']);
        if ($id)
            return $repo->find($id);
        return $repo->findAll();
    }
}
