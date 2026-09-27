<?php

use lbuchs\WebAuthn\WebAuthnException;
use TasmoAdmin\Helper\PasskeyHelper;
use TasmoAdmin\PasskeyRepository;

header('Content-Type: application/json');
header('Cache-Control: no-store');

// render_raw would wrap the output in a 200 response, so answer and stop here.
$respond = static function (int $status, array $body): never {
    http_response_code($status);
    echo json_encode($body);

    exit;
};

// Every call is a JSON POST: browsers cannot send that cross-site without a
// CORS preflight, which keeps the endpoints out of reach of CSRF.
if ('POST' !== $request->getMethod() || !str_starts_with((string) $request->headers->get('Content-Type'), 'application/json')) {
    $respond(405, ['error' => 'method not allowed']);
}

$payload = json_decode((string) $request->getContent(), true);
$payload = is_array($payload) ? $payload : [];

$repository = new PasskeyRepository(_DATADIR_.'passkeys.json');
$passkeyHelper = new PasskeyHelper($repository, $request->getHost());
// Only metadata leaves the server; public keys stay in passkeys.json.
$publicList = static fn (): array => array_map(
    static fn (array $entry): array => [
        'id' => $entry['id'],
        'name' => $entry['name'],
        'createdAt' => $entry['createdAt'],
        'lastUsedAt' => $entry['lastUsedAt'],
    ],
    $repository->all()
);
$requiresLogin = in_array($action, ['register_options', 'register', 'list', 'delete'], true);

if ($requiresLogin && !$loggedin) {
    $respond(401, ['error' => __('PASSKEY_LOGIN_REQUIRED', 'LOGIN')]);
}

try {
    switch ($action) {
        case 'register_options':
            $respond(200, (array) $passkeyHelper->registrationOptions((string) $Config->read('username'), $_SESSION));

            // no break
        case 'register':
            $passkeyHelper->finishRegistration($payload, (string) ($payload['name'] ?? ''), $_SESSION);
            $respond(200, ['ok' => true, 'passkeys' => $publicList()]);

            // no break
        case 'list':
            $respond(200, ['passkeys' => $publicList()]);

            // no break
        case 'delete':
            $respond(200, ['ok' => $repository->remove((string) ($payload['id'] ?? ''))]);

            // no break
        case 'login_options':
            if ('1' !== $Config->read('login') || [] === $repository->all()) {
                $respond(404, ['error' => __('PASSKEY_NONE_REGISTERED', 'LOGIN')]);
            }
            $respond(200, (array) $passkeyHelper->loginOptions($_SESSION));

            // no break
        case 'login':
            $passkeyHelper->finishLogin($payload, $_SESSION);
            session_regenerate_id(true);
            $_SESSION['login'] = '1';
            $respond(200, ['ok' => true, 'redirect' => _BASEURL_.$Config->read('homepage')]);

            // no break
        default:
            $respond(404, ['error' => 'unknown action']);
    }
} catch (WebAuthnException $exception) {
    $respond(400, ['error' => __('PASSKEY_FAILED', 'LOGIN')]);
}
