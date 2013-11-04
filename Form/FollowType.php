<?php

namespace Bangpound\Bundle\TwitterStreamingBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FollowType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('userId')
            ->add('isActive')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Bangpound\Bundle\TwitterStreamingBundle\Entity\Follow'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bangpound_twitter_streamingbundle_follow';
    }
}
