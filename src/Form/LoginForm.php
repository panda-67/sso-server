<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class LoginForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter an email',
                    ]),
                    new Email([
                        'message' => 'Please enter a valid email address',
                    ]),
                ],
                'attr' => [
                    'class' => 'block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-300 focus:outline-none',
                    'data-json' => 'email',
                    'placeholder' => 'you@example.com',
                ],
                'label' => 'Email Address',
                'label_attr' => ['class' => 'block text-sm font-medium text-gray-700 mb-1'],
                'row_attr' => ['class' => 'mb-4'],
            ])
            ->add('password', PasswordType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
                'attr' => [
                    'class' => 'block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-300 focus:outline-none resize-y',
                    'data-json' => 'password',
                    'placeholder' => 'Your password...',
                ],
                'label' => 'Password',
                'label_attr' => ['class' => 'block text-sm font-medium text-gray-700 mb-1'],
                'row_attr' => ['class' => 'mb-4'],
            ])
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'inline-block bg-emerald-500 hover:bg-emerald-700 text-white font-semibold py-1 px-3 rounded transition duration-150 ease-in-out',
                ],
                'label' => 'Sign In',
                'row_attr' => ['class' => 'mt-3 flex justify-center w-full'],
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
