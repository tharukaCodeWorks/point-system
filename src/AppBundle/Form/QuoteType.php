<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
class QuoteType extends AbstractType
{
   public function buildForm(FormBuilderInterface $builder, array $options)
    {
         $builder->add('title',null,array("label"=>"Title","attr"=>array("rows"=>10)));
         $builder->add('color',null,array("label"=>"Enabled"));
         $builder->add('enabled',null,array("label"=>"Enabled"));
         $builder->add('comment',null,array("label"=>"Enabled comments"));
         $builder->add('tags',null,array("label"=>"Tags (Keywords)"));

         $builder->add("categories",'entity',
                      array(
                            'class' => 'AppBundle:Category',
                            'expanded' => true,
                            "multiple" => "true",
                            'by_reference' => false
                          )
                      );
         $builder->add("languages",'entity',
                      array(
                            'class' => 'AppBundle:Language',
                            'expanded' => true,
                            "multiple" => "true",
                            'by_reference' => false
                          )
                      );
        $builder->add('save', 'submit',array("label"=>"save"));
      }
      public function getName()
      {
          return 'Video';
      }
}
?>