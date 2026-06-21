<?php
$lock = json_decode(file_get_contents($argv[1]), true);
foreach ($lock['packages'] as $pkg) {
    if ($pkg['name'] === 'livewire/livewire') {
        echo 'Livewire version: ' . $pkg['version'] . PHP_EOL;
    }
}
