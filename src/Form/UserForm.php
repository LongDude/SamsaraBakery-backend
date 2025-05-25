<?php

namespace App\Form;

use App\Entity\Affiliates;
use App\Entity\Partners;
use App\Entity\Suppliers;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('roles')
            ->add('password')
            ->add('phone')
            ->add('Username')
            ->add('suppliers_represent', EntityType::class, [
                'class' => Suppliers::class,
                'choice_label' => 'username',
                'multiple' => true,
            ])
            ->add('partners_represent', EntityType::class, [
                'class' => Partners::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
            ->add('affiliate', EntityType::class, [
                'class' => Affiliates::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
