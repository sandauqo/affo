<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class IslandSetupTest extends TestCase
{
    private const NORWEGIANS = [
        ['code' => 'A', 'name' => 'isleofman', 'coins' => 0],
        ['code' => 'B', 'name' => 'isleofskye', 'coins' => 0],
        ['code' => 'C', 'name' => 'outerhebrides', 'coins' => 0],
        ['code' => 'D', 'name' => 'islay', 'coins' => 0],
    ];
    private const ORIGINALS = [
        ['code' => 'A', 'name' => 'shetland', 'coins' => 0],
        ['code' => 'B', 'name' => 'faroeislands', 'coins' => 0],
        ['code' => 'C', 'name' => 'iceland', 'coins' => 0],
        ['code' => 'D', 'name' => 'greenland', 'coins' => 0],
    ];
    private const MINI1 = [
        ['code' => 'A', 'name' => 'lofoten', 'coins' => 0],
        ['code' => null, 'name' => 'tierradelfuego', 'coins' => 0],
    ];
    private const MINI2 = [
        ['code' => 'B', 'name' => 'isleofmull', 'coins' => 0],
    ];

    public static function setups(): array
    {
        $cases = [];
        foreach ([1, 2, 3, 4] as $players) {
            $cases["override $players players"] = [$players, true, true, false, false, [], array_merge(self::NORWEGIANS, self::ORIGINALS)];
            $cases["base game $players players"] = [$players, false, false, false, false, [], self::ORIGINALS];
        }
        foreach ([1, 2] as $players) {
            $cases["Norwegians $players players"] = [$players, true, false, false, false, [], self::NORWEGIANS];
        }
        $cases['Norwegians 4 players'] = [4, true, false, false, false, [], array_merge(self::NORWEGIANS, self::ORIGINALS)];
        foreach ([[true, false], [false, true], [true, true]] as [$mini1, $mini2]) {
            $extras = array_merge($mini1 ? self::MINI1 : [], $mini2 ? self::MINI2 : []);
            $cases["solo mini1=$mini1 mini2=$mini2"] = [1, true, false, $mini1, $mini2, [], array_merge(self::NORWEGIANS, $extras)];
            $cases["four players mini1=$mini1 mini2=$mini2"] = [4, true, false, $mini1, $mini2, [], array_merge(self::NORWEGIANS, self::ORIGINALS, $extras)];
            $cases["override mini1=$mini1 mini2=$mini2"] = [3, true, true, $mini1, $mini2, [], array_merge(self::NORWEGIANS, self::ORIGINALS, $extras)];
            $cases["base game mini1=$mini1 mini2=$mini2"] = [3, false, false, $mini1, $mini2, [], array_merge(self::ORIGINALS, $extras)];
        }
        foreach ([0, 1] as $ab) {
            foreach ([0, 1] as $cd) {
                $selected = [self::ORIGINALS[$ab], self::ORIGINALS[$cd + 2]];
                foreach ([[false, false], [true, false], [false, true], [true, true]] as [$mini1, $mini2]) {
                    $expected = array_merge(self::NORWEGIANS, $selected, $mini1 ? self::MINI1 : [], $mini2 ? self::MINI2 : []);
                    $cases["3 players ab=$ab cd=$cd mini1=$mini1 mini2=$mini2"] = [3, true, false, $mini1, $mini2, [$ab, $cd], $expected];
                }
            }
        }
        return $cases;
    }

    #[DataProvider('setups')]
    public function testSetup(int $players, bool $norwegians, bool $all, bool $mini1, bool $mini2, array $random, array $expected): void
    {
        $result = $this->runSetup(compact('players', 'norwegians', 'all', 'mini1', 'mini2', 'random'));
        usort($expected, fn($a, $b) => strcmp($a['name'], $b['name']));
        $this->assertSame($expected, $result['islands']);
        $this->assertSame($random ? [[0, 1], [0, 1]] : [], $result['randomCalls']);
        $this->assertSame([], $result['unusedRandom']);
    }

    public static function flips(): array
    {
        return [
            'A' => ['A', ['shetland' => 'bearisland', 'lofoten' => 'orkney', 'isleofman' => 'limerick']],
            'B' => ['B', ['faroeislands' => 'baffinisland', 'isleofskye' => 'wexford', 'isleofmull' => 'caithness']],
            'C' => ['C', ['iceland' => 'labrador', 'outerhebrides' => 'cork']],
            'D' => ['D', ['greenland' => 'newfoundland', 'islay' => 'waterford']],
            'unknown code' => ['X', []],
        ];
    }

    #[DataProvider('flips')]
    public function testFlip(string $code, array $backs): void
    {
        $result = $this->runSetup([
            'players' => 4, 'norwegians' => true, 'all' => false,
            'mini1' => true, 'mini2' => true, 'random' => [], 'flip' => $code,
        ]);
        $expected = array_merge(self::NORWEGIANS, self::ORIGINALS, self::MINI1, self::MINI2);
        foreach ($expected as &$island) {
            if (isset($backs[$island['name']])) {
                $island['name'] = $backs[$island['name']];
                $island['code'] = null;
            } else {
                $island['coins'] = 2;
            }
        }
        unset($island);
        usort($expected, fn($a, $b) => strcmp($a['name'], $b['name']));
        $this->assertSame($expected, $result['islands']);
    }

    private function runSetup(array $input): array
    {
        $command = [PHP_BINARY, __DIR__ . '/Support/run-island-setup.php'];
        $process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        $this->assertIsResource($process);
        fwrite($pipes[0], json_encode($input, JSON_THROW_ON_ERROR));
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $this->assertSame(0, proc_close($process), $errors);

        return json_decode($output, true, 512, JSON_THROW_ON_ERROR);
    }
}
