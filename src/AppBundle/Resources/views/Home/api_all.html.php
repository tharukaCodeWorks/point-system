<?php 
$obj ;
$obj["categories"] = $popular_categories;




$list_slides = array();
foreach ($slides as $key => $slide) {
	$s = null;
	$s["id"] = $slide->getId();
	$s["title"] = $slide->getTitle();
	$s["type"] = $slide->getType();
	$s["image"] = $this['imagine']->filter($view['assets']->getUrl($slide->getMedia()->getLink()), 'slide_thumb');
	if ($slide->getType() == 3 && $slide->getStatus() != null) {
        $status_slide = $slide->getStatus();
        $a_slide = array();

        $a_slide["id"]=$status_slide->getId();
        $a_slide["kind"]=$status_slide->getType();
        $a_slide["title"]=$status_slide->getTitle();
        $a_slide["description"]=$status_slide->getDescription();
        $a_slide["review"]=$status_slide->getReview();
        $a_slide["comment"]=$status_slide->getComment();
        $a_slide["comments"]=sizeof($status_slide->getComments());
        $a_slide["downloads"]=$status_slide->getDownloads();
        $a_slide["views"]=$status_slide->getViews();
        $a_slide["font"]=$status_slide->getFont();
        $a_slide["user"]=$status_slide->getUser()->getName();
        $a_slide["userid"]=$status_slide->getUser()->getId();
        $a_slide["userimage"]=$status_slide->getUser()->getImage();
        if ($status_slide->getType()!="quote") {
            if ($status_slide->getVideo()) {
                $a_slide["type"]=$status_slide->getVideo()->getType();
                $a_slide["extension"]=$status_slide->getVideo()->getExtension();
            }else{
                $a_slide["type"]=$status_slide->getMedia()->getType();
                $a_slide["extension"]=$status_slide->getMedia()->getExtension();
            }
            $a_slide["thumbnail"]= $this['imagine']->filter($view['assets']->getUrl($status_slide->getMedia()->getLink()), 'status_thumb_api');
            if ($status_slide->getVideo()) {
                if ($status_slide->getVideo()->getEnabled()) {
                    $a_slide["original"] = $app->getRequest()->getSchemeAndHttpHost() . "/" .$status_slide->getVideo()->getLink();
                }else{
                    $a_slide["original"] = $status_slide->getVideo()->getLink();
                }   
            }else{
                $a_slide["original"]=$app->getRequest()->getSchemeAndHttpHost() . "/" . $status_slide->getMedia()->getLink();
            }
        }else{
            $a_slide["color"]=$status_slide->getColor();
        }
        $a_slide["created"]=$view['time']->diff($status_slide->getCreated());
        $a_slide["tags"]=$status_slide->getTags();
        $a_slide["like"]=$status_slide->getLike();
        $a_slide["love"]=$status_slide->getLove();
        $a_slide["woow"]=$status_slide->getWoow();
        $a_slide["angry"]=$status_slide->getAngry();
        $a_slide["sad"]=$status_slide->getSad();
        $a_slide["haha"]=$status_slide->getHaha();
		$s["status"] = $a_slide;
	} elseif ($slide->getType() == 1 && $slide->getCategory() != null) {
		$c["id"] = $slide->getCategory()->getId();
		$c["title"] = $slide->getCategory()->getTitle();
		$c["image"] = $this['imagine']->filter($view['assets']->getUrl($slide->getCategory()->getMedia()->getLink()), 'category_thumb_api');
		$s["category"] = $c;
	} elseif ($slide->getType() == 2 && $slide->getUrl() != null) {
		$s["url"] = $slide->getUrl();
	}
	$list_slides[] = $s;
}
$obj["slides"] = $list_slides;

