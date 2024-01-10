<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Conversation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConversationType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
            ->add('name')
            ->add('type')
            ->add('users', EntityType::class, [
            'class' => User::class,
            'choices' => $options['friends'],
            'choice_label' => function (User $user) {
                return $user->getName(); 
            },
            'multiple' => true, 
            'expanded' => false, 
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults([
            'data_class' => Conversation::class,
            'friends' => [],
        ]);
    }
}

