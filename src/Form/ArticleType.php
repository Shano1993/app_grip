<?php

namespace App\Form;

use App\Entity\Article;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => "Entrez le titre",
                'required' => true,
                'empty_data' => "Pas de titre",
                'attr' => [
                    'maxlength' => 100,
                    'minlength' => 10,
                ]
            ])

            ->add('content', CKEditorType::class, [
                'label' => "Entrez le contenu",
                'constraints' => [
                    new Length([
                        'min' => 40,
                        'max' => 2500,
                        'minMessage' => "Votre article est trop court !",
                        'maxMessage' => "Votre article est trop long !",
                    ]),
                ],
            ])

            ->add('dateAdd', DateType::class, [
                'label' => "Entrez la date d'ajout",
                'widget' => 'choice',
                'format' => 'dd-MM-yyy',
                'placeholder' => [
                    'day' => "Jour",
                    'month' => "Mois",
                    'year' => "Année",
                ]
            ])

            ->add('fileName', FileType::class, [
                'label' => "Image de l'article",
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => "8M",
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ]
                    ])
                ]
            ])

            ->add('submit', SubmitType::class, [
                'label' => "Ajouter",
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
