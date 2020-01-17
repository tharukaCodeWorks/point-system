<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use MediaBundle\Entity\Media;
use UserBundle\Entity\User;

/**
 * Transaction
 *
 * @ORM\Table(name="transaction_table")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TransactionRepository")
 */
class Transaction
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
     * @ORM\Column(name="label", type="string", length=255))
     */
    private $label;

    /**
     * @var string
     * @ORM\Column(name="type", type="string", length=255))
     */
    private $type;
    /**
     * @var int
     *
     * @ORM\Column(name="points", type="integer")
     */
    private $points;

     /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

         /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User")
     * @ORM\JoinColumn(name="invited_id", referencedColumnName="id")
     * @ORM\JoinColumn(nullable=false)
     */
    private $invited;


     /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Status" , inversedBy="transaction")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     * @ORM\JoinColumn(nullable=true)
     */
    private $status;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled;


    public function __construct()
    {
                $this->created= new \DateTime();
                $this->enabled= true;
    }
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
    * Get label
    * @return  
    */
    public function getLabel()
    {
        return $this->label;
    }
    
    /**
    * Set label
    * @return $this
    */
    public function setLabel($label)
    {

        $this->label = $label;
        return $this;
    }
    /**
    * Get points
    * @return  
    */
    public function getPoints()
    {
        return $this->points;
    }
    
    /**
    * Set points
    * @return $this
    */
    public function setPoints($points)
    {
        $this->points = $points;
        return $this;
    }
   
    /**
     * Set user
     *
     * @param string $user
     * @return Wallpaper
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return string 
     */
    public function getUser()
    {
        return $this->user;
    }
    /**
    * Get created
    * @return  
    */
    public function getCreated()
    {
        return $this->created;
    }
    
    /**
    * Set created
    * @return $this
    */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }
    /**
    * Get status
    * @return  
    */
    public function getStatus()
    {
        return $this->status;
    }
    
    /**
    * Set status
    * @return $this
    */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }
    /**
    * Get type
    * @return  
    */
    public function getType()
    {
        return $this->type;
    }
    
    /**
    * Set type
    * @return $this
    */
    public function setType($type)
    {
        $this->type = $type;
        if ($this->getType()=="view_quote") {
            $this->label = "You see the status : ".$this->getStatus()->getDescription();
        }else if ($this->getType()=="share_quote"){
           $this->label = "You share the status : ".$this->getStatus()->getDescription();
        }else if ($this->getType()=="add_quote"){
           $this->label = "You add new status : ".$this->getStatus()->getDescription();
        }else if ($this->getType()=="view_video"){
           $this->label = "You watch the video : ".$this->getStatus()->getTitle();
        }else if ($this->getType()=="share_video"){
           $this->label = "You share the video : ".$this->getStatus()->getTitle();
        }else if ($this->getType()=="add_video"){
           $this->label = "You upload new video : ".$this->getStatus()->getTitle();
        }else if ($this->getType()=="view_image"){
           $this->label = "You see the image : ".$this->getStatus()->getTitle();
        }else if ($this->getType()=="share_image"){
           $this->label = "You share the image : ".$this->getStatus()->getTitle();
        }else if ($this->getType()=="add_image"){
           $this->label = "You upload new image : ".$this->getStatus()->getTitle();
        }else if ($this->getType()=="view_gif"){
           $this->label = "You see the Gif : ".$this->getStatus()->getTitle();
        }else if ($this->getType()=="share_gif"){
           $this->label = "You share the Gif : ".$this->getStatus()->getTitle();
        }else if ($this->getType()=="add_gif"){
           $this->label = "You upload new Gif : ".$this->getStatus()->getTitle();
        }else if ($this->getType()=="invited_user"){
           $this->label = "You Invite the User : ".$this->getInvited()->getName();
        }
        return $this;
    }
    /**
    * Get invited
    * @return  
    */
    public function getInvited()
    {
        return $this->invited;
    }
    
    /**
    * Set invited
    * @return $this
    */
    public function setInvited($invited)
    {
        $this->invited = $invited;
        return $this;
    }
    /**
    * Get enabled
    * @return  
    */
    public function getEnabled()
    {
        return $this->enabled;
    }
    
    /**
    * Set enabled
    * @return $this
    */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }
}
