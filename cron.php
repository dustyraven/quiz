<?php declare(strict_types=1);

use Quiz\Database;
use Quiz\QuizCron;
use Quiz\User;

/**
 * Cron to give 20 bonus points to first 3 users with most points every week.
 * Invoke with:
 * 58 23 * * 0 php /path/to/this/file/cron.php
 */

require __DIR__ . '/vendor/autoload.php';

$db = new Database;

(new QuizCron($db, new User($db)))->loadTopUsers()->giveBonus();
