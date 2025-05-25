<?php

namespace App\Form;

use App\Entity\Affiliates;
use App\Entity\Products;
use App\Entity\ProductsMovement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductsMovementForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('realised_price')
            ->add('realised_count')
            ->add('recieved_cost')
            ->add('recieved_count')
            ->add('date')
            ->add('affiliate', EntityType::class, [
                'class' => Affiliates::class,
                'choice_label' => 'id',
            ])
            ->add('product', EntityType::class, [
                'class' => Products::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductsMovement::class,
        ]);
    }
}
