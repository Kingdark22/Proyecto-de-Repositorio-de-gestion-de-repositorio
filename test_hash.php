<?php
echo 'sha1(md5(12090416)): ' . sha1(md5('12090416')) . PHP_EOL;
echo 'sha1(md5(strtoupper(12090416))): ' . sha1(md5(strtoupper('12090416'))) . PHP_EOL;
echo 'Target hash: 00bb6238775353c7a12e7b95697317983f101ac1' . PHP_EOL;
echo PHP_EOL;
echo 'Match: ' . (hash_equals('00bb6238775353c7a12e7b95697317983f101ac1', sha1(md5('12090416'))) ? 'YES' : 'NO') . PHP_EOL;
echo PHP_EOL;

// Check 13354832 against various passwords
echo 'Checking 13354832 bcrypt hash...' . PHP_EOL;
$hash = '$2y$10$iSoQ3To2gSoEaHdqOJvUU.W48l8.bp4Bi2sGuEmqF/OenmblYMFyG';
foreach (['13354832', '12090416', 'admin', 'password', '12345678', '123456', 'Admin123', 'root', 'uptp', 'uptp2024', 'uptp2025', 'repositorio', 'sogac', 'sogac2024'] as $pw) {
    if (password_verify($pw, $hash)) {
        echo "  MATCH: $pw\n";
    }
}
echo '  (no match found for 13354832)' . PHP_EOL;
