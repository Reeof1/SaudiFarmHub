<?php
declare(strict_types=1);

namespace Controllers;

use Core\BaseController;

class HomeController extends BaseController
{
    public function index(): void
    {
        $this->view('home/index');
    }
}

