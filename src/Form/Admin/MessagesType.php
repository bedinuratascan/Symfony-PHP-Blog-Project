<?php

namespace App\Form\Admin;

use App\Entity\Admin\Messages;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessagesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('email',EmailType::class)
            ->add('message',TextareaType::class)
            ->add('status', ChoiceType::class,[
                'choices' => [
                    'Read' => 'Read',
                    'New' => 'New',
                    'Answered' => 'Answered']
            ])
            ->add('ip')
            ->add('note')
            ->add('created_at')
            ->add('updated_at')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Messages::class,
            'csrf_protection'=> false,
        ]);
    }
}
