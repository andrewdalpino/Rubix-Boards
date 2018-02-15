<?php

include __DIR__ . '/../vendor/autoload.php';

use Rubix\Boards\Timer;
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

echo '╔═════════════════════════════════════════════════════╗' . "\n";
echo '║                                                     ║' . "\n";
echo '║ Rubix Boggle Solver                                 ║' . "\n";
echo '║                                                     ║' . "\n";
echo '╚═════════════════════════════════════════════════════╝' . "\n";
echo 'Generating ' .  Stats::format($board::SIZE) . ' X ' .  Stats::format($board::SIZE) . ' board ... ';

$timer->start();
$board = new $board(file($wordlist));
$runtime = $timer->stop()->result();

echo 'done in ' . Stats::format($runtime, 5) . ' seconds.' . "\n";

echo "\n";

echo $board->shake()->show();

echo "\n";

readline('Press enter to continue ');

echo "Finding words ... ";

$timer->reset()->start();

$result = $board->play();

$runtime = $timer->stop()->result();

echo 'found ' . Stats::format(Stats::sum($result['words'])) . ' words in ' . Stats::format($runtime, 5) . ' seconds.' . "\n";

echo "\n";

echo implode(', ', array_keys($result['words'])) . "\n";

echo "\n";

echo 'Score: ' . Stats::format($result['score']) . "\n";
echo 'Mean times the same word appears: ' . Stats::format(Stats::mean($result['words']), 2) . "\n";
