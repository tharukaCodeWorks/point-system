<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Comment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\HttpFoundation\Response;

class CommentController extends Controller
{
    public function indexAction(Request $request)
    {
        $em= $this->getDoctrine()->getManager();
        $dql        = "SELECT c FROM AppBundle:Comment c  ORDER BY c.created desc";
        $query      = $em->createQuery($dql);
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
        $query,
        $request->query->getInt('page', 1),
            10
        );
        $comments=$em->getRepository('AppBundle:Comment')->findAll();
        
        return $this->render('AppBundle:Comment:index.html.twig',
            array(
                'pagination' => $pagination,
                'comments' => $comments,
            )
        );
    }
    public function api_listAction($id,$token)
    {
        if ($token!=$this->container->getParameter('token_app')) {
            throw new NotFoundHttpException("Page not found");  
        }
        $em=$this->getDoctrine()->getManager();

        $status=$em->getRepository('AppBundle:Status')->find($id);
        $comments=array();
        if ($status!=null) {
            $comments=$em->getRepository('AppBundle:Comment')->findBy(array("status"=>$status));
        }

        return $this->render('AppBundle:Comment:api_by.html.php',
            array('comments' => $comments)
        );  
    }
    public function api_addAction(Request $request,$token)
    {
        if ($token!=$this->container->getParameter('token_app')) {
            throw new NotFoundHttpException("Page not found");  
        }

        $user=$request->get("user");
        $id=$request->get("id");
        $comment=$request->get("comment");

        $em=$this->getDoctrine()->getManager();
        $u=$em->getRepository("UserBundle:User")->find($user);
        $code="200";
        $message="";
        $errors=array();

            $comment_obj = new Comment();
            $comment_obj->setContent($comment);
            $comment_obj->setEnabled(true);
            $comment_obj->setUser($u);

            $em=$this->getDoctrine()->getManager();

            $status=$em->getRepository('AppBundle:Status')->find($id);
            $comment_obj->setStatus($status);

            $em->persist($comment_obj);
            $em->flush();  
            $errors[]=array("name"=>"id","value"=>$comment_obj->getId());
            $errors[]=array("name"=>"content","value"=>$comment_obj->getContent());
            $errors[]=array("name"=>"user","value"=>$comment_obj->getUser()->getName());
            $errors[]=array("name"=>"image","value"=>$comment_obj->getUser()->getImage());
            $errors[]=array("name"=>"enabled","value"=>$comment_obj->getEnabled());
            $trusted = "false";
            if ($comment_obj->getUser()->getTrusted()) {
               $trusted = "true";
            }
            $errors[]=array("name"=>"trusted","value"=> $trusted);
            $errors[]=array("name"=>"created","value"=>"now");
            $message="Your comment has been added";


        $error=array(
            "code"=>$code,
            "message"=>$message,
            "values"=>$errors
        );
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $jsonContent=$serializer->serialize($error, 'json');
        return new Response($jsonContent);
    }
    public function hideAction($id,Request $request){
        $em=$this->getDoctrine()->getManager();
        $comment = $em->getRepository("AppBundle:Comment")->find($id);
        if($comment==null){
            throw new NotFoundHttpException("Page not found");
        }
    	$status=$comment->getStatus();
        $user=$comment->getUser();
    	if ($comment->getEnabled()==true) {
    		$comment->setEnabled(false);
    	}else{
    		$comment->setEnabled(true);
    	}
        $em->flush();
        $this->addFlash('success', 'Operation has been done successfully');
        return  $this->redirect($request->server->get('HTTP_REFERER'));
    }
    public function hide_twoAction($id,Request $request){
        $em=$this->getDoctrine()->getManager();

        $comment = $em->getRepository("AppBundle:Comment")->find($id);
        if($comment==null){
            throw new NotFoundHttpException("Page not found");
        }
        	$id_article=$comment->getStatus()->getId();
        	if ($comment->getEnabled()==true) {
        		$comment->setEnabled(false);
        	}else{
        		$comment->setEnabled(true);
        	}
            $em->flush();
            $this->addFlash('success', 'Operation has been done successfully');
            return $this->redirect($this->generateUrl('app_comment_index'));

    }

    public function deleteAction($id,Request $request){
        $em=$this->getDoctrine()->getManager();

        $comment = $em->getRepository("AppBundle:Comment")->find($id);
        if($comment==null){
            throw new NotFoundHttpException("Page not found");
        }
        $status=$comment->getStatus();
        $user=$comment->getUser();
        $form=$this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->add('Yes', 'submit')
            ->getForm();
        if ($request->query->has("user")) {
            $url = $this->generateUrl('user_user_edit',array("id"=>$user->getId()));
        }
        if ($request->query->has("status")) {
            $url = $this->generateUrl('app_status_view',array("id"=>$status->getId()));
        }
        if ($request->query->has("comment")) {
            $url = $this->generateUrl('app_comment_index');
        }
        
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em->remove($comment);
            $em->flush();
            $this->addFlash('success', 'Operation has been done successfully');
            return $this->redirect($url);

        }
        return $this->render('AppBundle:Comment:delete.html.twig',array("url"=>$url,"form"=>$form->createView()));
    }
}
