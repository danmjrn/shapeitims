<?php

namespace App\Form\InternalForm;

use App\Entity\Invitee;
use Ramsey\Collection\Collection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InviteesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'invitee',
                CollectionType::class,
                [
                    'entry_type' => InviteeType::class,
                    'entry_options' => [
                        'label' => false,
                    ],
                    'by_reference' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InviteesType::class,
        ]);
    }
}
