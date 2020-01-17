<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class StatusReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       $builder->add('title',"text",array());
       $builder->add('description',null,array("label"=>"description"));

       $builder->add('comment');
       $builder->add('tags');
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
       $builder->add('save', 'submit',array("label"=>"REVIEW"));
    }
    public function getName()
    {
        return 'Status';
    }
}
?>