<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseController extends AbstractController
{
    /**
     * @param mixed $content
     * @param mixed $title
     */
    protected function renderApp($content = '', $title = 'Welcome'): Response
    {
        $navbar = $this->renderView('pages/navbar.html.twig');

        return $this->render('app.html.twig', [
            'title' => "PMED | $title",
            'navbar' => $navbar,
            'content' => $content
        ]);
    }

    /**
     * @return array<$errors,array>
     */
    protected function getFormErrors(FormInterface $form): array
    {
        $errors = [];

        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $childErrors = [];

                foreach ($child->getErrors(true) as $error) {
                    $childErrors[] = $error->getMessage();
                }

                $errors[$child->getName()] = $childErrors;
            }

            // Recurse into sub-forms if needed
            if ($child->count() > 0) {
                $nestedErrors = $this->getFormErrors($child);
                if (!empty($nestedErrors)) {
                    $errors[$child->getName()] = $nestedErrors;
                }
            }
        }

        // Handle global (non-field) errors
        foreach ($form->getErrors(true) as $error) {
            if ($form->isRoot()) {
                $errors['_global'][] = $error->getMessage();
            }
        }

        return $errors;
    }
}
