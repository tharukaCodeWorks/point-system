<?php
namespace AppBundle\Controller;
use AppBundle\Entity\Transaction;
use AppBundle\Entity\Status;
use AppBundle\Form\ImageType;
use AppBundle\Form\GifType;
use AppBundle\Form\QuoteType;
use AppBundle\Form\StatusReviewType;
use AppBundle\Form\QuoteReviewType;
use AppBundle\Form\VideoType;
use AppBundle\Form\VideoTypeUrl;
use MediaBundle\Entity\Media;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class StatusController extends Controller {
    function remove_emoji ($string="") {
    $string = str_replace(" ","736489290",$string);
     
     // PREG_REPLACE REMOVE ALL OTHER CHARACTERS THAT NOT AVAIALABLE IN PREG_REPLACE FIRST
     // PARAMETER YOU CANNOT UNDERSTAND FIRST PARAMETER YOU MUST READ PHP REGULAR EXPRESSION!
     $string = preg_replace('/[^A-Za-z0-9]/','',$string);
     
     //STRIP_TAGS REMOVE HTML TAGS
     $string=strip_tags($string,"");
      //HERE WE REMOVE WHITE SPACES AND RETURN IT
    
     $newString =  trim($string);
     $newString = str_replace("736489290"," ",$newString);

     return $newString;
    }
	public function addVideoAction(Request $request) {
		$video = new Status();
		$video->setType("video");
		$form = $this->createForm(new VideoType(), $video);
		$em = $this->getDoctrine()->getManager();

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			if ($video->getFile() != null and $video->getFilevideo() != null) {
				$media = new Media();
				$media->setFile($video->getFile());
				$media->setEnabled(true);
				$media->upload($this->container->getParameter('files_directory'));

				$video->setMedia($media);

				$video_media = new Media();
				$video_media->setFile($video->getFilevideo());
				$video_media->setEnabled(true);
				$video_media->upload($this->container->getParameter('files_directory'));

				$video->setVideo($video_media);

				$video->setUser($this->getUser());
				$video->setReview(false);
				$video->setDownloads(0);
				$em->persist($media);
				$em->flush();

				$em->persist($video_media);
				$em->flush();

				$em->persist($video);
				$em->flush();
				$this->addFlash('success', 'Operation has been done successfully');
				return $this->redirect($this->generateUrl('app_status_index'));
			} else {
				$photo_error = new FormError("Required image file");
				$video_error = new FormError("Required video file");
				$form->get('file')->addError($photo_error);
				$form->get('filevideo')->addError($video_error);
			}
		}
		return $this->render("AppBundle:Status:video_add.html.twig", array("form" => $form->createView()));
	}


    public function editVideoAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $video = $em->getRepository("AppBundle:Status")->findOneBy(array("id" => $id, "review" => false));
        if ($video == null) {
            throw new NotFoundHttpException("Page not found");
        }
        $form = $this->createForm(new VideoType(), $video);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($video->getFile() != null) {
                $media = new Media();
                $media_old = $video->getMedia();
                $media->setFile($video->getFile());
                $media->setEnabled(true);
                $media->upload($this->container->getParameter('files_directory'));
                $em->persist($media);
                $em->flush();
                $video->setMedia($media);
                $em->flush();
                $media_old->delete($this->container->getParameter('files_directory'));
                $em->remove($media_old);
                $em->flush();
            }

            if ($video->getFilevideo() != null) {
                $video_media = new Media();
                $video_media_old = $video->getVideo();
                $video_media->setFile($video->getFilevideo());
                $video_media->setEnabled(true);
                $video_media->upload($this->container->getParameter('files_directory'));
                $em->persist($video_media);
                $em->flush();

                $video->setVideo($video_media);
                $em->flush();

                $video_media_old->delete($this->container->getParameter('files_directory'));
                $em->remove($video_media_old);
                $em->flush();
            }

            $em->persist($video);
            $em->flush();
            $this->addFlash('success', 'Operation has been done successfully');
            return $this->redirect($this->generateUrl('app_status_index'));
        }
        return $this->render("AppBundle:Status:video_edit.html.twig", array("form" => $form->createView()));
    }

