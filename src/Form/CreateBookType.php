<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateBookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('author', ChoiceType::class, [
                'choices' => $options['data']['authors'],
                'row_attr' => [
                    'class' => 'row mb-3'
                ],
                'label_attr' => [
                    'class' => 'col-3'
                ],
                'attr' => [
                    'class' => 'col-auto'
                ]
            ])
            ->add('title', TextType::class, [
                'row_attr' => [
                    'class' => 'row mb-3'
                ],
                'label_attr' => [
                    'class' => 'col-3'
                ],
                'attr' => [
                    'class' => 'col-auto'
                ]
            ])
            ->add('release_date', DateType::class, [
                'row_attr' => [
                    'class' => 'row mb-3'
                ],
                'label_attr' => [
                    'class' => 'col-3'
                ],
                'attr' => [
                    'class' => 'col-auto'
                ],
                'input' => 'string'
            ])
            ->add('description', TextareaType::class, [
                'row_attr' => [
                    'class' => 'row mb-3'
                ],
                'label_attr' => [
                    'class' => 'col-3'
                ],
                'attr' => [
                    'class' => 'col-auto'
                ]
            ])
            ->add('isbn', TextType::class, [
                'row_attr' => [
                    'class' => 'row mb-3'
                ],
                'label_attr' => [
                    'class' => 'col-3'
                ],
                'attr' => [
                    'class' => 'col-auto'
                ]
            ])
            ->add('format', TextType::class, [
                'row_attr' => [
                    'class' => 'row mb-3'
                ],
                'label_attr' => [
                    'class' => 'col-3'
                ],
                'attr' => [
                    'class' => 'col-auto'
                ]
            ])
            ->add('number_of_pages', IntegerType::class, [
                'row_attr' => [
                    'class' => 'row'
                ],
                'label_attr' => [
                    'class' => 'col-3'
                ],
                'attr' => [
                    'class' => 'col-auto'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
