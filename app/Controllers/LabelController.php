<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Models\Label;
use App\Services\AuthService;
use Exception;

class LabelController
{
    public function apiGetLabels(): void
    {
        $userId = (int)AuthService::id();
        $labels = Label::getByUser($userId);
        Response::json([
            'success' => true,
            'labels' => $labels
        ]);
    }

    public function apiCreateLabel(): void
    {
        $userId = (int)AuthService::id();
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $name = trim($data['name'] ?? '');
        $color = trim($data['color'] ?? '#1a73e8');

        if (empty($name)) {
            Response::error('Label name cannot be empty.', 400);
            return;
        }

        try {
            $labelId = Label::create($userId, $name, $color);
            Response::success('Label created.', ['label_id' => $labelId, 'name' => $name, 'color' => $color]);
        } catch (Exception $e) {
            Response::error('Failed to create label: ' . $e->getMessage(), 400);
        }
    }

    public function apiUpdateLabel(array $params): void
    {
        $userId = (int)AuthService::id();
        $labelId = (int)($params['id'] ?? 0);
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $name = trim($data['name'] ?? '');
        $color = trim($data['color'] ?? '#1a73e8');

        if (!$labelId || empty($name)) {
            Response::error('Label ID and name required.', 400);
            return;
        }

        Label::update($userId, $labelId, $name, $color);
        Response::success('Label updated.');
    }

    public function apiDeleteLabel(array $params): void
    {
        $userId = (int)AuthService::id();
        $labelId = (int)($params['id'] ?? 0);

        if (!$labelId) {
            Response::error('Label ID required.', 400);
            return;
        }

        Label::delete($userId, $labelId);
        Response::success('Label deleted.');
    }
}
