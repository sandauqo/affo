<?php

// The main test bootstrap defines an Island stub, so run the real class separately.
class FeastForOdin
{
    public static $game;
    public static $pdo;

    public static function DbQuery($sql)
    {
        self::$pdo->exec($sql);
    }
}

class IslandSetupGame
{
    public function __construct(private array $options)
    {
    }

    public function isNorwegiansExpansion() { return $this->options['norwegians']; }
    public function isNorwegiansAllIslands() { return $this->options['all']; }
    public function hasMiniExpansion1() { return $this->options['mini1']; }
    public function hasMiniExpansion2() { return $this->options['mini2']; }
}

function bga_rand($min, $max)
{
    global $randomValues, $randomCalls;
    $randomCalls[] = [$min, $max];
    if (!$randomValues) {
        throw new RuntimeException('Unexpected random call');
    }
    $value = array_shift($randomValues);
    if ($value < $min || $value > $max) {
        throw new RuntimeException('Random value outside requested bounds');
    }
    return $value;
}

require dirname(__DIR__, 2) . '/modules/class.island.php';

$input = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
$randomValues = $input['random'];
$randomCalls = [];
FeastForOdin::$pdo = new PDO('sqlite::memory:');
FeastForOdin::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
FeastForOdin::$pdo->exec('CREATE TABLE available_islands (code TEXT, name TEXT, coins INTEGER)');
$game = new IslandSetupGame($input);
FeastForOdin::$game = $game;
Island::setup($game, array_fill(0, $input['players'], []));
if (isset($input['flip'])) {
    Island::flip($input['flip']);
}

echo json_encode([
    'islands' => FeastForOdin::$pdo->query('SELECT code, name, coins FROM available_islands ORDER BY name')->fetchAll(PDO::FETCH_ASSOC),
    'randomCalls' => $randomCalls,
    'unusedRandom' => $randomValues,
], JSON_THROW_ON_ERROR);
