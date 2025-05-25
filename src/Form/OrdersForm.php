<?php

namespace App\Form;

use App\Entity\Orders;
use App\Entity\Partners;
use App\Entity\Products;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrdersForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('price')
            ->add('quantity')
            ->add('status')
            ->add('reciever_partner', EntityType::class, [
                'class' => Partners::class,
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
            'data_class' => Orders::class,
        ]);
    }
}
