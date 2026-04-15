<?php
declare(strict_types=1);

final class HomeController
{
    public function index(): string
    {
        return render_page('Radrennen', 'home/index', [
            'flashMessage' => pull_flash(),
            'isLoggedIn' => current_user() !== null,
        ]);
    }
}
