<?php
/**
 * contact.php — Kontaktformular-Backend für Khalil Global Trade
 * ---------------------------------------------------------------
 * Nimmt die POST-Daten des Kontaktformulars (siehe index.html, #contact-form)
 * entgegen, validiert und bereinigt sie serverseitig und verschickt eine
 * E-Mail an die Firmenadresse. Antwortet ausschließlich mit JSON — das
 * Formular wird per fetch() aus js/script.js angesprochen.
 *
 * Sicherheitsmaßnahmen in diesem Skript:
 *   1. CSRF-Schutz über ein Session-Token (Synchronizer-Token-Pattern).
 *   2. Herkunftsprüfung via Origin-/Referer-Header (Defense in Depth,
 *      ersetzt NICHT den CSRF-Token, ergänzt ihn nur).
 *   3. Honeypot-Feld gegen einfache Spam-Bots.
 *   4. Serverseitiges Rate-Limiting pro IP-Adresse (Datei-basiert, ohne DB).
 *   5. Strikte Eingabevalidierung & -begrenzung (Länge, Format).
 *   6. Schutz vor E-Mail-Header-Injection (CRLF-Filterung, feste Header).
 *   7. Keine sensiblen Fehlerdetails an den Client (Logging nur serverseitig).
 *   8. Sichere Session-/Cookie-Parameter (HttpOnly, SameSite=Strict, Secure).
 *
 * Voraussetzung: PHP 7.4+ (aus Kompatibilitätsgründen ohne exotische
 * Sprachfeatures neuerer Versionen).
 */

declare(strict_types=1);

/* ====================================================================== */
/* Konfiguration — hier anpassen                                          */
/* ====================================================================== */

// Empfänger der Kontaktanfragen.
const MAIL_TO = 'info@khalilglobaltrade.de';

// Absenderadresse muss zur eigenen Domain gehören (SPF/DKIM/Zustellbarkeit).
// NIEMALS die vom Besucher eingegebene E-Mail-Adresse als "From" verwenden —
// das wäre Absender-Spoofing und würde von Mailservern als Spam eingestuft.
const MAIL_FROM = 'no-reply@khalilglobaltrade.de';
const MAIL_FROM_NAME = 'Khalil Global Trade Website';

// Erlaubte Herkünfte (Origin/Referer-Host) für die Defense-in-Depth-Prüfung.
// Bei Domain-Wechsel oder zusätzlichen Subdomains hier ergänzen.
const ALLOWED_HOSTS = [
    'khalilglobaltrade.de',
    'www.khalilglobaltrade.de',
    'localhost',
    '127.0.0.1',
];

// Rate-Limiting: maximale Anzahl Anfragen pro Zeitfenster und Mindestabstand
// zwischen zwei Anfragen derselben IP-Adresse.
const RATE_LIMIT_MAX_PER_WINDOW = 5;
const RATE_LIMIT_WINDOW_SECONDS = 3600; // 1 Stunde
const RATE_LIMIT_MIN_INTERVAL_SECONDS = 15;

// Verzeichnis für Rate-Limit-Zähler. Muss außerhalb des Web-Zugriffs liegen
// oder per .htaccess gesperrt sein (siehe storage/.htaccess).
const RATE_LIMIT_DIR = __DIR__ . '/storage/ratelimit';

// Nur zur Verschleierung der IP-Adresse im Dateinamen (kein Geheimnis nötig,
// dient lediglich dazu, dass Dateinamen nicht 1:1 die IP preisgeben).
const RATE_LIMIT_PEPPER = 'khalil-gt-contact-2026';

// Feldlängen-Obergrenzen (serverseitig immer prüfen, HTML-maxlength ist nur
// eine UX-Hilfe und kann vom Client umgangen werden).
const MAX_LEN_NAME = 100;
const MAX_LEN_EMAIL = 190;
const MAX_LEN_COMPANY = 150;
const MAX_LEN_MESSAGE = 5000;
const MIN_LEN_MESSAGE = 10;

/* ====================================================================== */
/* Grundabsicherung                                                       */
/* ====================================================================== */

// Keine Fehlerdetails an den Client durchreichen (Information Disclosure).
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Antwort ist immer JSON.
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
// Dieses Skript wird nur same-origin per fetch() aufgerufen — bewusst KEINE
// Access-Control-Allow-Origin-Header setzen, damit fremde Seiten das
// Endpoint nicht per Cross-Origin-fetch auslesen können.
header('Referrer-Policy: same-origin');

/**
 * Beendet die Ausführung mit einer JSON-Antwort und passendem HTTP-Status.
 *
 * @param array<string, mixed> $payload
 */
function json_response(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Loggt einen Fehler serverseitig, ohne Details an den Client zu geben.
 */
function log_error(string $message): void
{
    error_log('[contact.php] ' . $message);
}

/* ====================================================================== */
/* Sichere Session für den CSRF-Token                                     */
/* ====================================================================== */

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['SERVER_PORT'] ?? '') === '443')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Strict',
]);
ini_set('session.use_strict_mode', '1');
session_start();

