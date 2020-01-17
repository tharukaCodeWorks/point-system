<?php 
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use AppBundle\Entity\Support;
use AppBundle\Entity\Withdraw;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PaymentController extends Controller
{
    public function api_transaction_by_userAction(Request $request,$user,$key,$token)
    {
        if ($token!=$this->container->getParameter('token_app')) {
            throw new NotFoundHttpException("Page not found");  
        }

        $userId = $user;
        $userKey = $key;
        $em = $this->getDoctrine()->getManager();
        $user= $em->getRepository('UserBundle:User')->find($userId);
        if (!$user) {
            throw new NotFoundHttpException("Page not found");  
        }
        if (sha1($user->getPassword()) != $userKey) {
           throw new NotFoundHttpException("Page not found");  
        }


        $setting = $em->getRepository('AppBundle:Settings')->findOneBy(array());            
        $repository = $em->getRepository('AppBundle:Transaction');            
        $query = $repository->createQueryBuilder('w')
                ->where('w.user = :user',"w.enabled = true")
                ->setParameter('user', $user)
                ->addOrderBy('w.created', 'DESC')
                ->addOrderBy('w.id', 'asc')
                ->getQuery();
        $transactions = $query->getResult();
        return $this->render('AppBundle:Payment:api_all.html.php', array("currency"=>$setting->getCurrency(),"toCurrency"=>$setting->getOneusdtopoints(),"transactions" => $transactions));
    }
    public function withdrawalAction(Request $request)
    {
        $em= $this->getDoctrine()->getManager();
        $dql        = "SELECT s FROM AppBundle:Withdraw s order by s.created desc ";
        $query      = $em->createQuery($dql);
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
        $query,
        $request->query->getInt('page', 1),
            10
        );
        $count= $em->getRepository('AppBundle:Withdraw')->findAll();
        return $this->render('AppBundle:Payment:withdrawals.html.twig',
            array(
                'pagination' => $pagination,
                "count"=> $count
            )
        );
    }
    public function api_request_by_userAction(Request $request,$token)
    {
        if ($token!=$this->container->getParameter('token_app')) {
            throw new NotFoundHttpException("Page not found");  
        }


        $code="200";
        $message="";
        $errors=array();


        $userId = $request->get("user");
        $userKey = $request->get("key");
        $method = $request->get("method");
        $account = $request->get("account");
        $em = $this->getDoctrine()->getManager();
        $user= $em->getRepository('UserBundle:User')->find($userId);
        if (!$user) {
            throw new NotFoundHttpException("Page not found");  
        }
        if (sha1($user->getPassword()) != $userKey) {
            throw new NotFoundHttpException("Page not found");  
        }
        $setting = $em->getRepository('AppBundle:Settings')->findOneBy(array());            
        $transactions = $em->getRepository('AppBundle:Transaction')->findBy(array("user"=>$user,"enabled"=>true));            
        $total = 0;
        foreach ($transactions as $key => $transaction) {
            $total += $transaction->getPoints();
        }
        $earning  =  $total/$setting->getOneusdtopoints() ." ".$setting->getCurrency() ;
        $onetopoits = "1 ".$setting->getCurrency()." = ".$setting->getOneusdtopoints();
        if ($total> $setting->getMinpoints()) {
            $withdraw = new Withdraw();
            $withdraw->setUser($user);
            $withdraw->setMethode($method);
            $withdraw->setAccount($account);
            $withdraw->setPoints($total);
            $withdraw->setAmount($earning);
            $withdraw->setType("Pending");
            $em->persist($withdraw);
            $em->flush();
            $code = 200;
            $message = "Your withdrawal request has been submitted (".$earning.")";
            foreach ($transactions as $key => $transaction) {
                $transaction->setEnabled(false);
                #$em->remove($transaction);
            }
            $em->flush();
        }else{
            $code = "300";
            $message = "Withdrawal minimum ". $setting->getMinpoints()." Points";
        }
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
    public function api_withdrawals_by_userAction(Request $request,$user,$key,$token)
    {
        if ($token!=$this->container->getParameter('token_app')) {
            throw new NotFoundHttpException("Page not found");  
        }


        $code="200";
        $message="";
        $errors=array();


        $userId = $user;
        $userKey = $key;

        $em = $this->getDoctrine()->getManager();
        $user= $em->getRepository('UserBundle:User')->find($userId);
        if (!$user) {
            throw new NotFoundHttpException("Page not found");  
        }
        if (sha1($user->getPassword()) != $userKey) {
           throw new NotFoundHttpException("Page not found");  
        }
        $list=array();
        $withdrawals =   $em->getRepository("AppBundle:Withdraw")->findBy(array("user"=>$user),array("created"=>"desc"));
        foreach ($withdrawals as $key => $withdrawal) {
            $s["id"]=$withdrawal->getId();
            $s["method"]=$withdrawal->getMethode();
            $s["account"]=$withdrawal->getAccount();
            $s["amount"]=$withdrawal->getAmount();
            $s["points"]=$withdrawal->getPoints();
            $s["name"]=$withdrawal->getUser()->getName();
            $s["state"]=$withdrawal->getType();
            $s["date"]=$withdrawal->getCreated()->format('Y/m/d H:i:s');
            $list[]=$s;
        }
        header('Content-Type: application/json'); 
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $jsonContent=$serializer->serialize($list, 'json');
        return new Response($jsonContent);
    }
    public function api_earning_by_userAction(Request $request,$user,$key,$token)
    {
        if ($token!=$this->container->getParameter('token_app')) {
            throw new NotFoundHttpException("Page not found");  
        }


        $code="200";
        $message="";
        $errors=array();


        $userId = $user;
        $userKey = $key;
        $em = $this->getDoctrine()->getManager();
        $user= $em->getRepository('UserBundle:User')->find($userId);
        if (!$user) {
            throw new NotFoundHttpException("Page not found");  
        }
        if (sha1($user->getPassword()) != $userKey) {
           throw new NotFoundHttpException("Page not found");  
        }
        $setting = $em->getRepository('AppBundle:Settings')->findOneBy(array());            
        $transactions = $em->getRepository('AppBundle:Transaction')->findBy(array("user"=>$user,"enabled"=>true));            
        $total = 0;
        foreach ($transactions as $key => $transaction) {
            $total += $transaction->getPoints();
        }
        $earning  =  $this->number_format_short($total/$setting->getOneusdtopoints()) ." ".$setting->getCurrency() ;
        $onetopoits = "1 ".$setting->getCurrency()." = ".$setting->getOneusdtopoints();
        $errors[]=array("name"=>"earning","value"=>$earning);
        $errors[]=array("name"=>"points","value"=>$this->number_format_short($total));
        $errors[]=array("name"=>"equals","value"=>$onetopoits);
        $errors[]=array("name"=>"code","value"=>$user->getCode());
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
  
    public function deleteAction(Request $request,$id)
    {
        $em         = $this->getDoctrine()->getManager();
        $withdrawal    = $em->getRepository('AppBundle:Withdraw')->find($id);
        if ($withdrawal==null) {
            throw new NotFoundHttpException("Page not found");
        }
        $form=$this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->add('Yes', 'submit')
            ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em->remove($withdrawal);
            $em->flush();
            $request->getSession()->getFlashBag()->add('success','Operation has been done successfully');
            return $this->redirect($this->generateUrl('app_payment_withdrawal'));
        }
        return $this->render("AppBundle:Payment:delete.html.twig",array("form"=>$form->createView()));
    }
    public function rejectAction($id,Request $request){
        $em=$this->getDoctrine()->getManager();
        $withdraw = $em->getRepository("AppBundle:Withdraw")->find($id);
        if($withdraw==null){
            throw new NotFoundHttpException("Page not found");
        }
        $withdraw->setType("Rejected");
        $em->flush();
        $this->addFlash('success', 'Operation has been done successfully');
        return  $this->redirect($request->server->get('HTTP_REFERER'));
    }
    public function approveAction($id,Request $request){
        $em=$this->getDoctrine()->getManager();
        $withdraw = $em->getRepository("AppBundle:Withdraw")->find($id);
        if($withdraw==null){
            throw new NotFoundHttpException("Page not found");
        }
        $withdraw->setType("Paid");
        $em->flush();
        $this->addFlash('success', 'Operation has been done successfully');
        return $this->redirect($this->generateUrl('app_home_notif_user_payment',array("withdraw"=>$withdraw->getId())));
    }
    function number_format_short( $n ) {
        if($n<1000){
            return $n;
        }
      $precision = 1;
      if ($n < 900) {
            // 0 - 900
            $n_format = number_format($n, $precision);
            $suffix = '';
        } else if ($n < 900000) {
            // 0.9k-850k
            $n_format = number_format($n / 1000, $precision);
            $suffix = 'K';
        } else if ($n < 900000000) {
            // 0.9m-850m
            $n_format = number_format($n / 1000000, $precision);
            $suffix = 'M';
        } else if ($n < 900000000000) {
            // 0.9b-850b
            $n_format = number_format($n / 1000000000, $precision);
            $suffix = 'B';
        } else {
            // 0.9t+
            $n_format = number_format($n / 1000000000000, $precision);
            $suffix = 'T';
        }
      // Remove unecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
      // Intentionally does not affect partials, eg "1.50" -> "1.50"
        if ( $precision > 0 ) {
            $dotzero = '.' . str_repeat( '0', $precision );
            $n_format = str_replace( $dotzero, '', $n_format );
        }
        return $n_format . $suffix;
    }
}