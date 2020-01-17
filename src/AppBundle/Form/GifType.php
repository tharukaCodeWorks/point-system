<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\Ed;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
class GifType extends AbstractType
{
   public function buildForm(FormBuilderInterface $builder, array $options)
    {
         $builder->add('title',"text",array("label"=>"Title"));
         $builder->add('description',null,array("label"=>"description"));
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
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $article = $event->getData();
            $form = $event->getForm();
            if ($article and null !== $article->getId()) {
                 $form->add("filegif",null,array("label"=>"","required"=>false));
            }else{
                 $form->add("filegif",null,array("label"=>"","required"=>true));
            }
        });
        $builder->add('save', 'submit',array("label"=>"save"));
      }
      public function getName()
      {
          return 'Video';
      }
}
?>