function issue_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token(?string $submitted): bool
{
    if (empty($_SESSION['csrf_token']) || !is_string($submitted) || $submitted === '') {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $submitted);
}

function rotate_csrf_token(): string
{
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

/* ====================================================================== */
/* GET: Token-Ausgabe für das Frontend (js/script.js holt sich das Token   */
/* vor dem Absenden per fetch, siehe contact.php?csrf=1)                  */
/* ====================================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['csrf'])) {
    json_response(200, ['csrf_token' => issue_csrf_token()]);
}

/* ====================================================================== */
/* Ab hier: nur POST erlaubt                                              */
/* ====================================================================== */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST, GET');
    json_response(405, ['success' => false, 'error' => 'method_not_allowed']);
}

/* ------------------------------------------------------------------ */
/* Defense in Depth: Origin/Referer grob gegen Allowlist prüfen.       */
/* Ersetzt NICHT den CSRF-Token, da Header fälschbar bzw. von manchen  */
/* Browsern/Proxys entfernt werden — daher nur ablehnen, wenn der      */
/* Header vorhanden UND eindeutig fremd ist.                           */
/* ------------------------------------------------------------------ */

function extract_host(string $url): ?string
{
    $host = parse_url($url, PHP_URL_HOST);
    return is_string($host) ? strtolower($host) : null;
}

$originHeader = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? null;
if ($originHeader) {
    $host = extract_host($originHeader);
    if ($host !== null && !in_array($host, ALLOWED_HOSTS, true)) {
        log_error('Abgelehnt wegen fremder Origin/Referer: ' . $host);
        json_response(403, ['success' => false, 'error' => 'forbidden_origin']);
    }
}

/* ------------------------------------------------------------------ */
/* CSRF-Token prüfen                                                    */
/* ------------------------------------------------------------------ */

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    json_response(403, ['success' => false, 'error' => 'invalid_csrf']);
}

/* ------------------------------------------------------------------ */
/* Honeypot: Wenn dieses für Menschen unsichtbare Feld ausgefüllt      */
/* wurde, handelt es sich (fast) sicher um einen Bot. Wir täuschen     */
/* Erfolg vor, ohne tatsächlich eine E-Mail zu versenden, damit Bots   */
/* nicht lernen, das Feld leer zu lassen.                              */
/* ------------------------------------------------------------------ */

if (!empty($_POST['website'])) {
    json_response(200, ['success' => true, 'csrf_token' => rotate_csrf_token()]);
}

/* ------------------------------------------------------------------ */
/* Rate-Limiting pro IP-Adresse (dateibasiert, ohne Datenbank)          */
/* ------------------------------------------------------------------ */

function get_client_ip(): string
{
    // Bewusst NUR REMOTE_ADDR verwenden: X-Forwarded-For & Co. lassen sich
    // vom Client beliebig setzen und würden das Rate-Limiting aushebeln,
    // sofern der Server nicht nachweislich hinter einem vertrauenswürdigen
    // Reverse-Proxy/CDN steht. Falls doch, hier gezielt und mit fester
    // Proxy-IP-Prüfung erweitern statt dem Header blind zu vertrauen.
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

/**
 * @return array{allowed: bool, retryAfter: int}
 */
function check_rate_limit(string $ip): array
{
    if (!is_dir(RATE_LIMIT_DIR)) {
        @mkdir(RATE_LIMIT_DIR, 0755, true);
    }
    if (!is_dir(RATE_LIMIT_DIR) || !is_writable(RATE_LIMIT_DIR)) {
        // Kann nicht limitieren -> im Zweifel nicht blockieren, aber loggen.
        log_error('Rate-Limit-Verzeichnis nicht beschreibbar: ' . RATE_LIMIT_DIR);
        return ['allowed' => true, 'retryAfter' => 0];
    }

    $key = hash('sha256', RATE_LIMIT_PEPPER . '|' . $ip);
    $file = RATE_LIMIT_DIR . '/' . $key . '.json';
    $now = time();

    $handle = fopen($file, 'c+');
    if ($handle === false) {
        log_error('Konnte Rate-Limit-Datei nicht öffnen: ' . $file);
        return ['allowed' => true, 'retryAfter' => 0];
    }

    flock($handle, LOCK_EX);
    $raw = stream_get_contents($handle);
    $data = $raw ? json_decode($raw, true) : null;
    if (!is_array($data)) {
        $data = ['windowStart' => $now, 'count' => 0, 'lastRequest' => 0];
    }

    $result = ['allowed' => true, 'retryAfter' => 0];

    if ($now - (int) $data['lastRequest'] < RATE_LIMIT_MIN_INTERVAL_SECONDS) {
        $result = ['allowed' => false, 'retryAfter' => RATE_LIMIT_MIN_INTERVAL_SECONDS];
    } else {
        if ($now - (int) $data['windowStart'] > RATE_LIMIT_WINDOW_SECONDS) {
            $data['windowStart'] = $now;
            $data['count'] = 0;
        }
        if ((int) $data['count'] >= RATE_LIMIT_MAX_PER_WINDOW) {
            $result = [
                'allowed' => false,
                'retryAfter' => RATE_LIMIT_WINDOW_SECONDS - ($now - (int) $data['windowStart']),
            ];
        }
    }

    if ($result['allowed']) {
        $data['count'] = (int) $data['count'] + 1;
        $data['lastRequest'] = $now;

        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, json_encode($data));
        fflush($handle);
    }

    flock($handle, LOCK_UN);
    fclose($handle);

    return $result;
}