public function addVideoUrlAction(Request $request) {
        $video = new Status();
        $video->setType("video");
        $form = $this->createForm(new VideoTypeUrl(), $video);
        $em = $this->getDoctrine()->getManager();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file_ext = substr(strrchr($video->getUrlvideo(), '.'), 1);
            switch ($file_ext) {
            case 'mp4':
                $file_type = "video/mp4";
                break;
            case 'webm':
                $file_type = "video/webm";
                break;
            default:
                $file_type = "none";
                break;
            }

            if ($file_type != "none") {
                if ($video->getFile() != null) {
                    $media = new Media();
                    $media->setFile($video->getFile());
                    $media->setEnabled(true);
                    $media->upload($this->container->getParameter('files_directory'));
                    $video->setMedia($media);

                    $video_media = new Media();
                    $video_media->setTitre($video->getTitle());
                    $video_media->setUrl($video->getUrlvideo());
                    $video_media->setExtension($file_ext);
                    $video_media->setType($file_type);
                    $video_media->setEnabled(false);

                    $video->setVideo($video_media);

                    $video->setUser($this->getUser());
                    $video->setReview(false);
                    $video->setDownloads(0);
                    $em->persist($media);
                    $em->flush();

                    $em->persist($video_media);
                    $em->flush();

                    $em->persist($video);
                    $em->flush();
                    $this->addFlash('success', 'Operation has been done successfully');
                    return $this->redirect($this->generateUrl('app_status_index'));
                } else {
                    $photo_error = new FormError("Required image file");
                    $form->get('file')->addError($photo_error);
                }
            } else {
                $type_error = new FormError("Url has video not supported");
                $form->get('urlvideo')->addError($type_error);
            }
        }
        return $this->render("AppBundle:Status:video_add_url.html.twig", array("form" => $form->createView()));
    }
    public function editVideoUrlAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $video = $em->getRepository("AppBundle:Status")->findOneBy(array("id" => $id, "review" => false));
        if ($video == null) {
            throw new NotFoundHttpException("Page not found");
        }
        $videourl = $video->getVideo()->getUrl();
        $video->setUrlvideo($videourl);
        $form = $this->createForm(new VideoTypeUrl(), $video);
        $form->handleRequest($request);

        $file_ext = substr(strrchr($video->getUrlvideo(), '.'), 1);
        switch ($file_ext) {
        case 'mp4':
            $file_type = "video/mp4";
            break;
        case 'webm':
            $file_type = "video/webm";
            break;
        default:
            $file_type = "none";
            break;
        }

        if ($file_type != "none") {

            if ($form->isSubmitted() && $form->isValid()) {
                if ($videourl != $video->getUrlvideo()) {

                    $video_media = new Media();
                    $video_media->setTitre($video->getTitle());
                    $video_media->setUrl($video->getUrlvideo());
                    $video_media->setExtension($file_ext);
                    $video_media->setType($file_type);
                    $video_media->setEnabled(false);

                    $video_media_old = $video->getVideo();
                    $em->persist($video_media);
                    $em->flush();

                    $video->setVideo($video_media);
                    $em->flush();

                    $video_media_old->delete($this->container->getParameter('files_directory'));
                    $em->remove($video_media_old);
                    $em->flush();
                }
                if ($video->getFile() != null) {
                    $media = new Media();
                    $media_old = $video->getMedia();
                    $media->setFile($video->getFile());
                    $media->setEnabled(true);
                    $media->upload($this->container->getParameter('files_directory'));
                    $em->persist($media);
                    $em->flush();
                    $video->setMedia($media);
                    $em->flush();
                    $media_old->delete($this->container->getParameter('files_directory'));
                    $em->remove($media_old);
                    $em->flush();
                }

                $em->persist($video);
                $em->flush();
                $this->addFlash('success', 'Operation has been done successfully');
                return $this->redirect($this->generateUrl('app_status_index'));
            }
        } else {
            $type_error = new FormError("Url has video not supported");
            $form->get('urlvideo')->addError($type_error);
        }
        return $this->render("AppBundle:Status:video_edit_url.html.twig", array("form" => $form->createView()));
    }


    public function addQuoteAction(Request $request) {
        $status = new Status();
        $status->setType("quote");
        $form = $this->createForm(new QuoteType(), $status);
        $em = $this->getDoctrine()->getManager();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
                $status->setDescription($this->remove_emoji($status->getTitle()));
                $status->setTitle(base64_encode($status->getTitle()));

                $status->setUser($this->getUser());
                $status->setReview(false);
                $status->setDownloads(0);
                $em->persist($status);
                $em->flush();
                $this->addFlash('success', 'Operation has been done successfully');
                return $this->redirect($this->generateUrl('app_status_index'));
        
        }
        return $this->render("AppBundle:Status:quote_add.html.twig", array("form" => $form->createView()));
    }
    public function editQuoteAction(Request $request,$id)
    {
        $em=$this->getDoctrine()->getManager();
        $status=$em->getRepository("AppBundle:Status")->findOneBy(array("id"=>$id,"review"=>false));
        if ($status==null) {
            throw new NotFoundHttpException("Page not found");
        }
        $status->setTitle(base64_decode($status->getTitle()));

        $form = $this->createForm(new QuoteType(),$status);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $status->setDescription($this->remove_emoji($status->getTitle()));
            $status->setTitle(base64_encode($status->getTitle()));
            $em->persist($status);
            $em->flush();
            $this->addFlash('success', 'Operation has been done successfully');
            return $this->redirect($this->generateUrl('app_status_index'));
        }
        return $this->render("AppBundle:Status:quote_edit.html.twig",array("form"=>$form->createView()));
    }
	public function addImageAction(Request $request) {
		$video = new Status();
		$video->setType("image");
		$form = $this->createForm(new ImageType(), $video);
		$em = $this->getDoctrine()->getManager();

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			if ($video->getFile() != null) {
				$media = new Media();
				$media->setFile($video->getFile());
				$media->setEnabled(true);
				$media->upload($this->container->getParameter('files_directory'));

				$video->setMedia($media);

				$video->setUser($this->getUser());
				$video->setReview(false);
				$video->setDownloads(0);
				$em->persist($media);
				$em->flush();

				$em->persist($video);
				$em->flush();
				$this->addFlash('success', 'Operation has been done successfully');
				return $this->redirect($this->generateUrl('app_status_index'));
			} else {
				$photo_error = new FormError("Required image file");
				$form->get('file')->addError($photo_error);
			}
		}
		return $this->render("AppBundle:Status:image_add.html.twig", array("form" => $form->createView()));
	}
    public function editImageAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $video = $em->getRepository("AppBundle:Status")->findOneBy(array("id" => $id, "review" => false));
        if ($video == null) {
            throw new NotFoundHttpException("Page not found");
        }
        $form = $this->createForm(new ImageType(), $video);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($video->getFile() != null) {
                $media = new Media();
                $media_old = $video->getMedia();
                $media->setFile($video->getFile());
                $media->setEnabled(true);
                $media->upload($this->container->getParameter('files_directory'));
                $em->persist($media);
                $em->flush();
                $video->setMedia($media);
                $em->flush();
                $media_old->delete($this->container->getParameter('files_directory'));
                $em->remove($media_old);
                $em->flush();
            }
            $em->persist($video);
            $em->flush();
            $this->addFlash('success', 'Operation has been done successfully');
            return $this->redirect($this->generateUrl('app_status_index'));
        }
        return $this->render("AppBundle:Status:image_edit.html.twig", array("form" => $form->createView()));
    }

    public function addGifAction(Request $request) {
        $video = new Status();
        $video->setType("gif");
        $form = $this->createForm(new GifType(), $video);
        $em = $this->getDoctrine()->getManager();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($video->getFilegif() != null) {
                $media = new Media();
                $media->setFile($video->getFilegif());
                $media->setEnabled(true);
                $media->upload($this->container->getParameter('files_directory'));

                $video->setMedia($media);

                $video->setUser($this->getUser());
                $video->setReview(false);
                $video->setDownloads(0);
                $em->persist($media);
                $em->flush();

                $em->persist($video);
                $em->flush();
                $this->addFlash('success', 'Operation has been done successfully');
                return $this->redirect($this->generateUrl('app_status_index'));
            } else {
                $photo_error = new FormError("Required image file");
                $form->get('filegif')->addError($photo_error);
            }
        }
        return $this->render("AppBundle:Status:gif_add.html.twig", array("form" => $form->createView()));
    }
    public function editGifAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $video = $em->getRepository("AppBundle:Status")->findOneBy(array("id" => $id, "review" => false));
        if ($video == null) {
            throw new NotFoundHttpException("Page not found");
        }
        $form = $this->createForm(new GifType(), $video);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($video->getFilegif() != null) {
                $media = new Media();
                $media_old = $video->getMedia();
                $media->setFile($video->getFilegif());
                $media->setEnabled(true);
                $media->upload($this->container->getParameter('files_directory'));
                $em->persist($media);
                $em->flush();
                $video->setMedia($media);
                $em->flush();
                $media_old->delete($this->container->getParameter('files_directory'));
                $em->remove($media_old);
                $em->flush();
            }
            $em->persist($video);
            $em->flush();
            $this->addFlash('success', 'Operation has been done successfully');
            return $this->redirect($this->generateUrl('app_status_index'));
        }
        return $this->render("AppBundle:Status:gif_edit.html.twig", array("form" => $form->createView()));
    }

	
	public function api_add_angryAction(Request $request, $token) {
		if ($token != $this->container->getParameter('token_app')) {
			throw new NotFoundHttpException("Page not found");
		}
		$id = $request->get("id");
		$em = $this->getDoctrine()->getManager();
		$video = $em->getRepository("AppBundle:Status")->find($id);
		if ($video == null) {
			throw new NotFoundHttpException("Page not found");
		}
		$video->setAngry($video->getAngry() + 1);
		$em->flush();
		$encoders = array(new XmlEncoder(), new JsonEncoder());
		$normalizers = array(new ObjectNormalizer());
		$serializer = new Serializer($normalizers, $encoders);
		$jsonContent = $serializer->serialize($video->getAngry(), 'json');
		return new Response($jsonContent);
	}

	public function api_add_shareAction(Request $request, $token) {
        if ($token != $this->container->getParameter('token_app')) {
            throw new NotFoundHttpException("Page not found");
        }
        $em = $this->getDoctrine()->getManager();
        $id = $request->get("id");
        $userId = $request->get("user");
        $userKey = $request->get("key");
        $status = $em->getRepository("AppBundle:Status")->findOneBy(array("id"=>$id,"enabled"=>true));
        if ($status == null) {
            throw new NotFoundHttpException("Page not found");
        }
        if($userId){
            $user = $em->getRepository("UserBundle:User")->find($userId);
            if ($user) {
                if (sha1($user->getPassword()) == $userKey) {

                    $type =  $status->getType();
                    if ($type=="fullscreen") {
                       $type = "video";
                    }

                    $transaction = $em->getRepository("AppBundle:Transaction")->findOneBy(array("user"=>$user,"status"=>$status,"type"=>"share_".$type));
                    if ($transaction==null) {
                        $transaction = new Transaction();
                        $setting = $em->getRepository("AppBundle:Settings")->findOneBy(array());
                        $transaction->setPoints($setting->getPoints("share".$type));
                        $transaction->setStatus($status);
                        $transaction->setUser($user);
                        $transaction->setType("share_".$type);
                        $em->persist($transaction);
                        $em->flush();
                    }
                }
            }
        }        

        $status->setDownloads($status->getDownloads() + 1);
        $em->flush();
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $jsonContent = $serializer->serialize($status->getDownloads(), 'json');
        return new Response($jsonContent);
	}
    public function api_add_viewAction(Request $request, $token) {
        if ($token != $this->container->getParameter('token_app')) {
            throw new NotFoundHttpException("Page not found");
        }
        $em = $this->getDoctrine()->getManager();
        $hash = $request->get("id");
        $id = base64_decode($hash);
        $id = $id - 55463938;
        $userId = $request->get("user");
        $userKey = $request->get("key");
        $status = $em->getRepository("AppBundle:Status")->findOneBy(array("id"=>$id,"enabled"=>true));
        if ($status == null) {
            throw new NotFoundHttpException("Page not found");
        }
        if($userId){
            $user = $em->getRepository("UserBundle:User")->find($userId);
            if ($user) {
                if (sha1($user->getPassword()) == $userKey) {


                    $type =  $status->getType();
                    if ($type=="fullscreen") {
                       $type = "video";
                    }

                    $transaction = $em->getRepository("AppBundle:Transaction")->findOneBy(array("user"=>$user,"status"=>$status,"type"=>"view_".$type));
                    if ($transaction==null) {
                        $transaction = new Transaction();
                        $setting = $em->getRepository("AppBundle:Settings")->findOneBy(array());
                        $transaction->setPoints($setting->getPoints("view".$type));
                        $transaction->setStatus($status);
                        $transaction->setUser($user);
                        $transaction->setType("view_".$type);
                        $em->persist($transaction);
                        $em->flush();
                    }
                }
            }
        }        

        $status->setViews($status->getViews() + 1);
        $em->flush();
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $jsonContent = $serializer->serialize($status->getViews(), 'json');
        return new Response($jsonContent);
    }

	public function api_add_hahaAction(Request $request, $token) {
		if ($token != $this->container->getParameter('token_app')) {
			throw new NotFoundHttpException("Page not found");
		}
		$id = $request->get("id");
		$em = $this->getDoctrine()->getManager();
		$video = $em->getRepository("AppBundle:Status")->find($id);
		if ($video == null) {
			throw new NotFoundHttpException("Page not found");
		}
		$video->setHaha($video->getHaha() + 1);
		$em->flush();
		$encoders = array(new XmlEncoder(), new JsonEncoder());
		$normalizers = array(new ObjectNormalizer());
		$serializer = new Serializer($normalizers, $encoders);
		$jsonContent = $serializer->serialize($video->getHaha(), 'json');
		return new Response($jsonContent);
	}

	public function api_add_likeAction(Request $request, $token) {
		if ($token != $this->container->getParameter('token_app')) {
			throw new NotFoundHttpException("Page not found");
		}
		$id = $request->get("id");
		$em = $this->getDoctrine()->getManager();
		$video = $em->getRepository("AppBundle:Status")->find($id);
		if ($video == null) {
			throw new NotFoundHttpException("Page not found");
		}
		$video->setLike($video->getLike() + 1);
		$em->flush();
		$encoders = array(new XmlEncoder(), new JsonEncoder());
		$normalizers = array(new ObjectNormalizer());
		$serializer = new Serializer($normalizers, $encoders);
		$jsonContent = $serializer->serialize($video->getLike(), 'json');
		return new Response($jsonContent);
	}

	public function api_add_loveAction(Request $request, $token) {
		if ($token != $this->container->getParameter('token_app')) {
			throw new NotFoundHttpException("Page not found");
		}
		$id = $request->get("id");
		$em = $this->getDoctrine()->getManager();
		$video = $em->getRepository("AppBundle:Status")->find($id);
		if ($video == null) {
			throw new NotFoundHttpException("Page not found");
		}
		$video->setLove($video->getLove() + 1);
		$em->flush();
		$encoders = array(new XmlEncoder(), new JsonEncoder());
		$normalizers = array(new ObjectNormalizer());
		$serializer = new Serializer($normalizers, $encoders);
		$jsonContent = $serializer->serialize($video->getLove(), 'json');
		return new Response($jsonContent);
	}

	public function api_add_sadAction(Request $request, $token) {
		if ($token != $this->container->getParameter('token_app')) {
			throw new NotFoundHttpException("Page not found");
		}
		$id = $request->get("id");
		$em = $this->getDoctrine()->getManager();
		$video = $em->getRepository("AppBundle:Status")->find($id);
		if ($video == null) {
			throw new NotFoundHttpException("Page not found");
		}
		$video->setSad($video->getSad() + 1);
		$em->flush();
		$encoders = array(new XmlEncoder(), new JsonEncoder());
		$normalizers = array(new ObjectNormalizer());
		$serializer = new Serializer($normalizers, $encoders);
		$jsonContent = $serializer->serialize($video->getSad(), 'json');
		return new Response($jsonContent);
	}

	public function api_add_woowAction(Request $request, $token) {
		if ($token != $this->container->getParameter('token_app')) {
			throw new NotFoundHttpException("Page not found");
		}
		$id = $request->get("id");
		$em = $this->getDoctrine()->getManager();
		$video = $em->getRepository("AppBundle:Status")->find($id);
		if ($video == null) {
			throw new NotFoundHttpException("Page not found");
		}
		$video->setWoow($video->getWoow() + 1);
		$em->flush();
		$encoders = array(new XmlEncoder(), new JsonEncoder());
		$normalizers = array(new ObjectNormalizer());
		$serializer = new Serializer($normalizers, $encoders);
		$jsonContent = $serializer->serialize($video->getWoow(), 'json');
		return new Response($jsonContent);
	}
    public function api_fullscreenAction(Request $request, $page, $order, $language, $token) {
        if ($token != $this->container->getParameter('token_app')) {
            throw new NotFoundHttpException("Page not found");
        }
        $nombre = 30;
        $em = $this->getDoctrine()->getManager();
        $imagineCacheManager = $this->get('liip_imagine.cache.manager');
        $repository = $em->getRepository('AppBundle:Status');

        if ($language == 0) {
            $query = $repository->createQueryBuilder('w')
                ->where("w.enabled = true","w.type like 'fullscreen'")
                ->addOrderBy('w.' . $order, 'DESC')
                ->addOrderBy('w.id', 'asc')
                ->setFirstResult($nombre * $page)
                ->setMaxResults($nombre)
                ->getQuery();
        } else {
            $query = $repository->createQueryBuilder('w')
                ->leftJoin('w.languages', 'l')
                ->where('l.id in (' . $language . ')', "w.enabled = true","w.type like 'fullscreen'")
                ->addOrderBy('w.' . $order, 'DESC')
                ->addOrderBy('w.id', 'asc')
                ->setFirstResult($nombre * $page)
                ->setMaxResults($nombre)
                ->getQuery();
        }
        $videos = $query->getResult();
        return $this->render('AppBundle:Status:api_all.html.php', array("videos" => $videos));
    }
	public function api_allAction(Request $request, $page, $order, $language, $token) {
		if ($token != $this->container->getParameter('token_app')) {
			throw new NotFoundHttpException("Page not found");
		}
		$nombre = 30;
		$em = $this->getDoctrine()->getManager();
		$imagineCacheManager = $this->get('liip_imagine.cache.manager');
		$repository = $em->getRepository('AppBundle:Status');

		if ($language == 0) {
			$query = $repository->createQueryBuilder('w')
				->where("w.enabled = true","w.type not like 'fullscreen'")
				->addOrderBy('w.' . $order, 'DESC')
				->addOrderBy('w.id', 'asc')
				->setFirstResult($nombre * $page)
				->setMaxResults($nombre)
				->getQuery();
		} else {
			$query = $repository->createQueryBuilder('w')
				->leftJoin('w.languages', 'l')
				->where('l.id in (' . $language . ')', "w.enabled = true","w.type not like 'fullscreen'")
				->addOrderBy('w.' . $order, 'DESC')
				->addOrderBy('w.id', 'asc')
				->setFirstResult($nombre * $page)
				->setMaxResults($nombre)
				->getQuery();
		}
		$videos = $query->getResult();
		return $this->render('AppBundle:Status:api_all.html.php', array("videos" => $videos));
	}
    public function api_fullscreen_by_categoryAction(Request $request, $page, $order, $language, $category, $token) {
        if ($token != $this->container->getParameter('token_app')) {
            throw new NotFoundHttpException("Page not found");
        }
        $nombre = 30;
        $em = $this->getDoctrine()->getManager();
        $imagineCacheManager = $this->get('liip_imagine.cache.manager');
        $repository = $em->getRepository('AppBundle:Status');
        if ($language == 0) {
            $query = $repository->createQueryBuilder('w')
                ->leftJoin('w.categories', 'c')
                ->where('c.id = :category', "w.enabled = true","w.type like 'fullscreen'")
                ->setParameter('category', $category)
                ->addOrderBy('w.' . $order, 'DESC')
                ->addOrderBy('w.id', 'asc')
                ->setFirstResult($nombre * $page)
                ->setMaxResults($nombre)
                ->getQuery();
        } else {
            $query = $repository->createQueryBuilder('w')
                ->leftJoin('w.languages', 'l')
                ->leftJoin('w.categories', 'c')
                ->where('l.id in (' . $language . ')', "w.enabled = true","w.type like 'fullscreen'", 'c.id = :category')

                ->setParameter('category', $category)
                ->addOrderBy('w.' . $order, 'DESC')
                ->addOrderBy('w.id', 'asc')
                ->setFirstResult($nombre * $page)
                ->setMaxResults($nombre)
                ->getQuery();
        }
        $videos = $query->getResult();
        return $this->render('AppBundle:Status:api_all.html.php', array("videos" => $videos));
    }
	public function api_by_categoryAction(Request $request, $page, $order, $language, $category, $token) {
		if ($token != $this->container->getParameter('token_app')) {
			throw new NotFoundHttpException("Page not found");
		}
		$nombre = 30;
		$em = $this->getDoctrine()->getManager();
		$imagineCacheManager = $this->get('liip_imagine.cache.manager');
		$repository = $em->getRepository('AppBundle:Status');
		if ($language == 0) {
			$query = $repository->createQueryBuilder('w')
				->leftJoin('w.categories', 'c')
				->where('c.id = :category', "w.enabled = true","w.type not like 'fullscreen'")
				->setParameter('category', $category)
				->addOrderBy('w.' . $order, 'DESC')
				->addOrderBy('w.id', 'asc')
				->setFirstResult($nombre * $page)
				->setMaxResults($nombre)
				->getQuery();
		} else {
			$query = $repository->createQueryBuilder('w')
				->leftJoin('w.languages', 'l')
				->leftJoin('w.categories', 'c')
				->where('l.id in (' . $language . ')', "w.enabled = true", 'c.id = :category',"w.type not like 'fullscreen'")

				->setParameter('category', $category)
				->addOrderBy('w.' . $order, 'DESC')
				->addOrderBy('w.id', 'asc')
				->setFirstResult($nombre * $page)
				->setMaxResults($nombre)
				->getQuery();
		}
		$videos = $query->getResult();
		return $this->render('AppBundle:Status:api_all.html.php', array("videos" => $videos));
	}
    public function api_by_follow_fullscreenAction(Request $request, $page, $language, $user, $token) {
        if ($token != $this->container->getParameter('token_app')) {
            throw new NotFoundHttpException("Page not found");
        }
        $nombre = 30;
        $em = $this->getDoctrine()->getManager();
        $imagineCacheManager = $this->get('liip_imagine.cache.manager');
        $repository = $em->getRepository('AppBundle:Status');
        if ($language == 0) {
            $query = $repository->createQueryBuilder('w')
                ->leftJoin('w.user', 'u')
                ->leftJoin('u.followers', 'f')
                ->where('f.id = ' . $user, "w.enabled = true","w.type like  'fullscreen'")
                ->addOrderBy('w.created', 'DESC')
                ->addOrderBy('w.id', 'asc')
                ->setFirstResult($nombre * $page)
                ->setMaxResults($nombre)
                ->getQuery();
        } else {
            $query = $repository->createQueryBuilder('w')
                ->leftJoin('w.languages', 'l')
                ->leftJoin('w.user', 'u')
                ->leftJoin('u.followers', 'f')
                ->where('l.id in (' . $language . ')', 'f.id =' . $user, "w.enabled = true","w.type like  'fullscreen'")
                ->addOrderBy('w.created', 'DESC')
                ->addOrderBy('w.id', 'asc')
                ->setFirstResult($nombre * $page)
                ->setMaxResults($nombre)
                ->getQuery();
        }
        $videos = $query->getResult();
        return $this->render('AppBundle:Status:api_all.html.php', array("videos" => $videos));
    }
	public function api_by_followAction(Request $request, $page, $language, $user, $token) {
		if ($token != $this->container->getParameter('token_app')) {
			throw new NotFoundHttpException("Page not found");
		}
		$nombre = 30;
		$em = $this->getDoctrine()->getManager();
		$imagineCacheManager = $this->get('liip_imagine.cache.manager');
		$repository = $em->getRepository('AppBundle:Status');
		if ($language == 0) {
			$query = $repository->createQueryBuilder('w')
				->leftJoin('w.user', 'u')
				->leftJoin('u.followers', 'f')
				->where('f.id = ' . $user, "w.enabled = true","w.type not like  'fullscreen'")
				->addOrderBy('w.created', 'DESC')
				->addOrderBy('w.id', 'asc')
				->setFirstResult($nombre * $page)
				->setMaxResults($nombre)
				->getQuery();
		} else {
			$query = $repository->createQueryBuilder('w')
				->leftJoin('w.languages', 'l')
				->leftJoin('w.user', 'u')
				->leftJoin('u.followers', 'f')
				->where('l.id in (' . $language . ')', 'f.id =' . $user, "w.enabled = true","w.type not like  'fullscreen'")
				->addOrderBy('w.created', 'DESC')
				->addOrderBy('w.id', 'asc')
				->setFirstResult($nombre * $page)
				->setMaxResults($nombre)
				->getQuery();
		}
		$videos = $query->getResult();
		return $this->render('AppBundle:Status:api_all.html.php', array("videos" => $videos));
	}
    public function api_by_me_fullscreenAction(Request $request, $page, $user, $token) {
        if ($token != $this->container->getParameter('token_app')) {
            throw new NotFoundHttpException("Page not found");
        }
        $nombre = 30;
        $em = $this->getDoctrine()->getManager();
        $imagineCacheManager = $this->get('liip_imagine.cache.manager');
        $repository = $em->getRepository('AppBundle:Status');
        $query = $repository->createQueryBuilder('w')
            ->where('w.user = :user',"w.type like 'fullscreen'")
            ->setParameter('user', $user)
            ->addOrderBy('w.created', 'DESC')
            ->addOrderBy('w.id', 'asc')
            ->setFirstResult($nombre * $page)
            ->setMaxResults($nombre)
            ->getQuery();
        $videos = $query->getResult();
        return $this->render('AppBundle:Status:api_all.html.php', array("videos" => $videos));
    }
	public function api_by_meAction(Request $request, $page, $user, $token) {
		if ($token != $this->container->getParameter('token_app')) {
			throw new NotFoundHttpException("Page not found");
		}
		$nombre = 30;
		$em = $this->getDoctrine()->getManager();
		$imagineCacheManager = $this->get('liip_imagine.cache.manager');
		$repository = $em->getRepository('AppBundle:Status');
		$query = $repository->createQueryBuilder('w')
			->where('w.user = :user',"w.type not like  'fullscreen'")
			->setParameter('user', $user)
			->addOrderBy('w.created', 'DESC')
			->addOrderBy('w.id', 'asc')
			->setFirstResult($nombre * $page)
			->setMaxResults($nombre)
			->getQuery();
		$videos = $query->getResult();
		return $this->render('AppBundle:Status:api_all.html.php', array("videos" => $videos));
	}

	public function api_by_queryAction(Request $request, $order, $language, $page, $query, $token) {
		if ($token != $this->container->getParameter('token_app')) {
			throw new NotFoundHttpException("Page not found");
		}
		$nombre = 30;
		$em = $this->getDoctrine()->getManager();
		$imagineCacheManager = $this->get('liip_imagine.cache.manager');
		$repository = $em->getRepository('AppBundle:Status');
		if ($language == 0) {
			$query_dql = $repository->createQueryBuilder('w')
				->where("w.enabled = true", "LOWER(w.title) like LOWER('%" . $query . "%') OR LOWER(w.tags) like LOWER('%" . $query . "%')  OR LOWER(w.description) like LOWER('%" . $query . "%') ","w.type not like  'fullscreen'")
				->addOrderBy('w.' . $order, 'DESC')
				->addOrderBy('w.id', 'asc')
				->setFirstResult($nombre * $page)
				->setMaxResults($nombre)
				->getQuery();
		} else {
			$language = str_replace("_", ",", $language);
			$query_dql = $repository->createQueryBuilder('w')
				->leftJoin('w.languages', 'l')
				->where('l.id in (' . $language . ')', "LOWER(w.title) like LOWER('%" . $query . "%') OR LOWER(w.tags) like LOWER('%" . $query . "%') ","w.type not like  'fullscreen'")
				->addOrderBy('w.' . $order, 'DESC')
				->addOrderBy('w.id', 'asc')
				->setFirstResult($nombre * $page)
				->setMaxResults($nombre)
				->getQuery();
		}
		$videos = $query_dql->getResult();

		return $this->render('AppBundle:Status:api_all.html.php', array("videos" => $videos));
	}
    public function api_by_query_fullscreenAction(Request $request, $order, $language, $page, $query, $token) {
        if ($token != $this->container->getParameter('token_app')) {
            throw new NotFoundHttpException("Page not found");
        }
        $nombre = 30;
        $em = $this->getDoctrine()->getManager();
        $imagineCacheManager = $this->get('liip_imagine.cache.manager');
        $repository = $em->getRepository('AppBundle:Status');
        if ($language == 0) {
            $query_dql = $repository->createQueryBuilder('w')
                ->where("w.enabled = true", "LOWER(w.title) like LOWER('%" . $query . "%') OR LOWER(w.tags) like LOWER('%" . $query . "%')  OR LOWER(w.description) like LOWER('%" . $query . "%') ","w.type like  'fullscreen'")
                ->addOrderBy('w.' . $order, 'DESC')
                ->addOrderBy('w.id', 'asc')
                ->setFirstResult($nombre * $page)
                ->setMaxResults($nombre)
                ->getQuery();
        } else {
            $language = str_replace("_", ",", $language);
            $query_dql = $repository->createQueryBuilder('w')
                ->leftJoin('w.languages', 'l')
                ->where('l.id in (' . $language . ')', "LOWER(w.title) like LOWER('%" . $query . "%') OR LOWER(w.tags) like LOWER('%" . $query . "%') ","w.type like  'fullscreen'")
                ->addOrderBy('w.' . $order, 'DESC')
                ->addOrderBy('w.id', 'asc')
                ->setFirstResult($nombre * $page)
                ->setMaxResults($nombre)
                ->getQuery();
        }
        $videos = $query_dql->getResult();

        return $this->render('AppBundle:Status:api_all.html.php', array("videos" => $videos));
    }
    public function api_by_fullscreenrandomAction(Request $request, $language, $token) {
        if ($token != $this->container->getParameter('token_app')) {
            throw new NotFoundHttpException("Page not found");
        }

        $nombre = 6;
        $em = $this->getDoctrine()->getManager();
        $imagineCacheManager = $this->get('liip_imagine.cache.manager');
        $repository = $em->getRepository('AppBundle:Status');

        if ($language == 0) {
            $max = sizeof($repository->createQueryBuilder('g')
                    ->where("g.enabled = true","g.type like 'fullscreen'")
                    ->getQuery()->getResult());

            $query = $repository->createQueryBuilder('g')
                ->where("g.enabled = true","g.type like 'fullscreen'")
                ->orderBy('g.created', 'DESC')
                ->setFirstResult(rand(0, $max-2))
                ->setMaxResults($nombre)
                ->orderBy('g.downloads', 'DESC')
                ->getQuery();
        } else {
            $max = sizeof($repository->createQueryBuilder('g')
                    ->leftJoin('g.languages', 'l')
                    ->where('l.id in (' . $language . ')', "g.enabled = true","g.type like 'fullscreen'")

                    ->getQuery()->getResult());

            $query = $repository->createQueryBuilder('g')
                ->leftJoin('g.languages', 'l')
                ->where('l.id in (' . $language . ')', "g.enabled = true","g.type like 'fullscreen'")

                ->setFirstResult(rand(0, $max-2))
                ->orderBy('g.downloads', 'DESC')
                ->setMaxResults($nombre)
                ->getQuery();
        }

        $videos = $query->getResult();
        return $this->render('AppBundle:Status:api_all.html.php', array("videos" => $videos));
    }
	public function api_by_randomAction(Request $request, $language, $token) {
		if ($token != $this->container->getParameter('token_app')) {
			throw new NotFoundHttpException("Page not found");
		}

		$nombre = 6;
		$em = $this->getDoctrine()->getManager();
		$imagineCacheManager = $this->get('liip_imagine.cache.manager');
		$repository = $em->getRepository('AppBundle:Status');

		if ($language == 0) {
			$max = sizeof($repository->createQueryBuilder('g')
					->where("g.enabled = true","g.type not like 'fullscreen'")
					->getQuery()->getResult());

			$query = $repository->createQueryBuilder('g')
				->where("g.enabled = true","g.type not like  'fullscreen'")
				->orderBy('g.created', 'DESC')
				->setFirstResult(rand(0, $max-5))
				->setMaxResults($nombre)
				->orderBy('g.downloads', 'DESC')
				->getQuery();
		} else {
			$max = sizeof($repository->createQueryBuilder('g')
					->leftJoin('g.languages', 'l')
					->where('l.id in (' . $language . ')', "g.enabled = true","g.type not like  'fullscreen'")

					->getQuery()->getResult());

			$query = $repository->createQueryBuilder('g')
				->leftJoin('g.languages', 'l')
				->where('l.id in (' . $language . ')', "g.enabled = true","g.type not like  'fullscreen'")

                ->setFirstResult(rand(0, $max-5))
				->orderBy('g.downloads', 'DESC')
				->setMaxResults($nombre)
				->getQuery();
		}

		$videos = $query->getResult();
		return $this->render('AppBundle:Status:api_all.html.php', array("videos" => $videos));
	}
    public function api_by_user_fullscreenAction(Request $request, $page, $order, $language, $user, $token) {
        if ($token != $this->container->getParameter('token_app')) {
            throw new NotFoundHttpException("Page not found");
        }
        $nombre = 30;
        $em = $this->getDoctrine()->getManager();
        $imagineCacheManager = $this->get('liip_imagine.cache.manager');
        $repository = $em->getRepository('AppBundle:Status');
        if ($language == 0) {
            $query = $repository->createQueryBuilder('w')
                ->where('w.user = :user', "w.enabled = true","w.type  like 'fullscreen'")
                ->setParameter('user', $user)
                ->addOrderBy('w.' . $order, 'DESC')
                ->addOrderBy('w.id', 'asc')
                ->setFirstResult($nombre * $page)
                ->setMaxResults($nombre)
                ->getQuery();
        } else {
            $query = $repository->createQueryBuilder('w')
                ->leftJoin('w.languages', 'l')
                ->where('l.id in (' . $language . ')', "w.enabled = true", 'w.user = :user',"w.type  like 'fullscreen'")
                ->setParameter('user', $user)
                ->addOrderBy('w.' . $order, 'DESC')
                ->addOrderBy('w.id', 'asc')
                ->setFirstResult($nombre * $page)
                ->setMaxResults($nombre)
                ->getQuery();
        }
        $videos = $query->getResult();
        return $this->render('AppBundle:Status:api_all.html.php', array("videos" => $videos));
    }
	public function api_by_userAction(Request $request, $page, $order, $language, $user, $token) {
		if ($token != $this->container->getParameter('token_app')) {
			throw new NotFoundHttpException("Page not found");
		}
		$nombre = 30;
		$em = $this->getDoctrine()->getManager();
		$imagineCacheManager = $this->get('liip_imagine.cache.manager');
		$repository = $em->getRepository('AppBundle:Status');
		if ($language == 0) {
			$query = $repository->createQueryBuilder('w')
				->where('w.user = :user', "w.enabled = true","w.type not like 'fullscreen'")
				->setParameter('user', $user)
				->addOrderBy('w.' . $order, 'DESC')
				->addOrderBy('w.id', 'asc')
				->setFirstResult($nombre * $page)
				->setMaxResults($nombre)
				->getQuery();
		} else {
			$query = $repository->createQueryBuilder('w')
				->leftJoin('w.languages', 'l')
				->where('l.id in (' . $language . ')', "w.enabled = true", 'w.user = :user',"w.type not like 'fullscreen'")

				->setParameter('user', $user)
				->addOrderBy('w.' . $order, 'DESC')
				->addOrderBy('w.id', 'asc')
				->setFirstResult($nombre * $page)
				->setMaxResults($nombre)
				->getQuery();
		}
		$videos = $query->getResult();
		return $this->render('AppBundle:Status:api_all.html.php', array("videos" => $videos));
	}

	public function api_delete_angryAction(Request $request, $token) {
		if ($token != $this->container->getParameter('token_app')) {
			throw new NotFoundHttpException("Page not found");
		}
		$id = $request->get("id");
		$em = $this->getDoctrine()->getManager();
		$video = $em->getRepository("AppBundle:Status")->find($id);
		if ($video == null) {
			throw new NotFoundHttpException("Page not found");
		}
		$video->setAngry($video->getAngry() - 1);
		$em->flush();
		$encoders = array(new XmlEncoder(), new JsonEncoder());
		$normalizers = array(new ObjectNormalizer());
		$serializer = new Serializer($normalizers, $encoders);
		$jsonContent = $serializer->serialize($video->getAngry(), 'json');
		return new Response($jsonContent);
	}

	public function api_delete_hahaAction(Request $request, $token) {
		if ($token != $this->container->getParameter('token_app')) {
			throw new NotFoundHttpException("Page not found");
		}
		$id = $request->get("id");
		$em = $this->getDoctrine()->getManager();
		$video = $em->getRepository("AppBundle:Status")->find($id);
		if ($video == null) {
			throw new NotFoundHttpException("Page not found");
		}
		$video->setHaha($video->getHaha() - 1);
		$em->flush();
		$encoders = array(new XmlEncoder(), new JsonEncoder());
		$normalizers = array(new ObjectNormalizer());
		$serializer = new Serializer($normalizers, $encoders);
		$jsonContent = $serializer->serialize($video->getHaha(), 'json');
		return new Response($jsonContent);
	}

	public function api_delete_likeAction(Request $request, $token) {
		if ($token != $this->container->getParameter('token_app')) {
			throw new NotFoundHttpException("Page not found");
		}
		$id = $request->get("id");
		$em = $this->getDoctrine()->getManager();
		$video = $em->getRepository("AppBundle:Status")->find($id);
		if ($video == null) {
			throw new NotFoundHttpException("Page not found");
		}
		$video->setLike($video->getLike() - 1);
		$em->flush();
		$encoders = array(new XmlEncoder(), new JsonEncoder());
		$normalizers = array(new ObjectNormalizer());
		$serializer = new Serializer($normalizers, $encoders);
		$jsonContent = $serializer->serialize($video->getLike(), 'json');
		return new Response($jsonContent);
	}

	public function api_delete_loveAction(Request $request, $token) {
		if ($token != $this->container->getParameter('token_app')) {
			throw new NotFoundHttpException("Page not found");
		}
		$id = $request->get("id");
		$em = $this->getDoctrine()->getManager();
		$video = $em->getRepository("AppBundle:Status")->find($id);
		if ($video == null) {
			throw new NotFoundHttpException("Page not found");
		}
		$video->setLove($video->getLove() - 1);
		$em->flush();
		$encoders = array(new XmlEncoder(), new JsonEncoder());
		$normalizers = array(new ObjectNormalizer());
		$serializer = new Serializer($normalizers, $encoders);
		$jsonContent = $serializer->serialize($video->getLove(), 'json');
		return new Response($jsonContent);
	}

	public function api_delete_sadAction(Request $request, $token) {
		if ($token != $this->container->getParameter('token_app')) {
			throw new NotFoundHttpException("Page not found");
		}
		$id = $request->get("id");
		$em = $this->getDoctrine()->getManager();
		$video = $em->getRepository("AppBundle:Status")->find($id);
		if ($video == null) {
			throw new NotFoundHttpException("Page not found");
		}
		$video->setSad($video->getSad() - 1);
		$em->flush();
		$encoders = array(new XmlEncoder(), new JsonEncoder());
		$normalizers = array(new ObjectNormalizer());
		$serializer = new Serializer($normalizers, $encoders);
		$jsonContent = $serializer->serialize($video->getSad(), 'json');
		return new Response($jsonContent);
	}

	public function api_delete_woowAction(Request $request, $token) {
		if ($token != $this->container->getParameter('token_app')) {
			throw new NotFoundHttpException("Page not found");
		}
		$id = $request->get("id");
		$em = $this->getDoctrine()->getManager();
		$video = $em->getRepository("AppBundle:Status")->find($id);
		if ($video == null) {
			throw new NotFoundHttpException("Page not found");
		}
		$video->setWoow($video->getWoow() - 1);
		$em->flush();
		$encoders = array(new XmlEncoder(), new JsonEncoder());
		$normalizers = array(new ObjectNormalizer());
		$serializer = new Serializer($normalizers, $encoders);
		$jsonContent = $serializer->serialize($video->getWoow(), 'json');
		return new Response($jsonContent);
	}

	public function api_myAction(Request $request, $page, $user, $token) {
		if ($token != $this->container->getParameter('token_app')) {
			throw new NotFoundHttpException("Page not found");
		}
		$nombre = 30;
		$em = $this->getDoctrine()->getManager();
		$imagineCacheManager = $this->get('liip_imagine.cache.manager');
		$repository = $em->getRepository('AppBundle:Status');
		$query = $repository->createQueryBuilder('w')
			->leftJoin('w.user', 'c')
			->where('c.id = :user')
			->setParameter('user', $user)
			->addOrderBy('w.created', 'DESC')
			->addOrderBy('w.id', 'asc')
			->setFirstResult($nombre * $page)
			->setMaxResults($nombre)
			->getQuery();

		$videos = $query->getResult();
		return $this->render('AppBundle:Status:api_all.html.php', array("videos" => $videos));
	}

    public function api_uploadQuoteAction(Request $request, $token) {

        $id = $request->get("id");
        $key = $request->get("key");
        $quote = $request->get("quote");
        $color = $request->get("color");
        $font = $request->get("font");

        $language_ids = $request->get("language");
        $language_list = explode("_", $language_ids);

        $categories_ids = $request->get("categories");
        $categories_list = explode("_", $categories_ids);

        $code = "200";
        $message = "Ok";
        $values = array();
        if ($token != $this->container->getParameter('token_app')) {
            throw new NotFoundHttpException("Page not found");
        }
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('UserBundle:User')->findOneBy(array("id" => $id));
        if ($user == null) {
            throw new NotFoundHttpException("Page not found");
        }
        if (sha1($user->getPassword()) != $key) {
            throw new NotFoundHttpException("Page not found");
        }
        if ($user) {

                $w = new Status();
                $w->setType("quote");
                $w->setColor($color);
                $w->setFont($font);
                $w->setDownloads(0);
                if (!$user->getTrusted()) {
                    $w->setEnabled(false);
                    $w->setReview(true); 
                }else{
                    $w->setEnabled(true);
                    $w->setReview(false);         
                }

                $w->setComment(true);
                $w->setDescription($this->remove_emoji(base64_decode($quote)));
                $w->setTitle($quote);

                $w->setUser($user);

                foreach ($language_list as $key => $id_language) {
                    $language_obj = $em->getRepository('AppBundle:Language')->find($id_language);
                    if ($language_obj) {
                        $w->addlanguage($language_obj);
                    }
                }
                foreach ($categories_list as $key => $id_category) {
                    $category_obj = $em->getRepository('AppBundle:Category')->find($id_category);
                    if ($category_obj) {
                        $w->addCategory($category_obj);
                    }
                }

                $em->persist($w);
                $em->flush();

                if ($user->getTrusted()) {
                    $transaction = new Transaction();
                    $setting = $em->getRepository("AppBundle:Settings")->findOneBy(array());
                    $transaction->setPoints($setting->getPoints("add".$w->getType()));
                    $transaction->setStatus($w);
                    $transaction->setUser($user);
                    $transaction->setType("add_".$w->getType());
                    $em->persist($transaction);
                    $em->flush();
                    $this->sendNotif($em,$w);
                }

            
        }
        $error = array(
            "code" => $code,
            "message" => $message,
            "values" => $values,
        );
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $jsonContent = $serializer->serialize($error, 'json');
        return new Response($jsonContent);
    }
    public function sendNotif($em,$selected_status){
            $user= $selected_status->getUser();
             if ($user==null) {
                throw new NotFoundHttpException("Page not found");  
            }
            $tokens=array();

            $tokens[]=$user->getToken();
            $original = "";
            $thumbnail = "";
            $type = "";
            $extension = "";
            $color = "";
            $imagineCacheManager = $this->get('liip_imagine.cache.manager');

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
                  "title"=>"👍👍 status approved ❤️❤️",
                  "message"=>"😀😀 Congratulation you status has been approved ❤️❤️",
                  "image"=> "",
                  "icon"=>""
                );

             $setting = $em->getRepository('AppBundle:Settings')->findOneBy(array());            
             $key=$setting->getFirebasekey();
             $message_image = $this->send_notificationToken($tokens, $message,$key); 
    }
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
	public function api_uploadAction(Request $request, $token) {
        $type = "video";
		$id = str_replace('"', '', $request->get("id"));
		$key = str_replace('"', '', $request->get("key"));
        $title = str_replace('"', '', $request->get("title"));
        if ($request->request->has("type")) {
           $type  = str_replace('"', '', $request->get("type"));
        }
		 
		$description = str_replace('"', '', $request->get("description"));

		$language_ids = str_replace('"', '', $request->get("language"));
		$language_list = explode("_", $language_ids);

		$categories_ids = str_replace('"', '', $request->get("categories"));
		$categories_list = explode("_", $categories_ids);

		$code = "200";
		$message = "Ok";
		$values = array();
		if ($token != $this->container->getParameter('token_app')) {
			throw new NotFoundHttpException("Page not found");
		}
		$em = $this->getDoctrine()->getManager();
		$user = $em->getRepository('UserBundle:User')->findOneBy(array("id" => $id));
		if ($user == null) {
			throw new NotFoundHttpException("Page not found");
		}
		if (sha1($user->getPassword()) != $key) {
			throw new NotFoundHttpException("Page not found");
		}
		if ($user) {

			if ($this->getRequest()->files->has('uploaded_file')) {
				// $old_media=$user->getMedia();
				$file = $this->getRequest()->files->get('uploaded_file');
				$file_thum = $this->getRequest()->files->get('uploaded_file_thum');

				$media = new Media();
				$media->setFile($file);
				$media->upload($this->container->getParameter('files_directory'));
				$em->persist($media);
				$em->flush();

				$media_thum = new Media();
				$media_thum->setFile($file_thum);
				$media_thum->upload($this->container->getParameter('files_directory'));
				$em->persist($media_thum);
				$em->flush();

				$w = new Status();
				$w->setType($type);
				$w->setDownloads(0);
				//$w->setCategories($wallpaper->getCategories());
				//$w->setColors($wallpaper->getColors());

                if (!$user->getTrusted()) {
                    $w->setEnabled(false);
                    $w->setReview(true); 
                }else{
                    $w->setEnabled(true);
                    $w->setReview(false); 
                }
				$w->setComment(true);
				$w->setTitle($title);
				$w->setDescription($description);
				$w->setUser($user);
				$w->setVideo($media);
				$w->setMedia($media_thum);

				foreach ($language_list as $key => $id_language) {
					$language_obj = $em->getRepository('AppBundle:Language')->find($id_language);
					if ($language_obj) {
						$w->addlanguage($language_obj);
					}
				}
				foreach ($categories_list as $key => $id_category) {
					$category_obj = $em->getRepository('AppBundle:Category')->find($id_category);
					if ($category_obj) {
						$w->addCategory($category_obj);
					}
				}

				$em->persist($w);
				$em->flush();
                if ($user->getTrusted()) {
                    $transaction = new Transaction();
                    $setting = $em->getRepository("AppBundle:Settings")->findOneBy(array());
                    $transaction->setPoints($setting->getPoints("add".$w->getType()));
                    $transaction->setStatus($w);
                    $transaction->setUser($user);
                    $transaction->setType("add_".$w->getType());
                    $em->persist($transaction);
                    $em->flush();
                    $this->sendNotif($em,$w);
                }
			}
		}
		$error = array(
			"code" => $code,
			"message" => $message,
			"values" => $values,
		);
		$encoders = array(new XmlEncoder(), new JsonEncoder());
		$normalizers = array(new ObjectNormalizer());
		$serializer = new Serializer($normalizers, $encoders);
		$jsonContent = $serializer->serialize($error, 'json');
		return new Response($jsonContent);
	}

    public function api_uploadGifAction(Request $request, $token) {

        $id = str_replace('"', '', $request->get("id"));
        $key = str_replace('"', '', $request->get("key"));
        $title = str_replace('"', '', $request->get("title"));
        $description = str_replace('"', '', $request->get("description"));

        $language_ids = str_replace('"', '', $request->get("language"));
        $language_list = explode("_", $language_ids);

        $categories_ids = str_replace('"', '', $request->get("categories"));
        $categories_list = explode("_", $categories_ids);

        $code = "200";
        $message = "Ok";
        $values = array();
        if ($token != $this->container->getParameter('token_app')) {
            throw new NotFoundHttpException("Page not found");
        }
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('UserBundle:User')->findOneBy(array("id" => $id));
        if ($user == null) {
            throw new NotFoundHttpException("Page not found");
        }
        if (sha1($user->getPassword()) != $key) {
            throw new NotFoundHttpException("Page not found");
        }
        if ($user) {

            if ($this->getRequest()->files->has('uploaded_file')) {
                // $old_media=$user->getMedia();
                $file_thum = $this->getRequest()->files->get('uploaded_file');

                $media_thum = new Media();
                $media_thum->setFile($file_thum);
                $media_thum->upload($this->container->getParameter('files_directory'));
                $em->persist($media_thum);
                $em->flush();

                $w = new Status();
                $w->setType("gif");
                $w->setDownloads(0);
                //$w->setCategories($wallpaper->getCategories());
                //$w->setColors($wallpaper->getColors());
                if (!$user->getTrusted()) {
                    $w->setEnabled(false);
                    $w->setReview(true); 
                }else{
                    $w->setEnabled(true);
                    $w->setReview(false); 
                }
                $w->setComment(true);
                $w->setTitle($title);
                $w->setDescription($description);
                $w->setUser($user);
                $w->setMedia($media_thum);

                foreach ($language_list as $key => $id_language) {
                    $language_obj = $em->getRepository('AppBundle:Language')->find($id_language);
                    if ($language_obj) {
                        $w->addlanguage($language_obj);
                    }
                }
                foreach ($categories_list as $key => $id_category) {
                    $category_obj = $em->getRepository('AppBundle:Category')->find($id_category);
                    if ($category_obj) {
                        $w->addCategory($category_obj);
                    }
                }

                $em->persist($w);
                $em->flush();


                if ($user->getTrusted()) {
                    $transaction = new Transaction();
                    $setting = $em->getRepository("AppBundle:Settings")->findOneBy(array());
                    $transaction->setPoints($setting->getPoints("add".$w->getType()));
                    $transaction->setStatus($w);
                    $transaction->setUser($user);
                    $transaction->setType("add_".$w->getType());
                    $em->persist($transaction);
                    $em->flush();
                    $this->sendNotif($em,$w);

                }
            }
        }
        $error = array(
            "code" => $code,
            "message" => $message,
            "values" => $values,
        );
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $jsonContent = $serializer->serialize($error, 'json');
        return new Response($jsonContent);
    }
	public function api_uploadImageAction(Request $request, $token) {

		$id = str_replace('"', '', $request->get("id"));
		$key = str_replace('"', '', $request->get("key"));
		$title = str_replace('"', '', $request->get("title"));
		$description = str_replace('"', '', $request->get("description"));

		$language_ids = str_replace('"', '', $request->get("language"));
		$language_list = explode("_", $language_ids);

		$categories_ids = str_replace('"', '', $request->get("categories"));
		$categories_list = explode("_", $categories_ids);

		$code = "200";
		$message = "Ok";
		$values = array();
		if ($token != $this->container->getParameter('token_app')) {
			throw new NotFoundHttpException("Page not found");
		}
		$em = $this->getDoctrine()->getManager();
		$user = $em->getRepository('UserBundle:User')->findOneBy(array("id" => $id));
		if ($user == null) {
			throw new NotFoundHttpException("Page not found");
		}
		if (sha1($user->getPassword()) != $key) {
			throw new NotFoundHttpException("Page not found");
		}
		if ($user) {

			if ($this->getRequest()->files->has('uploaded_file')) {
				// $old_media=$user->getMedia();
				$file_thum = $this->getRequest()->files->get('uploaded_file');

				$media_thum = new Media();
				$media_thum->setFile($file_thum);
				$media_thum->upload($this->container->getParameter('files_directory'));
				$em->persist($media_thum);
				$em->flush();

				$w = new Status();
				$w->setType("image");
				$w->setDownloads(0);
				//$w->setCategories($wallpaper->getCategories());
				//$w->setColors($wallpaper->getColors());
                if (!$user->getTrusted()) {
                    $w->setEnabled(false);
                    $w->setReview(true); 
                }else{
                    $w->setEnabled(true);
                    $w->setReview(false); 
                }
				$w->setComment(true);
				$w->setTitle($title);
				$w->setDescription($description);
				$w->setUser($user);
				$w->setMedia($media_thum);

				foreach ($language_list as $key => $id_language) {
					$language_obj = $em->getRepository('AppBundle:Language')->find($id_language);
					if ($language_obj) {
						$w->addlanguage($language_obj);
					}
				}
				foreach ($categories_list as $key => $id_category) {
					$category_obj = $em->getRepository('AppBundle:Category')->find($id_category);
					if ($category_obj) {
						$w->addCategory($category_obj);
					}
				}

				$em->persist($w);
				$em->flush();

                if ($user->getTrusted()) {
                    $transaction = new Transaction();
                    $setting = $em->getRepository("AppBundle:Settings")->findOneBy(array());
                    $transaction->setPoints($setting->getPoints("add".$w->getType()));
                    $transaction->setStatus($w);
                    $transaction->setUser($user);
                    $transaction->setType("add_".$w->getType());
                    $em->persist($transaction);
                    $em->flush();
                    $this->sendNotif($em,$w);

                }
			}
		}
		$error = array(
			"code" => $code,
			"message" => $message,
			"values" => $values,
		);
		$encoders = array(new XmlEncoder(), new JsonEncoder());
		$normalizers = array(new ObjectNormalizer());
		$serializer = new Serializer($normalizers, $encoders);
		$jsonContent = $serializer->serialize($error, 'json');
		return new Response($jsonContent);
	}

	public function deleteAction($id, Request $request) {
		$em = $this->getDoctrine()->getManager();

		$video = $em->getRepository("AppBundle:Status")->find($id);
		if ($video == null) {
			throw new NotFoundHttpException("Page not found");
		}

		$form = $this->createFormBuilder(array('id' => $id))
			->add('id', 'hidden')
			->add('Yes', 'submit')
			->getForm();
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$media_old_video = null;
			$media_old_thumb = null;
			if ($video->getVideo() != null) {
				$media_old_video = $video->getVideo();
			}
			if ($video->getMedia() != null) {
				$media_old_thumb = $video->getMedia();
			}
			$em->remove($video);
			$em->flush();
			if ($media_old_thumb != null) {
				$media_old_thumb->delete($this->container->getParameter('files_directory'));
				$em->remove($media_old_thumb);
				$em->flush();
			}
			if ($media_old_video != null) {
				$media_old_video->delete($this->container->getParameter('files_directory'));
				$em->remove($media_old_video);
				$em->flush();
			}
			$this->addFlash('success', 'Operation has been done successfully');
			return $this->redirect($this->generateUrl('app_status_index'));
		}
		return $this->render('AppBundle:Status:delete.html.twig', array("form" => $form->createView()));
	}





	public function indexAction(Request $request) {

		$em = $this->getDoctrine()->getManager();
		$q = "  ";
		if ($request->query->has("q") and $request->query->get("q") != "") {
			$q .= " AND  w.title like '%" . $request->query->get("q") . "%'";
		}

		$dql = "SELECT i FROM AppBundle:Status i  WHERE i.review = false " . $q . " ORDER BY i.created desc ";
		$query = $em->createQuery($dql);
		$paginator = $this->get('knp_paginator');
		$status_list = $paginator->paginate(
			$query,
			$request->query->getInt('page', 1),
			12
		);
		$status_count = $em->getRepository('AppBundle:Status')->countAll();
		return $this->render('AppBundle:Status:index.html.twig', array("status_list" => $status_list, "status_count" => $status_count));
	}

	public function reviewAction(Request $request, $id) {
		$em = $this->getDoctrine()->getManager();
		$status = $em->getRepository("AppBundle:Status")->findOneBy(array("id" => $id, "review" => true));
		if ($status == null) {
			throw new NotFoundHttpException("Page not found");
		}
		$form = $this->createForm(new StatusReviewType(), $status);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$status->setReview(false);
			$status->setEnabled(true);
			$status->setCreated(new \DateTime());
			$em->persist($status);
			$em->flush();
			$this->addFlash('success', 'Operation has been done successfully');


            $type =  $status->getType();
            if ($type=="fullscreen") {
               $type = "video";
            }
            $transaction = new Transaction();
            $setting = $em->getRepository("AppBundle:Settings")->findOneBy(array());
            $transaction->setPoints($setting->getPoints("add".$type));
            $transaction->setStatus($status);
            $transaction->setUser($status->getUser());
            $transaction->setType("add_".$type);
            $em->persist($transaction);
            $em->flush();

			return $this->redirect($this->generateUrl('app_home_notif_user_status', array("status_id" => $status->getId())));
		}
		return $this->render("AppBundle:Status:review.html.twig", array("form" => $form->createView()));
	}
    public function reviewQuoteAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $status = $em->getRepository("AppBundle:Status")->findOneBy(array("id" => $id, "review" => true));
        if ($status == null) {
            throw new NotFoundHttpException("Page not found");
        }
        $form = $this->createForm(new QuoteReviewType(),$status);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $status->setReview(false);
            $status->setEnabled(true);
            $status->setCreated(new \DateTime());
            $em->persist($status);
            $em->flush();
            $type =  $status->getType();
            if ($type=="fullscreen") {
               $type = "video";
            }
            $transaction = new Transaction();
            $setting = $em->getRepository("AppBundle:Settings")->findOneBy(array());
            $transaction->setPoints($setting->getPoints("add".$type));
            $transaction->setStatus($status);
            $transaction->setUser($status->getUser());
            $transaction->setType("add_".$type);
            $em->persist($transaction);
            $em->flush();

            $this->addFlash('success', 'Operation has been done successfully');
            return $this->redirect($this->generateUrl('app_home_notif_user_status', array("status_id" => $status->getId())));
        }
        return $this->render("AppBundle:Status:quote_review.html.twig", array("status"=>$status,"form" => $form->createView()));
    }
	public function reviewsAction(Request $request) {

		$em = $this->getDoctrine()->getManager();

		$dql = "SELECT w FROM AppBundle:Status w  WHERE w.review = true ORDER BY w.created desc ";
		$query = $em->createQuery($dql);
		$paginator = $this->get('knp_paginator');
		$videos = $paginator->paginate(
			$query,
			$request->query->getInt('page', 1),
			12
		);
		$video_list = $em->getRepository('AppBundle:Status')->findBy(array("review" => true));
		$videos_count = sizeof($video_list);
		return $this->render('AppBundle:Status:reviews.html.twig', array("videos" => $videos, "videos_count" => $videos_count));
	}

	public function viewAction(Request $request, $id) {
		$em = $this->getDoctrine()->getManager();
		$status = $em->getRepository("AppBundle:Status")->find($id);
		if ($status == null) {
			throw new NotFoundHttpException("Page not found");
		}
		return $this->render("AppBundle:Status:view.html.twig", array("status" => $status));
	}
}
?>