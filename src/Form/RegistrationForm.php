<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-300 focus:outline-none',
                    'autocomplete' => 'email',
                    'data-json' => 'email',
                    'placeholder' => 'you@example.com',
                ],
                'label' => 'Email Address',
                'label_attr' => ['class' => 'block text-sm font-medium text-gray-700 mb-1'],
                'row_attr' => ['class' => 'mb-4'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter an email',
                    ]),
                    new Email([
                        'message' => 'Please enter a valid email address',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-300 focus:outline-none resize-y',
                    'autocomplete' => 'new-password',
                    'data-json' => 'password',
                    'placeholder' => 'Your password...',
                ],
                'label' => 'Password',
                'label_attr' => ['class' => 'block text-sm font-medium text-gray-700 mb-1'],
                'row_attr' => ['class' => 'mb-4'],
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
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'I agree to the terms and conditions',
                'attr' => [
                    'class' => 'h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded',
                ],
                'label_attr' => [
                    'class' => 'ml-2 block text-xs text-slate-700',
                ],
                'row_attr' => ['class' => 'w-full flex flex-row-reverse justify-center mb-2'],
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms and conditions.',
                    ]),
                ],
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
