<?php

namespace AppBundle\Controller;
use AppBundle\Entity\Comment;
use AppBundle\Entity\Device;
use MediaBundle\Entity\Media;
use AppBundle\Form\SettingsType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class HomeController extends Controller
{
    function send_notificationToken ($tokens, $message,$key)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $fields = array(
            'registration_ids'  => $tokens,
            'data'   => $message

            );
        $headers = array(
            'Authorization:key = '.$key,
            'Content-Type: application/json'
            );
       $ch = curl_init();
       curl_setopt($ch, CURLOPT_URL, $url);
       curl_setopt($ch, CURLOPT_POST, true);
       curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);  
       curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
       curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
       $result = curl_exec($ch);           
       if ($result === FALSE) {
           die('Curl failed: ' . curl_error($ch));
       }
       curl_close($ch);
       return $result;
    }
    function send_notification ($tokens, $message,$key)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $fields = array(
            'to'  => '/topics/StatusAllInOne',
            'data'   => $message
            );
        $headers = array(
            'Authorization:key = '.$key,
            'Content-Type: application/json'
            );
       $ch = curl_init();
       curl_setopt($ch, CURLOPT_URL, $url);
       curl_setopt($ch, CURLOPT_POST, true);
       curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);  
       curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
       curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
       $result = curl_exec($ch);           
       if ($result === FALSE) {
           die('Curl failed: ' . curl_error($ch));
       }
       curl_close($ch);
       return $result;
    }

   
    public function notifCategoryAction(Request $request)
    {
        memory_get_peak_usage();
        $imagineCacheManager = $this->get('liip_imagine.cache.manager');



        $em=$this->getDoctrine()->getManager();
        $categories= $em->getRepository("AppBundle:Category")->findAll();

        $devices= $em->getRepository('AppBundle:Device')->findAll();
        $tokens=array();
        foreach ($devices as $key => $device) {
           $tokens[]=$device->getToken();
        }

        $defaultData = array();
        $form = $this->createFormBuilder($defaultData)
            ->setMethod('GET')
            ->add('title', TextType::class)
            ->add('message', TextareaType::class)
           # ->add('url', UrlType::class)
           # ->add('categories', ChoiceType::class, array('choices' => $categories ))           
            ->add('category', 'entity', array('class' => 'AppBundle:Category'))           
            ->add('icon', UrlType::class,array("label"=>"Large Icon","required"=>false))
            ->add('image', UrlType::class,array("label"=>"Big Picture","required"=>false))
            ->add('send', SubmitType::class,array("label"=>"Send notification"))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // data is an array with "name", "email", and "message" keys
            $data = $form->getData();

            $category_selected = $em->getRepository("AppBundle:Category")->find($data["category"]);

            $message = array(
                        "type"=>"category",
                        "id"=>$category_selected->getId(),
                        "title_category"=>$category_selected->getTitle(),
                        "video_category"=>$imagineCacheManager->getBrowserPath( $category_selected->getMedia()->getLink(), 'category_thumb_api'),
                        "title"=> $data["title"],
                        "message"=>$data["message"],
                        "image"=> $data["image"],
                        "icon"=>$data["icon"]
                        );
            
            $setting = $em->getRepository('AppBundle:Settings')->findOneBy(array());            
            $key=$setting->getFirebasekey();

            $message_video = $this->send_notification(null, $message,$key); 
            
            $this->addFlash('success', 'Operation has been done successfully ');

        }
        return $this->render('AppBundle:Home:notif_category.html.twig',array(
          "form"=>$form->createView()
          ));
    }
   public function notifUrlAction(Request $request)
    {
    
        memory_get_peak_usage();
        $imagineCacheManager = $this->get('liip_imagine.cache.manager');

        $em=$this->getDoctrine()->getManager();

        $defaultData = array();
        $form = $this->createFormBuilder($defaultData)
            ->setMethod('GET')
            ->add('title', TextType::class)
            ->add('message', TextareaType::class)      
            ->add('url', UrlType::class,array("label"=>"Url"))
            ->add('icon', UrlType::class,array("label"=>"Large Icon","required"=>false))
            ->add('image', UrlType::class,array("label"=>"Big Picture","required"=>false))
            ->add('send', SubmitType::class,array("label"=>"Send notification"))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $message = array(
                        "type"=>"link",
                        "id"=>strlen($data["url"]),
                        "link"=>$data["url"],
                        "title"=> $data["title"],
                        "message"=>$data["message"],
                        "image"=> $data["image"],
                        "icon"=>$data["icon"]
                        );
                        $setting = $em->getRepository('AppBundle:Settings')->findOneBy(array());            
            $key=$setting->getFirebasekey();
            $message_image = $this->send_notification(null, $message,$key); 
           
            $this->addFlash('success', 'Operation has been done successfully ');
          
        }
        return $this->render('AppBundle:Home:notif_url.html.twig',array(
            "form"=>$form->createView()
        ));
    }


    public function notifStatusAction(Request $request)
    {
        $imagineCacheManager = $this->get('liip_imagine.cache.manager');
        $em=$this->getDoctrine()->getManager();
        $defaultData = array();
        $form = $this->createFormBuilder($defaultData)
            ->setMethod('GET')
            ->add('title', TextType::class)
            ->add('message', TextareaType::class)
            ->add('object', 'entity', array('class' => 'AppBundle:Status'))           
            ->add('icon', UrlType::class,array("label"=>"Large Icon","required"=>false))
            ->add('image', UrlType::class,array("label"=>"Big Picture","required"=>false))
            ->add('send', SubmitType::class,array("label"=>"Send notification"))
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $selected_status = $em->getRepository("AppBundle:Status")->find($data["object"]);
            $original = "";
            $thumbnail = "";
            $type = "";
            $extension = "";
            $color = "";
            if ($selected_status->getType()!="quote") {
                    if ($selected_status->getVideo()) {
                          $type=$selected_status->getVideo()->getType();
                          $extension=$selected_status->getVideo()->getExtension();
                    }else{
                          $type=$selected_status->getMedia()->getType();
                          $extension=$selected_status->getMedia()->getExtension();
                    }
                    $thumbnail= $imagineCacheManager->getBrowserPath($selected_status->getMedia()->getLink(), 'status_thumb_api');
                    if ($selected_status->getVideo()) {
                          if ($selected_status->getVideo()->getEnabled()) {
                                $original = $this->getRequest()->getUriForPath("/".$selected_status->getVideo()->getLink()) ;
                          }else{
                                $original = $selected_status->getVideo()->getLink();
                          } 
                    }else{
                                $original = $this->getRequest()->getUriForPath("/".$selected_status->getMedia()->getLink()) ;
                    }
            }else{
                  $color=$selected_status->getColor();
            }
            $message = array(
                  "type"=> "status",
                  "kind"=> $selected_status->getType(),
                  "id"=> $selected_status->getId(),
                  "status_title"=> $selected_status->getTitle(),
                  "status_description"=> $selected_status->getDescription(),
                  "status_review"=> $selected_status->getReview(),
                  "status_comment"=> $selected_status->getComment(),
                  "status_comments"=>sizeof($selected_status->getComments()),
                  "status_downloads"=> $selected_status->getDownloads(),
                  "status_views"=> $selected_status->getViews(),
                  "status_font"=> $selected_status->getFont(),
                  "status_user"=> $selected_status->getUser()->getName(),
                  "status_userid"=> $selected_status->getUser()->getId(),
                  "status_userimage"=> $selected_status->getUser()->getImage(),
                  "status_type"=>$type,
                  "status_extension"=>$extension,
                  "status_thumbnail"=>$thumbnail,
                  "status_original"=>$original,
                  "status_color"=>$color,
                  "status_created"=> "Now",
                  "status_tags"=> $selected_status->getTags(),
                  "status_like"=> $selected_status->getLike(),
                  "status_love"=> $selected_status->getLove(),
                  "status_woow"=> $selected_status->getWoow(),
                  "status_angry"=> $selected_status->getAngry(),
                  "status_sad"=> $selected_status->getSad(),
                  "status_haha"=> $selected_status->getHaha(),
                  "title"=> $data["title"],
                  "message"=>$data["message"],
                  "image"=> $data["image"],
                  "icon"=>$data["icon"]
                );

                        $setting = $em->getRepository('AppBundle:Settings')->findOneBy(array());            
            $key=$setting->getFirebasekey();
            $message_image = $this->send_notification(null, $message,$key); 
            $this->addFlash('success', 'Operation has been done successfully ');
        }
        return $this->render('AppBundle:Home:notif_status.html.twig',array(
          "form"=>$form->createView()
          ));
    }

  public function notifUserStatusAction(Request $request)
    {
        memory_get_peak_usage();
        $imagineCacheManager = $this->get('liip_imagine.cache.manager');
        $status_id= $request->query->get("status_id");
        $em=$this->getDoctrine()->getManager();

        $defaultData = array();
        $form = $this->createFormBuilder($defaultData)
            ->setMethod('GET')
            ->add('title', TextType::class)
            ->add('object', HiddenType::class,array("attr"=>array("value"=>$status_id)))
            ->add('message', TextareaType::class)
            ->add('icon', UrlType::class,array("label"=>"Large Icon","required"=>false))
            ->add('image', UrlType::class,array("label"=>"Big Picture","required"=>false))
            ->add('send', SubmitType::class,array("label"=>"Send notification"))
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // data is an array with "name", "email", and "message" keys
            $data = $form->getData();
            $selected_status = $em->getRepository("AppBundle:Status")->find($data["object"]);

            $user= $selected_status->getUser();

            $devices= $em->getRepository('AppBundle:Device')->findAll();
             if ($user==null) {
                throw new NotFoundHttpException("Page not found");  
            }
            $tokens=array();

            $tokens[]=$user->getToken();
                        $data = $form->getData();
            $original = "";
            $thumbnail = "";
            $type = "";
            $extension = "";
            $color = "";
            if ($selected_status->getType()!="quote") {
                    if ($selected_status->getVideo()) {
                          $type=$selected_status->getVideo()->getType();
                          $extension=$selected_status->getVideo()->getExtension();
                    }else{
                          $type=$selected_status->getMedia()->getType();
                          $extension=$selected_status->getMedia()->getExtension();
                    }
                    $thumbnail= $imagineCacheManager->getBrowserPath($selected_status->getMedia()->getLink(), 'status_thumb_api');
                    if ($selected_status->getVideo()) {
                          if ($selected_status->getVideo()->getEnabled()) {
                                $original = $this->getRequest()->getUriForPath("/".$selected_status->getVideo()->getLink()) ;
                          }else{
                                $original = $selected_status->getVideo()->getLink();
                          } 
                    }else{
                                $original = $this->getRequest()->getUriForPath("/".$selected_status->getMedia()->getLink()) ;
                    }
            }else{
                  $color=$selected_status->getColor();
            }
            $message = array(
                  "type"=> "status",
                  "kind"=> $selected_status->getType(),
                  "id"=> $selected_status->getId(),
                  "status_title"=> $selected_status->getTitle(),
                  "status_description"=> $selected_status->getDescription(),
                  "status_review"=> $selected_status->getReview(),
                  "status_comment"=> $selected_status->getComment(),
                  "status_comments"=>sizeof($selected_status->getComments()),
                  "status_downloads"=> $selected_status->getDownloads(),
                  "status_views"=> $selected_status->getViews(),
                  "status_font"=> $selected_status->getFont(),
                  "status_user"=> $selected_status->getUser()->getName(),
                  "status_userid"=> $selected_status->getUser()->getId(),
                  "status_userimage"=> $selected_status->getUser()->getImage(),
                  "status_type"=>$type,
                  "status_extension"=>$extension,
                  "status_thumbnail"=>$thumbnail,
                  "status_original"=>$original,
                  "status_color"=>$color,
                  "status_created"=> "Now",
                  "status_tags"=> $selected_status->getTags(),
                  "status_like"=> $selected_status->getLike(),
                  "status_love"=> $selected_status->getLove(),
                  "status_woow"=> $selected_status->getWoow(),
                  "status_angry"=> $selected_status->getAngry(),
                  "status_sad"=> $selected_status->getSad(),
                  "status_haha"=> $selected_status->getHaha(),
                  "title"=> $data["title"],
                  "message"=>$data["message"],
                  "image"=> $data["image"],
                  "icon"=>$data["icon"]
                );

                         $setting = $em->getRepository('AppBundle:Settings')->findOneBy(array());            
            $key=$setting->getFirebasekey();
             $message_image = $this->send_notificationToken($tokens, $message,$key); 
             $this->addFlash('success', 'Operation has been done successfully ');
             return $this->redirect($this->generateUrl('app_status_index'));
        }else{
           $video= $em->getRepository("AppBundle:Status")->find($status_id);
        }
        return $this->render('AppBundle:Home:notif_user_status.html.twig',array(
            "form"=>$form->createView()));
    }  
