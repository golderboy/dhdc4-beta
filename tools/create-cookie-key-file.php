<?php

$options = getopt('', ['app:', 'migrate', 'rotate']);
$app = isset($options['app']) ? strtolower((string) $options['app']) : '';

if (!in_array($app, ['frontend', 'backend'], true)) {
    fwrite(STDERR, "Usage: php tools/create-cookie-key-file.php --app=frontend|backend [--migrate|--rotate]\n");
    exit(2);
}
if (isset($options['migrate'], $options['rotate'])) {
    fwrite(STDERR, "Use either --migrate or --rotate, not both.\n");
    exit(2);
}

$root = dirname(__DIR__);
$configDir = $root . DIRECTORY_SEPARATOR . $app . DIRECTORY_SEPARATOR . 'config';
$target = $configDir . DIRECTORY_SEPARATOR . 'cookie-validation-key.php';

if (is_file($target) && !isset($options['rotate'])) {
    echo "$app cookie validation key already exists.\n";
    exit(0);
}

$key = null;
if (isset($options['migrate'])) {
    $source = $configDir . DIRECTORY_SEPARATOR . 'main-local.php';
    $contents = is_file($source) ? file_get_contents($source) : '';
    if (preg_match("/'cookieValidationKey'\\s*=>\\s*'([^']{32,})'/", $contents, $matches)) {
        $key = $matches[1];
    }
}

if ($key === null) {
    $key = rtrim(strtr(base64_encode(random_bytes(48)), '+/', '-_'), '=');
}

$php = "<?php\nreturn " . var_export($key, true) . ";\n";
$temporary = tempnam($configDir, 'dhdc-cookie-');
if ($temporary === false || file_put_contents($temporary, $php, LOCK_EX) === false) {
    if (is_string($temporary)) {
        @unlink($temporary);
    }
    fwrite(STDERR, "Unable to write a temporary cookie key file.\n");
    exit(1);
}

@chmod($temporary, 0600);
if (!rename($temporary, $target)) {
    @unlink($temporary);
    fwrite(STDERR, "Unable to replace $target\n");
    exit(1);
}

echo "$app cookie validation key " . (isset($options['rotate']) ? 'rotated' : 'created') . ".\n";
