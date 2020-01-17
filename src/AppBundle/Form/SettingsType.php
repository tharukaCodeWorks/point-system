<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
class SettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('firebasekey',"text",array());
        $builder->add('currency',"text",array());
        $builder->add('adduser');
        $builder->add('sharevideo');
        $builder->add('viewvideo');
        $builder->add('addvideo');
        $builder->add('shareimage');
        $builder->add('viewimage');
        $builder->add('addimage');
        $builder->add('sharegif');
        $builder->add('viewgif');
        $builder->add('addgif');
        $builder->add('sharequote');
        $builder->add('viewquote');
        $builder->add('addquote');
        $builder->add('minpoints');
        $builder->add('oneusdtopoints');
        $builder->add('save', 'submit',array("label"=>"SAVE"));

    }
    public function getName()
    {
        return 'Settings';
    }
}
?>