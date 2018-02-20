<?php

include __DIR__ . '/../vendor/autoload.php';

use Rubix\Boards\Scrabble;
use Rubix\Boards\Timer;
use Rubix\Engine\Stats;

$wordlist = $argv[1] ?? __DIR__ . '/dictionary.txt';

$timer = new Timer();

echo '╔═════════════════════════════════════════════════════╗' . "\n";
echo '║                                                     ║' . "\n";
echo '║ Rubix Scrabble Solver                               ║' . "\n";
echo '║                                                     ║' . "\n";
echo '╚═════════════════════════════════════════════════════╝' . "\n";
echo 'Generating ' .  Stats::format(Scrabble::WIDTH) . ' X ' .  Stats::format(Scrabble::HEIGHT) . ' board ... ';

$timer->start();
$board = new Scrabble(file($wordlist));
$runtime = $timer->stop()->result();

echo 'done in ' . Stats::format($runtime, 5) . ' seconds.' . "\n";

echo "\n";

readline('Press enter to continue ');

echo "Finding words ... ";

$timer->reset()->start();

$result = $board->play();

$runtime = $timer->stop()->result();

echo 'found ' . Stats::format(count($result['words'])) . ' words in ' . Stats::format($runtime, 5) . ' seconds.' . "\n";

echo "\n";

echo implode(', ', $result['words']) . "\n";

echo "\n";

echo 'Score: ' . Stats::format($result['score']) . "\n";
