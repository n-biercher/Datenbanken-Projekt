<?php
declare(strict_types=1);

final class DashboardController
{
    public function index(): string
    {
        require_login();

        return render_page('Dashboard | Stadtradeln', 'dashboard/index', [
            'user' => current_user(),
            'flash' => pull_flash(),
        ]);
    }
}
