<?php
ini_set('display_errors','Off');
use App\Router;

require __DIR__ . '/vendor/autoload.php';

(new Router())->routes()->run();