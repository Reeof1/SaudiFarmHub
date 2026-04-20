<?php
declare(strict_types=1);

namespace Controllers;

use Core\ActivityTypes;
use Core\BaseController;
use Core\Cities;
use Core\Security;
use Models\Farm;

class FarmController extends BaseController
{
    public function index(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 9;
        $offset = ($page - 1) * $perPage;

        $farmModel = new Farm();

        $userLat = $_GET['user_lat'] ?? null;
        $userLng = $_GET['user_lng'] ?? null;
        $sortedByDistance = false;

        if (is_numeric($userLat) && is_numeric($userLng)) {
            $lat = (float)$userLat;
            $lng = (float)$userLng;
            if ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180) {
                $farms = $farmModel->getPaginatedSortedByDistance($lat, $lng, $perPage, $offset);
                $sortedByDistance = true;
            } else {
                $farms = $farmModel->getPaginated($perPage, $offset);
            }
        } else {
            $farms = $farmModel->getPaginated($perPage, $offset);
        }

        $total = $farmModel->countActive();
        $totalPages = (int)ceil($total / $perPage);

        $farmIds = array_map(static fn ($f) => (int)$f['id'], $farms);
        $visitCounts = $farmModel->getVisitCountsByFarmIds($farmIds);

        $favoriteFarmIds = [];
        $user = $this->user();
        if ($user !== null && ($user['role'] ?? '') === 'visitor' && !empty($farmIds)) {
            $favoriteFarmIds = $farmModel->getFavoriteFarmIdsForUser((int)$user['id'], $farmIds);
        }

        $ratings = $farmModel->getRatingsForFarmIds($farmIds);

