<?php

namespace theodorejb\ArrayUtils;

use Traversable;

/**
 * Returns true if all the needles are in the haystack
 *
 * @param array $needles
 * @param array $haystack
 * @return bool
 */
function contains_all(array $needles, array $haystack)
{
    // return false if any of the needles aren't in the haystack
    foreach ($needles as $needle) {
        if (!in_array($needle, $haystack, true)) {
            return false;
        }
    }

    return true;
}

/**
 * Returns true if the two arrays contain exactly the same values
 * (not necessarily in the same order)
 *
 * @param array $ar1
 * @param array $ar2
 * @return bool
 */
function contains_same(array $ar1, array $ar2)
{
    return contains_all($ar1, $ar2) && contains_all($ar2, $ar1);
}

/**
 * Splits the array of rows into groups when the specified column value changes.
 * Note that the rows must be sorted by the column used to divide results.
 *
 * @param array | Traversable $rows
 * @param string $groupColumn
 * @return \Iterator
 * @throws \Exception if $rows is not an array or Traversable
 */
function group_rows($rows, $groupColumn)
{
    if (!is_array($rows) && !$rows instanceof Traversable) {
        throw new \Exception('$rows must be array or Traversable');
    }

    $divideColVal = null;
    $itemSet = [];

    foreach ($rows as $row) {
        if ($divideColVal === $row[$groupColumn]) {
            // same set of items
            $itemSet[] = $row;
            continue;
        }

        // new set of items
        if (!empty($itemSet)) {
            yield $itemSet; // yield previous set
        }

        $itemSet = [$row]; // start over
        $divideColVal = $row[$groupColumn];
    }

    if (!empty($itemSet)) {
        yield $itemSet;
    }
}

/**
 * Rounds all floats in an array using http://php.net/manual/en/function.round.php.
 *
 * @param array | Traversable $rows
 * @param int $precision
 * @return \Iterator
 * @throws \Exception if $rows is not an array or Traversable
 */
function array_round(array $rows, int $precision)
{
    if (!is_array($rows) && !$rows instanceof Traversable) {
        throw new \Exception('$rows must be array or Traversable');
    }

    return array_map(
      function($row) use($precision) {
        if(is_array($row)) {
          return array_round($row,$precision);
        }
        if(is_float($row)) {
          return round($row,$precision);
        }
        return $row;
      },
      $rows
    );
}
