<?php

namespace theodorejb\ArrayUtils;

class ConverterstTest extends \PHPUnit_Framework_TestCase {

    public function setUp() {
        $this->table = array(array("A"=>1,"B"=>2),array("A"=>3,"B"=>4));
    }

    public function testArray2Console() {
        $actual = Converters::array2console($this->table);
        $expected = __DIR__."/fixtures/array2console.txt";
        //file_put_contents($expected,$actual);
        $expected = file_get_contents($expected);
        $this->assertEquals($expected,$actual);
    }

    public function testArray2Html() {
        $actual = Converters::array2html($this->table);
        $expected = __DIR__."/fixtures/array2html.html";
        //file_put_contents($expected,$actual);
        $expected = file_get_contents($expected);
        $this->assertEquals($expected,$actual);
    }

    public function testArray2Xlsx() {
        $actual = Converters::array3d2xlsx(array("table"=>$this->table));
        $expected = __DIR__."/fixtures/array2xlsx.xlsx";

        // TODO it seems that PHPExcel saves a timestamp in the xlsx file, 
        // hence making me unable to replicate generating the file, 
        // and thus requiring me to keep the copy line here, 
        // rendering this test useless
        copy($actual,$expected); 

        $this->assertFileEquals($expected,$actual);
    }

    // http://stackoverflow.com/a/3423692
    public function testArrayTranspose() {
      // Start with this array
      $foo = array(
        'a' => array(
           1 => 'a1',
           2 => 'a2',
           3 => 'a3' 
        ),
        'b' => array(
           1 => 'b1',
           2 => 'b2',
           3 => 'b3' 
        ),
        'c' => array(
           1 => 'c1',
           2 => 'c2',
           3 => 'c3' 
        )
      );
      $expected = array(
        1=>array(
          "a"=>"a1",
          "b"=>"b1",
          "c"=>"c1"
        ),
        2=>array(
          "a"=>"a2",
          "b"=>"b2",
          "c"=>"c2"
        ),
        3=>array(
          "a"=>"a3",
          "b"=>"b3",
          "c"=>"c3"
        )
      );
      // loses keys
      $expected = array_values(
        array_map(
          function($row) { return array_values($row); },
          $expected
        )
      );
      $actual = Converters::array_transpose($foo);
      $this->assertEquals($expected,$actual);
    }

}

