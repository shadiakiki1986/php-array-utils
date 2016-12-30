<?php

namespace theodorejb\ArrayUtils;

class ArrayUtilsTest extends \PHPUnit_Framework_TestCase
{
    public function testContainsAll()
    {
        // order shouldn't matter
        $this->assertTrue(contains_all([1, 2], [3, 2, 1]));

        // types must match
        $this->assertFalse(contains_all([1, 2], ["1", "2"]));

        $this->assertFalse(contains_all([1, 2], [1]));
    }

    public function testContainsSame()
    {
        // order shouldn't matter
        $this->assertTrue(contains_same([1, 2], [2, 1]));

        $this->assertFalse(contains_same([1, 2], [3, 2, 1]));
    }

    public function testGroupRows()
    {
        // an array retrieved by joining people and pets tables
        $peoplePets = [
            ['name' => 'Jack', 'petName' => 'Scruffy'],
            ['name' => 'Jack', 'petName' => 'Spot'],
            ['name' => 'Jack', 'petName' => 'Paws'],
            ['name' => 'Amy', 'petName' => 'Blackie'],
            ['name' => 'Amy', 'petName' => 'Whiskers']
        ];

        // the expected array grouped by name
        $expected = [
            [$peoplePets[0], $peoplePets[1], $peoplePets[2]],
            [$peoplePets[3], $peoplePets[4]],
        ];

        $actual = [];

        foreach (group_rows($peoplePets, 'name') as $group) {
            $actual[] = $group;
        }

        $this->assertSame($expected, $actual);
    }

    public function testGroupRowsFalsyGroupValues()
    {
        $rows = [
            ['name' => null, 'petName' => 'Blackie'],
            ['name' => null, 'petName' => 'Whiskers'],
            ['name' => false, 'petName' => 'Scruffy'],
            ['name' => false, 'petName' => 'Spot'],
            ['name' => 0, 'petName' => 'Paws'],
            ['name' => 0, 'petName' => 'Claws'],
            ['name' => '', 'petName' => 'Puffball'],
            ['name' => '', 'petName' => 'Tinker'],
        ];

        $expected = [
            [$rows[0], $rows[1]],
            [$rows[2], $rows[3]],
            [$rows[4], $rows[5]],
            [$rows[6], $rows[7]],
        ];

        $actual = [];

        foreach (group_rows($rows, 'name') as $group) {
            $actual[] = $group;
        }

        $this->assertSame($expected, $actual);
    }

    public function testGroupRowsEmptyArray()
    {
        foreach (group_rows([], 'test') as $group) {
            $this->fail('Empty array incorrectly resulted in yield: '.json_encode($group));
        }
    }

    public function testGroupRowsTraversable()
    {
        $generate = function () {
            for ($i = 0; $i < 4; $i++) {
                $set = $i < 2 ? 1 : 2;
                yield ['set' => $set, 'i' => $i];
            }
        };

        $groups = [];

        foreach (group_rows($generate(), 'set') as $group) {
            $groups[] = $group;
        }

        $expected = [
            [
                ['set' => 1, 'i' => 0],
                ['set' => 1, 'i' => 1],
            ],
            [
                ['set' => 2, 'i' => 2],
                ['set' => 2, 'i' => 3],
            ],
        ];

        $this->assertSame($expected, $groups);
    }

    public function testArrayRound()
    {
        // an array of mixed values
        $peoplePets = [
            ['name' => 'Jack', 'petName' => 'Scruffy', 'bones'=>10.01234],
            ['name' => 'Jack', 'petName' => 'Spot', 'bones'=>10.1923],
            ['name' => 'Jack', 'petName' => 'Paws', 'bones'=>10.1999]
        ];

        // the expected array grouped by name
        $expected = [
            array_merge($peoplePets[0], ['bones'=>10.01]),
            array_merge($peoplePets[1], ['bones'=>10.19]),
            array_merge($peoplePets[2], ['bones'=>10.20])
        ];

        $actual = [];

        foreach (array_round($peoplePets, 2) as $group) {
            $actual[] = $group;
        }

        $this->assertSame($expected, $actual);
    }
}