public function notifUserPaymentAction(Request $request)
    {
        memory_get_peak_usage();
        $imagineCacheManager = $this->get('liip_imagine.cache.manager');
        $payment= $request->query->get("withdraw");
        $em=$this->getDoctrine()->getManager();

        $defaultData = array();
        $form = $this->createFormBuilder($defaultData)
            ->setMethod('GET')
            ->add('title', TextType::class)
            ->add('object', HiddenType::class,array("attr"=>array("value"=>$payment)))
            ->add('message', TextareaType::class)
            ->add('icon', UrlType::class,array("label"=>"Large Icon","required"=>false))
            ->add('image', UrlType::class,array("label"=>"Big Picture","required"=>false))
            ->add('send', SubmitType::class,array("label"=>"Send notification"))
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // data is an array with "name", "email", and "message" keys
            $data = $form->getData();
            $withdrawal = $em->getRepository("AppBundle:Withdraw")->find($data["object"]);

            $user= $withdrawal->getUser();

            $devices= $em->getRepository('AppBundle:Device')->findAll();
             if ($user==null) {
                throw new NotFoundHttpException("Page not found");  
            }
            $tokens=array();

            $tokens[]=$user->getToken();
            $data = $form->getData();
            $message = array(
                  "id"=> $withdrawal->getId(),
                  "type"=> "payment",
                  "title"=> $data["title"],
                  "message"=>$data["message"],
                  "image"=> $data["image"],
                  "icon"=>$data["icon"]
                );

                         $setting = $em->getRepository('AppBundle:Settings')->findOneBy(array());            
            $key=$setting->getFirebasekey();
             $message_image = $this->send_notificationToken($tokens, $message,$key); 
             $this->addFlash('success', 'Operation has been done successfully ');
             return $this->redirect($this->generateUrl('app_payment_withdrawal'));
        }
        return $this->render('AppBundle:Home:notif_user_payment.html.twig',array(
            "form"=>$form->createView()));
    }  
    public function settingsAction(Request $request)
    {   
        $em=$this->getDoctrine()->getManager();
        $settings=$em->getRepository("AppBundle:Settings")->findOneBy(array());
        if ($settings==null) {
            throw new NotFoundHttpException("Page not found");
        }
        $form = $this->createForm(new SettingsType(),$settings);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Operation has been done successfully');
        }
        return $this->render('AppBundle:Home:settings.html.twig',array(
            "form"=>$form->createView()));
    }  
    public function indexAction(Request $request)
    {   

        $em=$this->getDoctrine()->getManager();
        $supports_count= $em->getRepository("AppBundle:Support")->count();
        $devices_count= $em->getRepository("AppBundle:Device")->count();
        $video_count= $em->getRepository("AppBundle:Status")->count("video");
       $image_count= $em->getRepository("AppBundle:Status")->count("image");
       $gif_count= $em->getRepository("AppBundle:Status")->count("gif");
       $quote_count= $em->getRepository("AppBundle:Status")->count("quote");
       $withdrawals_count= $em->getRepository("AppBundle:Withdraw")->count();
        $review_count= $em->getRepository("AppBundle:Status")->countReview();
        $count_downloads= $em->getRepository("AppBundle:Status")->countDownloads();
        $count_views= $em->getRepository("AppBundle:Status")->countViews();

        $category_count= $em->getRepository("AppBundle:Category")->count();
        $comment_count= $em->getRepository("AppBundle:Comment")->count();
        $language_count= $em->getRepository("AppBundle:Language")->count();
        $version_count= $em->getRepository("AppBundle:Version")->count();
        $users= $em->getRepository("UserBundle:User")->findAll();
        $users_count= sizeof($users)-1;





        return $this->render('AppBundle:Home:index.html.twig',array(
            
                "count_views"=>$count_views,
                "count_downloads"=>$count_downloads,
                "withdrawals_count"=>$withdrawals_count,
                "devices_count"=>$devices_count,
                "video_count"=>$video_count,
                "image_count"=>$image_count,
                "gif_count"=>$gif_count,
                "quote_count"=>$quote_count,
                "category_count"=>$category_count,

                "review_count"=>$review_count,
                "users_count"=>$users_count,
                "comment_count"=>$comment_count,

                "version_count"=>$version_count,
                "language_count"=>$language_count,
                "supports_count"=>$supports_count

        ));
    }
    public function api_firstAction(Request $request,$language, $token) {
        if ($token != $this->container->getParameter('token_app')) {
          throw new NotFoundHttpException("Page not found");
        }
        //--------------- status ------------------//
        $page = 0;
        $nombre = 30;
        $em = $this->getDoctrine()->getManager();
        $imagineCacheManager = $this->get('liip_imagine.cache.manager');
        $repository = $em->getRepository('AppBundle:Status');

        if ($language == 0) {
          $query = $repository->createQueryBuilder('w')
            ->where("w.enabled = true","w.type not like 'fullscreen'")
            ->addOrderBy('w.created', 'DESC')
            ->addOrderBy('w.id', 'asc')
            ->setFirstResult($nombre * $page)
            ->setMaxResults($nombre)
            ->getQuery();
        } else {
          $query = $repository->createQueryBuilder('w')
            ->leftJoin('w.languages', 'l')
            ->where('l.id in (' . $language . ')', "w.enabled = true","w.type not like 'fullscreen'")
            ->addOrderBy('w.created', 'DESC')
            ->addOrderBy('w.id', 'asc')
            ->setFirstResult($nombre * $page)
            ->setMaxResults($nombre)
            ->getQuery();
        }
        $videos = $query->getResult();

        //--------------- fullscreen videos ------------------//
        
        $repository_full_screen = $em->getRepository('AppBundle:Status');


        $nombre_full_screen = 20;
        $page_full_screen = 0;
        if ($language == 0) {
            $query_fullscreen = $repository_full_screen->createQueryBuilder('w')
                ->where("w.enabled = true","w.type like 'fullscreen'")
                ->addOrderBy('w.created' , 'DESC')
                ->addOrderBy('w.id', 'asc')
                ->setFirstResult($nombre_full_screen * $page_full_screen)
                ->setMaxResults($nombre_full_screen)
                ->getQuery();
        } else {
            $query_fullscreen = $repository_full_screen->createQueryBuilder('w')
                ->leftJoin('w.languages', 'l')
                ->where('l.id in (' . $language . ')', "w.enabled = true","w.type like 'fullscreen'")
                ->addOrderBy('w.created' , 'DESC')
                ->addOrderBy('w.id', 'asc')
                ->setFirstResult($nombre_full_screen * $page_full_screen)
                ->setMaxResults($nombre_full_screen)
                ->getQuery();
        }
        $full_screen_videos = $query_fullscreen->getResult();
        // ----------------  popular categories ------------------// 
        
        $popular_categories=array();

       $repository_category = $em->getRepository('AppBundle:Category');

        $query_categories = $repository_category->createQueryBuilder('C')
          ->select(array("C.id","C.title","m.url as image","m.extension as extension","SUM(w.downloads) as test"))
          ->leftJoin('C.status', 'w')
          ->leftJoin('C.media', 'm')
          ->groupBy('C.id')
          ->orderBy('test',"DESC")
          ->where('w.enabled=true')
          ->getQuery();

        $categories = $query_categories->getResult();

        foreach ($categories as $key => $category) {
            $s["id"]=$category["id"];
            $s["title"]=$category["title"];
            $media =  new Media();
            $s["image"]=$imagineCacheManager->getBrowserPath("uploads/".$category["extension"]."/".$category["image"], 'category_thumb_api');
            $popular_categories[]=$s;
        }

        //------------- slide --------------// 
        $em = $this->getDoctrine()->getManager();
        $slides = $em->getRepository("AppBundle:Slide")->findBy(array(), array("position" => "asc"));

        return $this->render('AppBundle:Home:api_all.html.php', array("slides"=>$slides,"full_screen_videos"=>$full_screen_videos,"popular_categories"=>$popular_categories,"videos" => $videos));
    }
    public function api_deviceAction($tkn,$token){
        if ($token!=$this->container->getParameter('token_app')) {
            throw new NotFoundHttpException("Page not found");  
        }
        $code="200";
        $message="";
        $errors=array();
        $em = $this->getDoctrine()->getManager();
        $d=$em->getRepository('AppBundle:Device')->findOneBy(array("token"=>$tkn));
        if ($d==null) {
            $device = new Device();
            $device->setToken($tkn);
            $em->persist($device);
            $em->flush();
            $message="Deivce added";
        }else{
            $message="Deivce Exist";
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

    



}