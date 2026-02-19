<?php

/**
 * Extract version from plugin file for build scripts
 */
$content = file_get_contents(__DIR__.'/regex-validation-for-gravity-forms.php');
if (preg_match('/Version:\s*([0-9.]+)/', $content, $matches)) {
    echo $matches[1];
} else {
    echo '0.0.0';
}
