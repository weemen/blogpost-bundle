<?php

namespace Weemen\BlogPostBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\IsFalse;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class BlogPost extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('min' => 1)),
                ),
            ))
            ->add('content', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('min' => 1)),
                ),
            ))
            ->add('author', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('min' => 1)),
                ),
            ))
            ->add('published', 'checkbox', array(
                'constraints' => array(
                ),
            ))
            ->add('source', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('min' => 1)),
                ),
            ))
            ->add('publishDate', 'text', array(
                'constraints' => array(
                ),
            ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false
        ));
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return 'blog_post';
    }


}