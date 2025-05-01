<?php
// Charger l'autoloader
require __DIR__ . '/../vendor/autoload.php';

// Charger l'application Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Forcer l'environnement à 'testing'
$app->loadEnvironmentFrom('.env.testing');

// Démarrer l'application
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();