        $this->view('farm/index', [
            'farms' => $farms,
            'page' => $page,
            'totalPages' => $totalPages,
            'sortedByDistance' => $sortedByDistance,
            'cities' => Cities::LIST,
            'visitCounts' => $visitCounts,
            'favoriteFarmIds' => $favoriteFarmIds,
            'ratings' => $ratings,
        ]);
    }

    public function show(): void
    {
        $farmId = (int)($_GET['farm_id'] ?? 0);
        if ($farmId <= 0) {
            http_response_code(422);
            echo 'farm_id is required.';
            return;
        }

        $farmModel = new Farm();
        $farm = $farmModel->getById($farmId);
        if (!$farm) {
            http_response_code(404);
            echo 'Farm not found.';
            return;
        }

        // Record a unique visit for logged-in visitors only.
        $user = $this->user();
        if ($user !== null && ($user['role'] ?? '') === 'visitor') {
            $farmModel->recordVisit($farmId, (int)$user['id']);
        }

        $visitCount = $farmModel->countVisits($farmId);

        // Activities for booking
        $activityModel = new \Models\Activity();
        $activities = $activityModel->getActiveByFarmId($farmId);

        $gallery = $farmModel->getGalleryImages($farmId);

        $isFavorite = false;
        $userReview = null;
        if ($user !== null && ($user['role'] ?? '') === 'visitor') {
            $isFavorite = $farmModel->isFavorite((int)$user['id'], $farmId);
            $userReview = $farmModel->getUserReview((int)$user['id'], $farmId);
        }

        $reviews = $farmModel->getReviewsByFarm($farmId);
        $ratingSummary = $farmModel->getRatingSummary($farmId);

        $this->view('farm/view', [
            'farm' => $farm,
            'activities' => $activities,
            'gallery' => $gallery,
            'visitCount' => $visitCount,
            'isFavorite' => $isFavorite,
            'reviews' => $reviews,
            'ratingSummary' => $ratingSummary,
            'userReview' => $userReview,
        ]);
    }

    public function search(): void
    {
        Security::requireCsrfToken();
        header('Content-Type: application/json');

        $name = trim((string)($_POST['name'] ?? ''));
        $city = trim((string)($_POST['city'] ?? ''));
        $availabilityDate = trim((string)($_POST['availability_date'] ?? ''));
        $minPrice = $_POST['min_price'] ?? '';
        $maxPrice = $_POST['max_price'] ?? '';

        $activityTypesRaw = $_POST['activity_types'] ?? [];
        if (!is_array($activityTypesRaw)) {
            $activityTypesRaw = [];
        }
        $activityTypes = [];
        foreach ($activityTypesRaw as $t) {
            $t = trim((string)$t);
            if ($t !== '' && ActivityTypes::isValid($t)) {
                $activityTypes[] = $t;
            }
        }
        $activityTypes = array_values(array_unique($activityTypes));

        // Ignore any city value that isn't in our fixed list.
        if ($city !== '' && !Cities::isValid($city)) {
            $city = '';
        }

        $page = max(1, (int)($_POST['page'] ?? 1));
        $perPage = 9;
        $offset = ($page - 1) * $perPage;

        $userLatRaw = $_POST['user_lat'] ?? null;
        $userLngRaw = $_POST['user_lng'] ?? null;
        $userLat = null;
        $userLng = null;
        $sortedByDistance = false;
        if (is_numeric($userLatRaw) && is_numeric($userLngRaw)) {
            $lat = (float)$userLatRaw;
            $lng = (float)$userLngRaw;
            if ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180) {
                $userLat = $lat;
                $userLng = $lng;
                $sortedByDistance = true;
            }
        }

        $filters = [
            'name' => $name,
            'city' => $city,
            'activity_types' => $activityTypes,
            'availability_date' => $availabilityDate,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
        ];

        $farmModel = new Farm();
        $farms = $farmModel->searchFiltered($filters, $perPage, $offset, $userLat, $userLng);

        $farmIds = array_map(static fn ($f) => (int)$f['id'], $farms);
        $visitCounts = $farmModel->getVisitCountsByFarmIds($farmIds);
        $ratings = $farmModel->getRatingsForFarmIds($farmIds);

        ob_start();
        include dirname(__DIR__) . '/views/farm/_farm_cards.php';
        $html = ob_get_clean();

        echo json_encode(['success' => true, 'html' => $html, 'page' => $page]);
    }

    public function toggleFavorite(): void
    {
        Security::requireCsrfToken();
        header('Content-Type: application/json');

        $this->requireRole(['visitor']);
        $user = $this->user();

        $farmId = (int)($_POST['farm_id'] ?? 0);
        if ($farmId <= 0) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'farm_id is required.']);
            return;
        }

        $farmModel = new Farm();
        $farm = $farmModel->getById($farmId);
        if (!$farm) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Farm not found.']);
            return;
        }

        $userId = (int)$user['id'];
        $isFav = $farmModel->isFavorite($userId, $farmId);
        if ($isFav) {
            $farmModel->removeFavorite($userId, $farmId);
            $nowFav = false;
        } else {
            $farmModel->addFavorite($userId, $farmId);
            $nowFav = true;
        }

        echo json_encode(['success' => true, 'favorited' => $nowFav]);
    }

    public function favorites(): void
    {
        $this->requireRole(['visitor']);
        $user = $this->user();

        $farmModel = new Farm();
        $farms = $farmModel->getFavoritesByUser((int)$user['id']);

        $this->view('visitor/favorites', [
            'farms' => $farms,
        ]);
    }

    public function submitReview(): void
    {
        Security::requireCsrfToken();
        $this->requireRole(['visitor']);
        $user = $this->user();

        $farmId = (int)($_POST['farm_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 0);
        $comment = trim((string)($_POST['comment'] ?? ''));

        if ($farmId <= 0 || $rating < 1 || $rating > 5 || $comment === '') {
            $_SESSION['review_error'] = 'Please provide both a rating (1-5) and a comment.';
            $this->redirect('farm/view?farm_id=' . $farmId);
            return;
        }

        $farmModel = new Farm();
        $farm = $farmModel->getById($farmId);
        if (!$farm) {
            http_response_code(404);
            echo 'Farm not found.';
            return;
        }

        $userId = (int)$user['id'];

        if ($farmModel->getUserReview($userId, $farmId) !== null) {
            $_SESSION['review_error'] = 'You have already reviewed this farm. Delete your existing review to submit a new one.';
            $this->redirect('farm/view?farm_id=' . $farmId);
            return;
        }

        $farmModel->addReview($userId, $farmId, $rating, $comment);
        $this->redirect('farm/view?farm_id=' . $farmId);
    }

    public function deleteReview(): void
    {
        Security::requireCsrfToken();
        $this->requireRole(['visitor']);
        $user = $this->user();

        $farmId = (int)($_POST['farm_id'] ?? 0);
        if ($farmId <= 0) {
            http_response_code(422);
            echo 'farm_id is required.';
            return;
        }

        $farmModel = new Farm();
        $farmModel->deleteReview((int)$user['id'], $farmId);
        $this->redirect('farm/view?farm_id=' . $farmId);
    }

    public function ownerReplyReview(): void
    {
        Security::requireCsrfToken();
        $this->requireRole(['owner']);
        $user = $this->user();

        $farmId   = (int)($_POST['farm_id']   ?? 0);
        $reviewId = (int)($_POST['review_id'] ?? 0);
        $reply    = trim((string)($_POST['reply'] ?? ''));

        if ($farmId <= 0 || $reviewId <= 0 || $reply === '') {
            $_SESSION['review_error'] = 'Please write a reply before submitting.';
            $this->redirect('farm/view?farm_id=' . $farmId);
            return;
        }

        if (mb_strlen($reply) > 1000) {
            $reply = mb_substr($reply, 0, 1000);
        }

        $farmModel = new Farm();
        $ok = $farmModel->addOwnerReply($farmId, $reviewId, (int)$user['id'], $reply);
        if (!$ok) {
            $_SESSION['review_error'] = 'Unable to post reply. You may not own this farm.';
        }
        $this->redirect('farm/view?farm_id=' . $farmId);
    }

    public function ownerDeleteReply(): void
    {
        Security::requireCsrfToken();
        $this->requireRole(['owner']);
        $user = $this->user();

        $farmId   = (int)($_POST['farm_id']   ?? 0);
        $reviewId = (int)($_POST['review_id'] ?? 0);

        if ($farmId <= 0 || $reviewId <= 0) {
            http_response_code(422);
            echo 'farm_id and review_id are required.';
            return;
        }

        $farmModel = new Farm();
        $farmModel->deleteOwnerReply($farmId, $reviewId, (int)$user['id']);
        $this->redirect('farm/view?farm_id=' . $farmId);
    }

    public function adminDeleteReview(): void
    {
        Security::requireCsrfToken();
        $this->requireRole(['admin']);

        $reviewId = (int)($_POST['review_id'] ?? 0);
        $farmId   = (int)($_POST['farm_id']   ?? 0);

        if ($reviewId <= 0) {
            http_response_code(422);
            echo 'review_id is required.';
            return;
        }

        $farmModel = new Farm();
        $farmModel->deleteReviewAsAdmin($reviewId);

        if ($farmId > 0) {
            $this->redirect('farm/view?farm_id=' . $farmId);
        } else {
            $this->redirect('farms');
        }
    }
}

