<?php

use PHPUnit\Framework\TestCase;

final class ActionFilterTest extends TestCase
{
    public function testFiltersActionsByGameIncludingSharedActions(): void
    {
        $baseActions = Action::filterActions('base', 'game');
        $norwegiansActions = Action::filterActions('norwegians', 'game');

        self::assertContains(1, $baseActions);
        self::assertNotContains(1, $norwegiansActions);
        self::assertContains(2, $baseActions);
        self::assertContains(2, $norwegiansActions);
    }

    public function testFiltersArrayValuedRows(): void
    {
        $houseActions = Action::filterActions('houses', 'row');

        self::assertContains(1, $houseActions);
        self::assertContains(4, $houseActions);
        self::assertNotContains(5, $houseActions);
    }

    public function testFiltersScalarMetadata(): void
    {
        $firstColumnActions = Action::filterActions(1, 'column');

        self::assertContains(1, $firstColumnActions);
        self::assertContains(5, $firstColumnActions);
        self::assertNotContains(2, $firstColumnActions);
    }
}
