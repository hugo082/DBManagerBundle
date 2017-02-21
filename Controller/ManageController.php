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
        return $this->render('DBManagerBundle:Manage:index.html.twig', array(
            'entities' => $entities
        ));
    }

    public function listAction(Request $request, $name)
    {
        $array = $this->container->getParameter( 'db_manager.entities' );
        $settings = $this->container->getParameter('db_manager.views');
        $einfo = $this->get('db.manager.checker')->getEntity($array, $name);

        $e = new $einfo['fullpath']();
        $all = $this->getEntity($einfo);

        $form = NULL;
        if ($settings['list']['add'] && $einfo['permission']['add']) {
            $form = $this->createForm($einfo['formtype'], $e);
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

        return $this->render('DBManagerBundle:Manage:entity.html.twig', array(
            'name' => $name,
            'einfo' => $einfo,
            'all' => $all,
            'form' => $form,
            'action' => 'list'
        ));
    }

    public function addAction(Request $request, $name)
    {
        $array = $this->container->getParameter( 'db_manager.entities' );
        $settings = $this->container->getParameter('db_manager.views');
        $einfo = $this->get('db.manager.checker')->getEntity($array, $name);

        if ($settings['list']['add'] || !$einfo['permission']['add'])
            return $this->redirectToRoute('db.manager.list', array('name' => $name));

        $e = new $einfo['fullpath']();
        $form = $this->createForm($einfo['formtype'], $e);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($e);
            $em->flush();
            $this->addFlash('success','Your entity have been added');
            return $this->redirectToRoute('db.manager.list', array('name' => $name));
        }

        return $this->render('DBManagerBundle:Manage:entity.html.twig', array(
            'name' => $name,
            'einfo' => $einfo,
            'all' => NULL,
            'form' => $form->createView(),
            'action' => 'add'
        ));
    }

    public function editAction(Request $request, $name, $id)
    {
        $array = $this->container->getParameter('db_manager.entities');
        $settings = $this->container->getParameter('db_manager.views');
        $einfo = $this->get('db.manager.checker')->getEntity($array, $name);
        $this->get('db.manager.checker')->edit($einfo);

        $all = ($settings['edit']['list']) ? $this->getEntity($einfo) : NULL;
        $e = $this->getEntity($einfo, $id);
        if (!$e) {
            $this->addFlash('danger','Entity not found');
            return $this->redirectToRoute('db.manager.list', array('name' => $name));
        }
        $form = $this->createForm($einfo['formtype'], $e);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($e);
            $em->flush();
            $this->addFlash('success','Your entity have updated');
            return $this->redirectToRoute('db.manager.list', array('name' => $name));
        }
        return $this->render('DBManagerBundle:Manage:entity.html.twig', array(
            'name' => $name,
            'einfo' => $einfo,
            'all' => $all,
            'form' => $form->createView(),
            'action' => 'edit'
        ));
    }

    public function removeAction(Request $request, $name, $id)
    {
        $array = $this->container->getParameter( 'db_manager.entities' );
        $einfo = $this->get('db.manager.checker')->getEntity($array, $name);
        $this->get('db.manager.checker')->remove($einfo);

        $e = $this->getEntity($einfo, $id);
        if ($e) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($e);
            $em->flush();
            $this->addFlash('success','Your entity have been removed');
        } else
            $this->addFlash('danger','Your entity could not be deleted');
        return $this->redirectToRoute('db.manager.list', array('name' => $name));
    }

    private function getEntity($einfo, $id = NULL) {
        $repo = $this->getDoctrine()->getRepository($einfo['bundle'].':'.$einfo['name']);
        if ($id)
            return $repo->find($id);
        return $repo->findAll();
    }
}
