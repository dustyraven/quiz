<?php declare(strict_types=1);

use Quiz\Database;
use Quiz\Datasource;
use Quiz\Quiz;
use Quiz\Subscription;
use Quiz\User;

require __DIR__ . '/vendor/autoload.php';

$result = [];

try {
    // Init objects

    $datasource = (new Datasource($argv[1]))
        ->decodeJson()
        ->validateData()
        ->setVariables();
    $database = new Database;
    $user = (new User($database))->load($datasource->getUserId());
    $subscription = (new Subscription($database, $user))->load();

    // Check the subscription

    if (!$user->inTrial() && !$subscription->exists()) {
        $subscription->create()->load();
    }

    if (!$user->inTrial() && !$subscription->isActive()) {
        throw new Exception('No access to the service');
    }

    if ($subscription->isLastDay()) {
        $subscription->renew();
    }

    // Quiz part

    $quiz = new Quiz($database, $user);
    $answerId = $datasource->getAnswerId();

    if (!empty($answerId)) {
        $points = $quiz->setUserAnswer($answerId);
        if ($points) {
            $user->updatePoints($points);
        }
        $result['correct'] = (bool)$points;
    }

    $result['points'] = $user->getPoints();

    if ($quiz->canAnswerMoreQuestions()) {
        $question = $quiz->getNextQuestion();
        $result['next'] = $question;
    }
} catch (Exception $e) {
    $result['error'] = $e->getMessage();
}

echo json_encode($result);
