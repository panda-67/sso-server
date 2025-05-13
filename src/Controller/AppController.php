<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AppController extends BaseController
{
    #[Route('/', name: 'app_root')]
    public function app(): Response
    {
        return $this->renderApp();
    }
}
