<?php

include __DIR__ . '/../vendor/autoload.php';

use Rubix\Boards\Timer;
use Rubix\Engine\Counter;
use Rubix\Engine\Stats;

const BOARDS = [
    'regular' => \Rubix\Boards\Boggle::class,
    'big' => \Rubix\Boards\BigBoggle::class,
    'super' => \Rubix\Boards\SuperBigBoggle::class,
    'extreme' => \Rubix\Boards\ExtremeBoggle::class,
];

$board = BOARDS[strtolower($argv[1] ?? 'regular')];
$wordlist = $argv[2] ?? __DIR__ . '/dictionary.txt';
$seconds = $argv[3] ?? 60;

$timer = new Timer();
$counter = new Counter(1);
$score = 0;

echo '╔═════════════════════════════════════════════════════╗' . "\n";
echo '║                                                     ║' . "\n";
echo '║ Find Highest Scoring Boggle Board                   ║' . "\n";
echo '║                                                     ║' . "\n";
echo '╚═════════════════════════════════════════════════════╝' . "\n";
echo 'Generating ' .  Stats::format($board::SIZE) . ' X ' .  Stats::format($board::SIZE) . ' board ... ';

$timer->start();
$board = new $board(file($wordlist));

$runtime = $timer->stop()->result();

echo 'done in ' . $runtime . ' seconds.' . "\n";

$timer->reset()->setInterval((float) $seconds);

readline('Press enter to continue ');

echo "Running simulation ... ";

$timer->start();

$best = ['score' => 0];

while ($timer->isValid()) {
    $layout = $board->shake()->show();
    $game = $board->play();

    if ($game['score'] > $best['score']) {
        $best['score'] = $game['score'];
        $best['words'] = $game['words'];
        $best['layout'] = $layout;
    }

    $counter->next();
}

$interval = $timer->stop()->result();

echo 'done in ' . Stats::format($interval, 5) . ' seconds.' . "\n";

echo "\n";

echo 'Best score was ' . Stats::format($best['score']) . ' out of ' . Stats::format($counter->current()) . ' games.' . "\n";

echo "\n";

echo $best['layout'];

echo "\n";

echo implode(', ', array_keys($best['words'])) . "\n";
