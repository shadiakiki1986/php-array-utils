# ArrayUtils

ArrayUtils is a collection of useful PHP array functions.

[![Build Status](https://travis-ci.org/shadiakiki1986/php-array-utils.svg?branch=master)](https://travis-ci.org/shadiakiki1986/php-array-utils)
[Packagist](https://packagist.org/packages/shadiakiki1986/array-utils)


This is a fork of [theodorejb/array-utils](https://github.com/theodorejb/array-utils):
[![Packagist Version](https://img.shields.io/packagist/v/theodorejb/array-utils.svg)](https://packagist.org/packages/theodorejb/array-utils) [![License](https://img.shields.io/packagist/l/theodorejb/array-utils.svg)](https://packagist.org/packages/theodorejb/array-utils) [![Build Status](https://travis-ci.org/theodorejb/array-utils.svg?branch=master)](https://travis-ci.org/theodorejb/array-utils)


Major changes are that I require `php>=7` for the sake of the parameter types in `CellCounterManager` functions


## Install via Composer

`composer require shadiakiki1986/array-utils`

and add bootstrap

`require_once __DIR__."/vendor/autoload.php";`

## Functions

### contains_all

Returns true if all the needles are in the haystack.

```php
use function theodorejb\ArrayUtils\contains_all;

$haystack = [1, 2, 3, 5, 8, 13];
$needles = [2, 13, 5];
echo contains_all($needles, $haystack); // true
echo contains_all($haystack, $needles); // false
```

### contains_same

Returns true if both arrays contain the same values (regardless of order).

```php
use function theodorejb\ArrayUtils\contains_same;

$set1 = [1, 3, 5, 7];
$set2 = [3, 7, 5, 1];

echo contains_same($set1, $set2); // true
```

### group_rows

Splits the array of associative arrays into groups when the specified key value changes.
The array must be sorted by the array key used to group results.

```php
use function theodorejb\ArrayUtils\group_rows;

// obtained by joining tables of people and their pets
$peoplePets = [
    ['name' => 'Jack', 'petName' => 'Scruffy'],
    ['name' => 'Jack', 'petName' => 'Spot'],
    ['name' => 'Jack', 'petName' => 'Paws'],
    ['name' => 'Amy', 'petName' => 'Blackie'],
    ['name' => 'Amy', 'petName' => 'Whiskers']
];

$grouped = [];

foreach (group_rows($peoplePets, 'name') as $group) {
    $grouped[] = $group;
}

$expected = [
    [
        $peoplePets[0],
        $peoplePets[1],
        $peoplePets[2],
    ],
    [
        $peoplePets[3],
        $peoplePets[4],
    ]
];

var_dump($grouped === $expected); // bool(true)
```

### array3d2xlsx

Dumps array of arrays as xlsx file, with each subarray as a sheet

Requires `apt-get install php-zip` and `composer require PHPOffice/PHPExcel`

```php
use shadiakiki1986\ArrayUtils\Converters;

// obtained by joining tables of people and their pets
$peoplePets = [
    ['name' => 'Jack', 'petName' => 'Scruffy'],
    ['name' => 'Jack', 'petName' => 'Spot'],
    ['name' => 'Jack', 'petName' => 'Paws'],
    ['name' => 'Amy', 'petName' => 'Blackie'],
    ['name' => 'Amy', 'petName' => 'Whiskers']
];

var_dump(Converters::array3d2xlsx(array("people-pets"=>$peoplePets))); // returns path to xlsx filename in temporary directory
```

For excel dates in the cells, use the \DateTime class for the php values, e.g.
```
$people = [
    ['name' => 'Roula', 'dateOfBirth' => \DateTime::createFromFormat('!Y-m-d','1982-10-05')],
    ['name' => 'Shadi', 'dateOfBirth' => \DateTime::createFromFormat('!Y-m-d','1986-09-22')]
];
```
Note the `!` preceding `Y-m-d` above resets the hours/minutes/seconds to 0 so that they don't show up in the excel data autofilter.
Check the docs for [DateTime::createFromFormat](http://php.net/manual/en/datetime.createfromformat.php) for more details.

Memory issues:
* for large excel files, phpexcel could run out of memory (check [here](https://github.com/PHPOffice/PHPExcel/blob/1c8c2379ccf5ab9dd7cb46be965821d22173bcf4/Documentation/markdown/Overview/04-Configuration-Settings.md))
* Need `apt-get install sqlite3`
* pass `true` for the `isLarge` (2nd) parameter to `array3d2xlsx`

### array2console

Dumps a 3d array to a string in tabular format for viewing in the console

```php
use shadiakiki1986\ArrayUtils\Converters;

// obtained by joining tables of people and their pets
$peoplePets = [
    ['name' => 'Jack', 'petName' => 'Scruffy'],
    ['name' => 'Jack', 'petName' => 'Spot'],
    ['name' => 'Jack', 'petName' => 'Paws'],
    ['name' => 'Amy', 'petName' => 'Blackie'],
    ['name' => 'Amy', 'petName' => 'Whiskers']
];

echo(Converters::array2console($peoplePets)); // outputs array in tabular format
```

### array2html

Dumps a 3d array to a html string in tabular format for viewing in a browser

```php
use shadiakiki1986\ArrayUtils\Converters;

// obtained by joining tables of people and their pets
$peoplePets = [
    ['name' => 'Jack', 'petName' => 'Scruffy'],
    ['name' => 'Jack', 'petName' => 'Spot'],
    ['name' => 'Jack', 'petName' => 'Paws'],
    ['name' => 'Amy', 'petName' => 'Blackie'],
    ['name' => 'Amy', 'petName' => 'Whiskers']
];

echo(Converters::array2html($peoplePets)); // outputs array in html table
```

## Author

Theodore Brown  
<http://theodorejb.me>

Shadi Akiki

## License

MIT

## Dev notes
If `phpcs` reports errors that can be fixed automatically, run `vendor/bin/phpcbf src/` and then commit the changes
