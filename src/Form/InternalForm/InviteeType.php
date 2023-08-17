<?php

namespace App\Form\InternalForm;

use App\Entity\Invitee;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InviteeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'phoneNumber',
                TextType::class,
                [
                    'attr' => [
                        'label' => false,
                    ]
                ]
            )
            ->add(
                'title',
                TextType::class,
                [
                    'attr' => [
                        'label' => false,
                    ]
                ]
            )
            ->add(
                'firstname',
                TextType::class,
                [
                    'attr' => [
                        'label' => false,
                    ]
                ]
            )
            ->add(
                'lastname',
                TextType::class,
                [
                    'attr' => [
                        'label' => false,
                    ]
                ]
            )
            ->add(
                'username',
                TextType::class,
                [
                    'attr' => [
                        'label' => false,
                    ]
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Invitee::class,
        ]);
    }
}
