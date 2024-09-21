<?php

declare(strict_types=1);

use App\Application\Controllers\UploadController;
use Slim\App;

return function (App $app): void {
    $app->post('/upload', UploadController::class . ':upload');
};
