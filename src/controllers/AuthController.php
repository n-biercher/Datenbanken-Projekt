<?php
declare(strict_types=1);

final class AuthController
{
    public function handle(string $method, array $query, array $post): string
    {
        $mode = ($query['mode'] ?? 'login') === 'register' ? 'register' : 'login';
        $role = ($query['role'] ?? 'teamchef') === 'veranstalter' ? 'veranstalter' : 'teamchef';

        if ($method === 'POST') {
            verify_csrf_token($post['csrf_token'] ?? null);

            try {
                if ($mode === 'register') {
                    $this->register($role, $post);
                }

                $this->login($role, $post);
            } catch (Throwable $exception) {
                http_response_code(400);
                return $this->renderForm($mode, $role, $post, $exception->getMessage());
            }
        }

        return $this->renderForm($mode, $role);
    }

    public function logout(string $method, array $post): never
    {
        if ($method !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            echo '405 Method Not Allowed';
            exit;
        }

        verify_csrf_token($post['csrf_token'] ?? null);
        logout_user();
        set_flash('Du wurdest erfolgreich abgemeldet.');
        redirect('/');
    }

    private function renderForm(string $mode, string $role, array $values = [], ?string $error = null): string
    {
        if ($mode === 'register' && $role === 'teamchef') {
            $title = 'Team erstellen';
        } else {
            $title = $mode === 'register' ? 'Registrierung' : 'Anmeldung';
        }

        return render_page($title . ' | Stadtradeln', 'auth/form', [
            'titleText' => $title,
            'mode' => $mode,
            'role' => $role,
            'values' => $values,
            'error' => $error,
            'flash' => pull_flash(),
        ]);
    }

    private function register(string $role, array $input): never
    {
        $service = new AuthService(get_database_connection());

        if ($role === 'teamchef') {
            $service->registerTeamchef([
                'teamname' => trim((string) ($input['teamname'] ?? '')),
                'loginname' => trim((string) ($input['loginname'] ?? '')),
                'kennwort' => (string) ($input['kennwort'] ?? ''),
                'kennwort_bestaetigung' => (string) ($input['kennwort_bestaetigung'] ?? ''),
                'vorname' => trim((string) ($input['vorname'] ?? '')),
                'nachname' => trim((string) ($input['nachname'] ?? '')),
            ]);
        } else {
            $service->registerVeranstalter([
                'loginname' => trim((string) ($input['loginname'] ?? '')),
                'kennwort' => (string) ($input['kennwort'] ?? ''),
                'kennwort_bestaetigung' => (string) ($input['kennwort_bestaetigung'] ?? ''),
            ]);
        }

        set_flash($role === 'teamchef'
            ? 'Team und Teamchef wurden erfolgreich erstellt. Du kannst dich jetzt anmelden.'
            : 'Registrierung erfolgreich. Du kannst dich jetzt anmelden.'
        );

        redirect('/auth?mode=login&role=' . $role);
    }

    private function login(string $role, array $input): never
    {
        $service = new AuthService(get_database_connection());
        $user = $service->login(
            $role,
            trim((string) ($input['loginname'] ?? '')),
            (string) ($input['kennwort'] ?? '')
        );

        login_user($user);
        set_flash('Anmeldung erfolgreich.');
        redirect('/dashboard');
    }
}
