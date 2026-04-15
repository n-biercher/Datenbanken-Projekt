<?php
declare(strict_types=1);

final class AppController
{
    public function __construct(
        private readonly HomeController $homeController = new HomeController(),
        private readonly AuthController $authController = new AuthController(),
        private readonly DashboardController $dashboardController = new DashboardController()
    ) {
    }

    public function dispatch(string $requestPath, string $method): never
    {
        switch ($requestPath) {
            case '/':
            case '/index.php':
                $this->respondHtml($this->homeController->index());

            case '/auth':
            case '/auth.php':
                $this->respondHtml($this->authController->handle($method, $_GET, $_POST));

            case '/dashboard':
            case '/dashboard.php':
                $this->respondHtml($this->dashboardController->index());

            case '/logout':
            case '/logout.php':
                $this->authController->logout($method, $_POST);

            default:
                http_response_code(404);
                header('Content-Type: text/plain; charset=utf-8');
                echo '404 Not Found';
                exit;
        }
    }

    private function respondHtml(string $content): never
    {
        header('Content-Type: text/html; charset=utf-8');
        echo $content;
        exit;
    }
}
