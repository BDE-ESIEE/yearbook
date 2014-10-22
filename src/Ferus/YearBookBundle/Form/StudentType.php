<?php

namespace Ferus\YearBookBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class StudentType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', null, array(
                'mapped' => false,
            ))
            ->add('x', null, array(
                'mapped' => false,
            ))
            ->add('y', null, array(
                'mapped' => false,
            ))
            ->add('w', null, array(
                'mapped' => false,
            ))
            ->add('h', null, array(
                'mapped' => false,
            ))
            ->add('password', null, array(
                'mapped' => false,
            ))
            ->add('quote')
            ->add('file', 'file')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ferus\YearBookBundle\Entity\Student',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return '';
    }
}