$rate = check_rate_limit(get_client_ip());
if (!$rate['allowed']) {
    header('Retry-After: ' . max(1, $rate['retryAfter']));
    json_response(429, ['success' => false, 'error' => 'rate_limited']);
}

/* ------------------------------------------------------------------ */
/* Eingabevalidierung                                                   */
/* ------------------------------------------------------------------ */

/**
 * Entfernt Steuerzeichen (insbesondere CR/LF), die für E-Mail-Header-
 * Injection missbraucht werden könnten. $allowNewlines erlaubt normale
 * Zeilenumbrüche innerhalb des Nachrichtentexts.
 */
function clean_text(string $value, bool $allowNewlines = false): string
{
    $value = trim($value);
    // Alle Steuerzeichen (0x00–0x1F, 0x7F) entfernen, optional \n behalten.
    $pattern = $allowNewlines ? '/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/u' : '/[\x00-\x1F\x7F]/u';
    $cleaned = preg_replace($pattern, '', $value);
    return $cleaned ?? '';
}

$errors = [];

$name = clean_text((string) ($_POST['name'] ?? ''));
if ($name === '' || mb_strlen($name) < 2) {
    $errors[] = 'name';
} elseif (mb_strlen($name) > MAX_LEN_NAME) {
    $name = mb_substr($name, 0, MAX_LEN_NAME);
}

$emailRaw = clean_text((string) ($_POST['email'] ?? ''));
$email = filter_var($emailRaw, FILTER_VALIDATE_EMAIL);
if ($email === false || mb_strlen($emailRaw) > MAX_LEN_EMAIL) {
    $errors[] = 'email';
}

$company = clean_text((string) ($_POST['company'] ?? ''));
if (mb_strlen($company) > MAX_LEN_COMPANY) {
    $company = mb_substr($company, 0, MAX_LEN_COMPANY);
}

$message = clean_text((string) ($_POST['message'] ?? ''), true);
if (mb_strlen($message) < MIN_LEN_MESSAGE) {
    $errors[] = 'message';
} elseif (mb_strlen($message) > MAX_LEN_MESSAGE) {
    $message = mb_substr($message, 0, MAX_LEN_MESSAGE);
}

if (!empty($errors)) {
    json_response(422, ['success' => false, 'error' => 'validation', 'fields' => $errors]);
}

/* ------------------------------------------------------------------ */
/* E-Mail zusammensetzen & versenden                                    */
/* ------------------------------------------------------------------ */

// Betreff bewusst FEST, nicht aus Nutzereingaben — das schließt jede Form
// von Subject-/Header-Injection über dieses Feld von vornherein aus.
$subject = 'Neue Kontaktanfrage über khalilglobaltrade.de';

$bodyLines = [
    'Neue Anfrage über das Kontaktformular auf khalilglobaltrade.de',
    '',
    'Name:       ' . $name,
    'E-Mail:     ' . $email,
    'Unternehmen: ' . ($company !== '' ? $company : '-'),
    '',
    'Nachricht:',
    $message,
    '',
    '---',
    'Eingegangen: ' . date('d.m.Y H:i:s'),
    'IP-Adresse:  ' . get_client_ip(),
];
$body = wordwrap(implode("\r\n", $bodyLines), 70, "\r\n");

// $email wurde bereits über filter_var(FILTER_VALIDATE_EMAIL) geprüft und
// kann daher keine CR/LF enthalten — sicher für den Reply-To-Header.
$headers = [];
$headers[] = 'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM . '>';
$headers[] = 'Reply-To: <' . $email . '>';
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-Type: text/plain; charset=UTF-8';
$headers[] = 'Content-Transfer-Encoding: 8bit';
$headers[] = 'X-Auto-Response-Suppress: OOF, AutoReply';

$encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

$envelopeSender = '-f' . MAIL_FROM;

$sent = @mail(MAIL_TO, $encodedSubject, $body, implode("\r\n", $headers), $envelopeSender);

if (!$sent) {
    log_error('mail() fehlgeschlagen für Anfrage von ' . $email);
    json_response(500, ['success' => false, 'error' => 'send_failed']);
}

// Token nach erfolgreicher Einreichung rotieren (verhindert Replay des
// gleichen Tokens) und dem Frontend für eine mögliche Folgeeinreichung
// mitgeben.
json_response(200, ['success' => true, 'csrf_token' => rotate_csrf_token()]);
