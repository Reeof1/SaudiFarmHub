<?php
declare(strict_types=1);

namespace Controllers;

use Core\BaseController;
use Core\Security;
use Models\Farm;

class OwnerFarmController extends BaseController
{
    public function index(): void
    {
        $this->requireRole(['owner']);
        $user = $this->user();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 9;
        $offset = ($page - 1) * $perPage;

        $farmModel = new Farm();
        $farms = $farmModel->getByOwnerId($user['id'], $perPage, $offset);
        $total = $farmModel->countByOwnerId($user['id']);
        $totalPages = (int)ceil($total / $perPage);

        $this->view('owner/farms/index', [
            'farms' => $farms,
            'page' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    public function create(): void
    {
        $this->requireRole(['owner']);
        $this->view('owner/farms/create');
    }

    public function store(): void
    {
        $this->requireRole(['owner']);
        Security::requireCsrfToken();

        $name = trim($_POST['name'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($name === '' || $location === '' || $description === '') {
            $this->view('owner/farms/create', ['error' => 'All fields are required.']);
            return;
        }

        $user = $this->user();
        $farmModel = new Farm();
        $farmModel->create([
            'owner_id' => (int)$user['id'],
            'name' => $name,
            'location' => $location,
            'description' => $description,
        ]);

        $this->redirect('owner/farms');
    }

    public function edit(): void
    {
        $this->requireRole(['owner']);
        $id = (int)($_GET['id'] ?? 0);
        $user = $this->user();

        $farmModel = new Farm();
        $farm = $farmModel->getByIdForOwner($id, (int)$user['id']);
        if (!$farm) {
            http_response_code(404);
            echo 'Farm not found.';
            return;
        }

        $this->view('owner/farms/edit', ['farm' => $farm]);
    }

    public function update(): void
    {
        $this->requireRole(['owner']);
        Security::requireCsrfToken();

        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($id <= 0 || $name === '' || $location === '' || $description === '') {
            $this->view('owner/farms/edit', ['error' => 'All fields are required.']);
            return;
        }

        $user = $this->user();
        $farmModel = new Farm();
        $farmModel->update($id, (int)$user['id'], [
            'name' => $name,
            'location' => $location,
            'description' => $description,
        ]);

        $this->redirect('owner/farms');
    }

    public function delete(): void
    {
        $this->requireRole(['owner']);
        Security::requireCsrfToken();

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('owner/farms');
        }

        $user = $this->user();
        $farmModel = new Farm();
        $farmModel->delete($id, (int)$user['id']);

        $this->redirect('owner/farms');
    }
}

