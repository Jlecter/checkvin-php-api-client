<?php

declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

header('Content-Type: application/json');

switch ($path) {
    case '/200-json':
        http_response_code(200);
        echo json_encode(['message' => 'Report found', 'preset_link' => 'https://example.com/hash']);
        break;

    case '/400-json':
        http_response_code(400);
        echo json_encode(['message' => 'Bad request', 'errors' => ['field' => 'required']]);
        break;

    case '/404-json':
        http_response_code(404);
        echo json_encode(['message' => 'Report not found', 'preset_link' => '']);
        break;

    case '/200-html':
        http_response_code(200);
        header('Content-Type: text/html');
        echo '<html><body>Error page</body></html>';
        break;

    case '/empty':
        http_response_code(200);
        echo '';
        break;

    case '/slow':
        sleep(1);
        http_response_code(200);
        echo json_encode(['message' => 'ok']);
        break;

    default:
        http_response_code(404);
        echo json_encode(['message' => 'Not found']);
}
