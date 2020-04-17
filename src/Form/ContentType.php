<?php

namespace App\Form;

use App\Entity\Content;
use App\Entity\Univers;
use App\Entity\User;
use App\Entity\ContentType as EntityContentType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ContentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class,[
                'attr' => [
                    'class' => 'form-control',
                    'label' => 'Nom',
                    'required' => true,
                    "placeholder" => "Titre"
                ]
            ])
            ->add('image', FileType::class, [
                "attr" => [
                    "id" => "upload-file",
                    "required" => false,
                    'onchange' => "openFile(event)"
                ],
                "required" => false
            ])
            ->add("description", TextType::class,[
                "attr" => [
                    "class" => "form-control",
                    "required" => "false",
                    "placeholder" => "Description"
                ]
            ])
            ->add('submit', SubmitType::class,[
                "attr" => [
                    "class" => "btn btn-success",
                ],
            ])
            ->add('isPrivate', CheckboxType::class,[
                'label' => "Rendre le contenu privé ?"
            ])
            ->add("contentType", EntityType::class, [
                'class' => EntityContentType::class,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    return $er->createQueryBuilder('ct')
                        ->where('ct.univers = :id')
                        ->setParameter("id",$options['idUnivers']);
                }
            ])
            ->add("content", TextareaType::class, [
                'attr' => [
                    "required" => false,
                    "id" => "summernote"
                ]
            ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Content::class,
            'update' => false,
            'idUnivers' => null
        ));
    }
}