$list_fullscreen=array();
foreach ($full_screen_videos as $key => $status_full_screen) {
	$a_fullscreen = array();

	$a_fullscreen["id"]=$status_full_screen->getId();
	$a_fullscreen["kind"]=$status_full_screen->getType();
	$a_fullscreen["title"]=$status_full_screen->getTitle();
	$a_fullscreen["description"]=$status_full_screen->getDescription();
	$a_fullscreen["review"]=$status_full_screen->getReview();
	$a_fullscreen["comment"]=$status_full_screen->getComment();
	$a_fullscreen["comments"]=sizeof($status_full_screen->getComments());
	$a_fullscreen["downloads"]=$status_full_screen->getDownloads();
	$a_fullscreen["font"]=$status_full_screen->getFont();
	$a_fullscreen["views"]=$status_full_screen->getViews();
	$a_fullscreen["user"]=$status_full_screen->getUser()->getName();
	$a_fullscreen["userid"]=$status_full_screen->getUser()->getId();
	$a_fullscreen["userimage"]=$status_full_screen->getUser()->getImage();
	if ($status_full_screen->getType()!="quote") {
		if ($status_full_screen->getVideo()) {
			$a_fullscreen["type"]=$status_full_screen->getVideo()->getType();
			$a_fullscreen["extension"]=$status_full_screen->getVideo()->getExtension();
		}else{
			$a_fullscreen["type"]=$status_full_screen->getMedia()->getType();
			$a_fullscreen["extension"]=$status_full_screen->getMedia()->getExtension();
		}
		$a_fullscreen["thumbnail"]= $this['imagine']->filter($view['assets']->getUrl($status_full_screen->getMedia()->getLink()), 'status_thumb_api');
		if ($status_full_screen->getVideo()) {
			if ($status_full_screen->getVideo()->getEnabled()) {
				$a_fullscreen["original"] = $app->getRequest()->getSchemeAndHttpHost() . "/" .$status_full_screen->getVideo()->getLink();
			}else{
				$a_fullscreen["original"] = $status_full_screen->getVideo()->getLink();
			}	
		}else{
			$a_fullscreen["original"]=$app->getRequest()->getSchemeAndHttpHost() . "/" . $status_full_screen->getMedia()->getLink();
		}
	}else{
		$a_fullscreen["color"]=$status_full_screen->getColor();
	}
	$a_fullscreen["created"]=$view['time']->diff($status_full_screen->getCreated());
	$a_fullscreen["tags"]=$status_full_screen->getTags();
	$a_fullscreen["like"]=$status_full_screen->getLike();
	$a_fullscreen["love"]=$status_full_screen->getLove();
	$a_fullscreen["woow"]=$status_full_screen->getWoow();
	$a_fullscreen["angry"]=$status_full_screen->getAngry();
	$a_fullscreen["sad"]=$status_full_screen->getSad();
	$a_fullscreen["haha"]=$status_full_screen->getHaha();

	$list_fullscreen[]=$a_fullscreen;
}
$obj["fullscreen"] = $list_fullscreen;

$list=array();
foreach ($videos as $key => $status) {
	$a = array();

	$a["id"]=$status->getId();
	$a["kind"]=$status->getType();
	$a["title"]=$status->getTitle();
	$a["description"]=$status->getDescription();
	$a["review"]=$status->getReview();
	$a["comment"]=$status->getComment();
	$a["comments"]=sizeof($status->getComments());
	$a["downloads"]=$status->getDownloads();
	$a["font"]=$status->getFont();
	$a["views"]=$status->getViews();
	$a["user"]=$status->getUser()->getName();
	$a["userid"]=$status->getUser()->getId();
	$a["userimage"]=$status->getUser()->getImage();
	if ($status->getType()!="quote") {
		if ($status->getVideo()) {
			$a["type"]=$status->getVideo()->getType();
			$a["extension"]=$status->getVideo()->getExtension();
		}else{
			$a["type"]=$status->getMedia()->getType();
			$a["extension"]=$status->getMedia()->getExtension();
		}
		$a["thumbnail"]= $this['imagine']->filter($view['assets']->getUrl($status->getMedia()->getLink()), 'status_thumb_api');
		if ($status->getVideo()) {
			if ($status->getVideo()->getEnabled()) {
				$a["original"] = $app->getRequest()->getSchemeAndHttpHost() . "/" .$status->getVideo()->getLink();
			}else{
				$a["original"] = $status->getVideo()->getLink();
			}	
		}else{
			$a["original"]=$app->getRequest()->getSchemeAndHttpHost() . "/" . $status->getMedia()->getLink();
		}
	}else{
		$a["color"]=$status->getColor();
	}
	$a["created"]=$view['time']->diff($status->getCreated());
	$a["tags"]=$status->getTags();
	$a["like"]=$status->getLike();
	$a["love"]=$status->getLove();
	$a["woow"]=$status->getWoow();
	$a["angry"]=$status->getAngry();
	$a["sad"]=$status->getSad();
	$a["haha"]=$status->getHaha();

	$list[]=$a;
}
$obj["status"] = $list;



echo json_encode($obj, JSON_UNESCAPED_UNICODE);?>
 ?>