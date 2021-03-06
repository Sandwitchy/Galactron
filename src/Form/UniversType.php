<?php

namespace App\Form;

use App\Entity\Univers;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UniversType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class,[
                'attr' => [
                    'class' => 'form-control',
                    'label' => 'Nom',
                    'required' => true
                ]
            ])
            ->add('image', FileType::class, [
                "attr" => [
                    "id" => "upload-file",
                    'onchange' => "openFile(event)"
                ],
                "required" => false
            ])
            ->add('submit', SubmitType::class,[
                "attr" => [
                    "class" => "btn btn-success",
                ],
            ])
        ;
        if($options['update']){
            $builder
                ->add('isPrivate', CheckboxType::class,[
                    'label' => "Rendre l'univers invisible ?"
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Univers::class,
            'update' => false
        ));
    }
}