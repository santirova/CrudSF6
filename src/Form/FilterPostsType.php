<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterPostsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Start Date',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'End Date',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('type', ChoiceType::class, [
                'choices' => array_merge(["Todos"=> ''], Post::TYPES),
                'required' => false,
                'placeholder' => 'All Types',
                'attr' => ['class' => 'form-control'],
            ]);
        
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
