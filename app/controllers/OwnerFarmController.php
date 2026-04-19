<?php
declare(strict_types=1);

namespace Controllers;

use Core\BaseController;
use Core\Cities;
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
        $this->view('owner/farms/create', ['cities' => Cities::LIST]);
    }

    public function store(): void
    {
        $this->requireRole(['owner']);
        Security::requireCsrfToken();

        $name = trim($_POST['name'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $description = trim($_POST['description'] ?? '');
        [$latitude, $longitude] = $this->parseCoordinates($_POST['latitude'] ?? '', $_POST['longitude'] ?? '');

        if ($name === '' || $description === '' || !Cities::isValid($city)) {
            $this->view('owner/farms/create', [
                'error' => 'All fields are required and city must be a valid region.',
                'cities' => Cities::LIST,
            ]);
            return;
        }

        $user = $this->user();
        $farmModel = new Farm();

        $coverPath = null;
        if (isset($_FILES['cover_photo']) && is_array($_FILES['cover_photo']) && $_FILES['cover_photo']['error'] === UPLOAD_ERR_OK) {
            try {
                $coverPath = $this->saveUploadedImage($_FILES['cover_photo'], 0);
            } catch (\RuntimeException $e) {
                $this->view('owner/farms/create', [
                    'error' => $e->getMessage(),
                    'cities' => Cities::LIST,
                ]);
                return;
            }
        }

        $farmId = $farmModel->create([
            'owner_id' => (int)$user['id'],
            'name' => $name,
            'city' => $city,
            'description' => $description,
            'main_image' => $coverPath,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);

        if ($coverPath !== null) {
            $finalCover = $this->renameImageWithFarmId($coverPath, $farmId);
            if ($finalCover !== $coverPath) {
                $farmModel->update($farmId, (int)$user['id'], [
                    'name' => $name,
                    'city' => $city,
                    'description' => $description,
                    'main_image' => $finalCover,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ]);
            }
        }

        $this->handleGalleryUploads($farmModel, $farmId);

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

        $gallery = $farmModel->getGalleryImages($id);

        $this->view('owner/farms/edit', [
            'farm' => $farm,
            'cities' => Cities::LIST,
            'gallery' => $gallery,
        ]);
    }

    public function update(): void
    {
        $this->requireRole(['owner']);
        Security::requireCsrfToken();

        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $description = trim($_POST['description'] ?? '');
        [$latitude, $longitude] = $this->parseCoordinates($_POST['latitude'] ?? '', $_POST['longitude'] ?? '');

        $user = $this->user();
        $farmModel = new Farm();

        if ($id <= 0 || $name === '' || $description === '' || !Cities::isValid($city)) {
            $farm = $farmModel->getByIdForOwner($id, (int)$user['id']) ?? [];
            $gallery = $id > 0 ? $farmModel->getGalleryImages($id) : [];
            $this->view('owner/farms/edit', [
                'farm' => $farm,
                'cities' => Cities::LIST,
                'gallery' => $gallery,
                'error' => 'All fields are required and city must be a valid region.',
            ]);
            return;
        }

        $existingFarm = $farmModel->getByIdForOwner($id, (int)$user['id']);
        if (!$existingFarm) {
            http_response_code(404);
            echo 'Farm not found.';
            return;
        }

        $data = [
            'name' => $name,
            'city' => $city,
            'description' => $description,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];

        if (isset($_FILES['cover_photo']) && is_array($_FILES['cover_photo']) && $_FILES['cover_photo']['error'] === UPLOAD_ERR_OK) {
            try {
                $newCover = $this->saveUploadedImage($_FILES['cover_photo'], $id);
                $this->deleteImageFile($existingFarm['main_image'] ?? null);
                $data['main_image'] = $newCover;
            } catch (\RuntimeException $e) {
                $gallery = $farmModel->getGalleryImages($id);
                $this->view('owner/farms/edit', [
                    'farm' => array_merge($existingFarm, $data),
                    'cities' => Cities::LIST,
                    'gallery' => $gallery,
                    'error' => $e->getMessage(),
                ]);
                return;
            }
        }

        $farmModel->update($id, (int)$user['id'], $data);

        try {
            $this->handleGalleryUploads($farmModel, $id);
        } catch (\RuntimeException $e) {
            $gallery = $farmModel->getGalleryImages($id);
            $farm = $farmModel->getByIdForOwner($id, (int)$user['id']);
            $this->view('owner/farms/edit', [
                'farm' => $farm,
                'cities' => Cities::LIST,
                'gallery' => $gallery,
                'error' => $e->getMessage(),
            ]);
            return;
        }

        $this->redirect('owner/farms');
    }

    public function deleteImage(): void
    {
        $this->requireRole(['owner']);
        Security::requireCsrfToken();

        $imageId = (int)($_POST['image_id'] ?? 0);
        $farmId = (int)($_POST['farm_id'] ?? 0);

        $user = $this->user();
        $farmModel = new Farm();
        $image = $farmModel->getGalleryImageForOwner($imageId, (int)$user['id']);

        if ($image !== null) {
            $this->deleteImageFile($image['image_path']);
            $farmModel->deleteGalleryImage($imageId);
        }

        $this->redirect('owner/farm/edit?id=' . $farmId);
    }

    private function parseCoordinates(mixed $latRaw, mixed $lngRaw): array
    {
        $lat = is_string($latRaw) ? trim($latRaw) : '';
        $lng = is_string($lngRaw) ? trim($lngRaw) : '';

        if ($lat === '' || $lng === '' || !is_numeric($lat) || !is_numeric($lng)) {
            return [null, null];
        }

        $lat = (float)$lat;
        $lng = (float)$lng;

        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return [null, null];
        }

        return [$lat, $lng];
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

    private const UPLOAD_DIR = 'public/assets/uploads/farms/';
    private const MAX_FILE_SIZE = 5 * 1024 * 1024;
    private const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp'];
    private const MAX_GALLERY_IMAGES = 9;

    private function handleGalleryUploads(Farm $farmModel, int $farmId): void
    {
        if (!isset($_FILES['gallery_photos']) || !is_array($_FILES['gallery_photos']['name'])) {
            return;
        }

        $currentCount = $farmModel->countGalleryImages($farmId);
        $files = $_FILES['gallery_photos'];
        $total = count($files['name']);

        for ($i = 0; $i < $total; $i++) {
            if ((int)$files['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            if ($currentCount >= self::MAX_GALLERY_IMAGES) {
                throw new \RuntimeException('Gallery is limited to ' . self::MAX_GALLERY_IMAGES . ' photos.');
            }

            $fileData = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i],
            ];

            $path = $this->saveUploadedImage($fileData, $farmId);
            $farmModel->addGalleryImage($farmId, $path);
            $currentCount++;
        }
    }

    private function saveUploadedImage(array $file, int $farmId): string
    {
        if ((int)$file['size'] > self::MAX_FILE_SIZE) {
            throw new \RuntimeException('Image file exceeds 5MB limit.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            throw new \RuntimeException('Only JPG, PNG, and WebP images are allowed.');
        }

        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };

        $uploadDir = dirname(__DIR__, 2) . '/' . self::UPLOAD_DIR;
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
            throw new \RuntimeException('Upload directory could not be created.');
        }

        $prefix = $farmId > 0 ? (string)$farmId : 'tmp';
        $filename = $prefix . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $target = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            throw new \RuntimeException('Failed to save uploaded image.');
        }

        return 'assets/uploads/farms/' . $filename;
    }

    private function renameImageWithFarmId(string $relativePath, int $farmId): string
    {
        if (!str_contains($relativePath, '/tmp_')) {
            return $relativePath;
        }

        $basename = basename($relativePath);
        $newName = preg_replace('/^tmp_/', $farmId . '_', $basename);
        $dir = dirname(__DIR__, 2) . '/public/' . dirname($relativePath) . '/';

        if (rename($dir . $basename, $dir . $newName)) {
            return dirname($relativePath) . '/' . $newName;
        }

        return $relativePath;
    }

    private function deleteImageFile(?string $relativePath): void
    {
        if ($relativePath === null || $relativePath === '') {
            return;
        }

        $fullPath = dirname(__DIR__, 2) . '/public/' . ltrim($relativePath, '/');
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}

