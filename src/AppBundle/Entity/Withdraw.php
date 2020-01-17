<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use MediaBundle\Entity\Media;
use UserBundle\Entity\User;

/**
 * Withdraw
 *
 * @ORM\Table(name="withdraws_table")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\WithdrawRepository")
 */
class Withdraw
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
     * @ORM\Column(name="methode", type="string", length=255))
     */
    private $methode;

    /**
     * @var string
     * @ORM\Column(name="account", type="string", length=255))
     */
    private $account;

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
     * @var int
     *
     * @ORM\Column(name="amount", type="string", length=255))
     */
    private $amount;

     /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;



    public function __construct()
    {
                $this->created= new \DateTime();
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
        return $this;
    }
    /**
    * Get amount
    * @return  
    */
    public function getAmount()
    {
        return $this->amount;
    }
    
    /**
    * Set amount
    * @return $this
    */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }
    /**
    * Get methode
    * @return  
    */
    public function getMethode()
    {
        return $this->methode;
    }
    
    /**
    * Set methode
    * @return $this
    */
    public function setMethode($methode)
    {
        $this->methode = $methode;
        return $this;
    }
    /**
    * Get account
    * @return  
    */
    public function getAccount()
    {
        return $this->account;
    }
    
    /**
    * Set account
    * @return $this
    */
    public function setAccount($account)
    {
        $this->account = $account;
        return $this;
    }
}
