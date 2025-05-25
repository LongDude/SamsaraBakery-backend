<?php

namespace App\Form;

use App\Entity\Affiliates;
use App\Entity\Partners;
use App\Entity\Suppliers;
use App\Entity\User;
use App\Enum\UserRoles;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('roles', ChoiceType::class, [
                'choices' => UserRoles::cases(),
                'choice_label' => function (UserRoles $role) {
                    return $role->value;
                },
                'multiple' => true,
                'data' => array_map(fn(string $role) => UserRoles::from($role), $options['data']->getRoles()),
                'expanded' => true, // or false for select dropdown
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
                'required' => false,
            ])
            ->add('phone')
            ->add('Username')
            ->add('suppliers_represent', EntityType::class, [
                'class' => Suppliers::class,
                'choice_label' => 'firmname',
                'multiple' => true,
                'required' => false, 
                'empty_data' => [],  
            ])
            ->add('partners_represent', EntityType::class, [
                'class' => Partners::class,
                'choice_label' => 'firmname',
                'multiple' => true,
                'required' => false, 
                'empty_data' => [],  
            ])
            ->add('affiliate', EntityType::class, [
                'class' => Affiliates::class,
                'choice_label' => 'id',
                'required' => false, 
                'empty_data' => [],  
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
