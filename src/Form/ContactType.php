<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom',TextType::class,[
                'label' => 'Votre nom',
                'attr' => [
                    'placeholder' => 'Entrez votre nom'
                ],
            ])
            ->add('prenom',TextType::class,[
                'label' => 'Votre prénom',
                'attr' => [
                    'placeholder' => 'Entrez votre prénom'
                ]
            ])
            ->add('email',EmailType::class,[
                'label' => 'Votre email',
                'attr' => [
                    'placeholder' => 'Merci de saisir votre adresse email'
                ]
                ])
            ->add('content', TextareaType::class,[
                'label' => 'Votre message',
                'attr' => [
                    'placeholder' => 'En quoi pouvons-nous vous aider ?'
                ]
                ])
            ->add('submit', SubmitType::class,[
                'label' => 'Envoyer',
                'attr' => [
                    'class' => 'btn-block btn-success text-yellow background-grey border-grey'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
