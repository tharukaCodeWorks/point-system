<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Settings
 *
 * @ORM\Table(name="settings_table")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SettingsRepository")
 */
class Settings
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="firebasekey", type="text")
     */
    private $firebasekey;

    /**
     * @var string
     *
     * @ORM\Column(name="sharevideo", type="integer")
     */
    private $sharevideo;

    /**
     * @var string
     *
     * @ORM\Column(name="viewvideo", type="integer")
     */
    private $viewvideo;
    /**
     * @var string
     *
     * @ORM\Column(name="addvideo", type="integer")
     */
    private $addvideo;
    /**
     * @var string
     *
     * @ORM\Column(name="shareimage", type="integer")
     */
    private $shareimage;

    /**
     * @var string
     *
     * @ORM\Column(name="viewimage", type="integer")
     */
    private $viewimage;
    /**
     * @var string
     *
     * @ORM\Column(name="addimage", type="integer")
     */
    private $addimage;
    /**
     * @var string
     *
     * @ORM\Column(name="sharegif", type="integer")
     */
    private $sharegif;

     /**
     * @var string
     *
     * @ORM\Column(name="viewgif", type="integer")
     */
    private $viewgif;
     /**
     * @var string
     *
     * @ORM\Column(name="addgif", type="integer")
     */
    private $addgif;
    /**
     * @var string
     *
     * @ORM\Column(name="sharequote", type="integer")
     */
    private $sharequote;

    /**
     * @var string
     *
     * @ORM\Column(name="viewquote", type="integer")
     */
    private $viewquote;


    /**
     * @var string
     *
     * @ORM\Column(name="addquote", type="integer")
     */
    private $addquote;


    /**
     * @var string
     *
     * @ORM\Column(name="adduser", type="integer")
     */
    private $adduser;

    /**
     * @var string
     *
     * @ORM\Column(name="minpoints", type="integer")
     */
    private $minpoints;

    /**
     * @var string
     *
     * @ORM\Column(name="currency",type="string" , length=255)
     */
    private $currency;

    /**
     * @var string
     *
     * @ORM\Column(name="oneusdtopoints", type="integer")
     */
    private $oneusdtopoints;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
    * Get firebasekey
    * @return  
    */
    public function getFirebasekey()
    {
        return $this->firebasekey;
    }
    
    /**
    * Set firebasekey
    * @return $this
    */
    public function setFirebasekey($firebasekey)
    {
        $this->firebasekey = $firebasekey;
        return $this;
    }
    /**
    * Get sharevideo
    * @return  
    */
    public function getSharevideo()
    {
        return $this->sharevideo;
    }
    
    /**
    * Set sharevideo
    * @return $this
    */
    public function setSharevideo($sharevideo)
    {
        $this->sharevideo = $sharevideo;
        return $this;
    }
    /**
    * Get viewvideo
    * @return  
    */
    public function getViewvideo()
    {
        return $this->viewvideo;
    }
    
    /**
    * Set viewvideo
    * @return $this
    */
    public function setViewvideo($viewvideo)
    {
        $this->viewvideo = $viewvideo;
        return $this;
    }
    /**
    * Get addvideo
    * @return  
    */
    public function getAddvideo()
    {
        return $this->addvideo;
    }
    
    /**
    * Set addvideo
    * @return $this
    */
    public function setAddvideo($addvideo)
    {
        $this->addvideo = $addvideo;
        return $this;
    }
    /**
    * Get shareimage
    * @return  
    */
    public function getShareimage()
    {
        return $this->shareimage;
    }
    
    /**
    * Set shareimage
    * @return $this
    */
    public function setShareimage($shareimage)
    {
        $this->shareimage = $shareimage;
        return $this;
    }
    /**
    * Get viewimage
    * @return  
    */
    public function getViewimage()
    {
        return $this->viewimage;
    }
    
    /**
    * Set viewimage
    * @return $this
    */
    public function setViewimage($viewimage)
    {
        $this->viewimage = $viewimage;
        return $this;
    }
    /**
    * Get addimage
    * @return  
    */
    public function getAddimage()
    {
        return $this->addimage;
    }
    
    /**
    * Set addimage
    * @return $this
    */
    public function setAddimage($addimage)
    {
        $this->addimage = $addimage;
        return $this;
    }
    /**
    * Get sharegif
    * @return  
    */
    public function getSharegif()
    {
        return $this->sharegif;
    }
    
    /**
    * Set sharegif
    * @return $this
    */
    public function setSharegif($sharegif)
    {
        $this->sharegif = $sharegif;
        return $this;
    }
    /**
    * Get addgif
    * @return  
    */
    public function getAddgif()
    {
        return $this->addgif;
    }
    
    /**
    * Set addgif
    * @return $this
    */
    public function setAddgif($addgif)
    {
        $this->addgif = $addgif;
        return $this;
    }
    /**
    * Get sharequote
    * @return  
    */
    public function getSharequote()
    {
        return $this->sharequote;
    }
    
    /**
    * Set sharequote
    * @return $this
    */
    public function setSharequote($sharequote)
    {
        $this->sharequote = $sharequote;
        return $this;
    }
    /**
    * Get viewshare
    * @return  
    */
    public function getViewshare()
    {
        return $this->viewshare;
    }
    
    /**
    * Set viewshare
    * @return $this
    */
    public function setViewshare($viewshare)
    {
        $this->viewshare = $viewshare;
        return $this;
    }
    /**
    * Get addquote
    * @return  
    */
    public function getAddquote()
    {
        return $this->addquote;
    }
    
    /**
    * Set addquote
    * @return $this
    */
    public function setAddquote($addquote)
    {
        $this->addquote = $addquote;
        return $this;
    }
    /**
    * Get oneusdtopoints
    * @return  
    */
    public function getOneusdtopoints()
    {
        return $this->oneusdtopoints;
    }
    
    /**
    * Set oneusdtopoints
    * @return $this
    */
    public function setOneusdtopoints($oneusdtopoints)
    {
        $this->oneusdtopoints = $oneusdtopoints;
        return $this;
    }
    /**
    * Get minpoints
    * @return  
    */
    public function getMinpoints()
    {
        return $this->minpoints;
    }
    
    /**
    * Set minpoints
    * @return $this
    */
    public function setMinpoints($minpoints)
    {
        $this->minpoints = $minpoints;
        return $this;
    }
    /**
    * Get viewgif
    * @return  
    */
    public function getViewgif()
    {
        return $this->viewgif;
    }
    
    /**
    * Set viewgif
    * @return $this
    */
    public function setViewgif($viewgif)
    {
        $this->viewgif = $viewgif;
        return $this;
    }
    /**
    * Get viewquote
    * @return  
    */
    public function getViewquote()
    {
        return $this->viewquote;
    }
    
    /**
    * Set viewquote
    * @return $this
    */
    public function setViewquote($viewquote)
    {
        $this->viewquote = $viewquote;
        return $this;
    }
    /**
    * Get adduser
    * @return  
    */
    public function getAdduser()
    {
        return $this->adduser;
    }
    
    /**
    * Set adduser
    * @return $this
    */
    public function setAdduser($adduser)
    {
        $this->adduser = $adduser;
        return $this;
    }
    public function getPoints($name)
    {
        return $this->$name;
    }
    /**
    * Get currency
    * @return  
    */
    public function getCurrency()
    {
        return $this->currency;
    }
    
    /**
    * Set currency
    * @return $this
    */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }
}
