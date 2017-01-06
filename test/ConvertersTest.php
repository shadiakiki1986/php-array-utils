<?php

namespace shadiakiki1986\ArrayUtils;

class ConverterstTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->table = array(array("A"=>1,"B"=>2),array("A"=>3,"B"=>4));
    }

    public function testArray2ConsoleNonempty()
    {
        $actual = Converters::array2console($this->table);
        $expected = __DIR__."/fixtures/array2console.txt";
        //file_put_contents($expected,$actual);
        $expected = file_get_contents($expected);
        $this->assertEquals($expected, $actual);
    }

    public function testArray2ConsoleEmpty()
    {
        $actual = Converters::array2console([]);
        $this->assertEquals("No data".PHP_EOL, $actual);
    }

    public function testArray2ConsoleDateTimeDoesntFail()
    {
        $actual = Converters::array2console([
          [\DateTime::createFromFormat('Y-m-d','2014-05-06')]
        ]);
        $this->assertEquals(1,1); // just assert no exception
    }

    public function testArray2HtmlOk()
    {
        $actual = Converters::array2html($this->table);
        $expected = __DIR__."/fixtures/array2html.html";
        //file_put_contents($expected,$actual);
        $expected = file_get_contents($expected);
        $this->assertEquals($expected,$actual);
    }

  public function testArray2HtmlFailNotA2d() {
    try {
      var_dump(Converters::array2html(array(4,5,6)));
      $this->assertTrue(false); // shouldn't get here
    } catch(\Exception $e) {
      $this->assertTrue($e->getMessage()=="Only arrays of arrays supported");
    }
  }

  public function testArray2htmlUrls() {
    $data=array(
      array("name"=>"bla","nav"=>2,"url"=>"http://test"),
      array("name"=>"bli","nav"=>3,"url"=>"http://test")
    );
    $o=Converters::array2html($data,"",array(),array(),null,array("nav"=>"url"));
    //var_dump($o);
  }

    /**
    * @requires extension zip
    */
    public function testArray2XlsxStrings()
    {
        $this->markTestIncomplete("it seems that PHPExcel saves a UID in the binary xlsx file each time, hence making me unable to replicate generating the file");

        $actual = Converters::array3d2xlsx(array("table"=>$this->table));
        $expected = __DIR__."/fixtures/array2xlsxStrings.xlsx";
        // copy($actual,$expected);
        $this->assertFileEquals($expected, $actual);
    }



    /**
    * @requires extension zip
    */
    public function testArray2XlsxMixed()
    {
        $this->markTestIncomplete("it seems that PHPExcel saves a UID in the binary xlsx file each time, hence making me unable to replicate generating the file");

        $tableWithDates = array(
            array("A"=>Converters::s2d("2015-01-02"),"B"=>2)
          , array("A"=>Converters::s2d("2015-01-03"),"B"=>4)
          , array("A"=>Converters::s2d("2015-01-04"),"B"=>5)
          , array("A"=>Converters::s2d("2015-02-03"),"B"=>6)
          , array("A"=>Converters::s2u("link", "http://www.duckduckgo.com"),"B"=>7)
        );

        $actual = Converters::array3d2xlsx(array("table"=>$tableWithDates));
        $expected = __DIR__."/fixtures/array2xlsxMixed.xlsx";
        //copy($actual,$expected);
        $this->assertFileEquals($expected, $actual);
    }

    /**
    * This test takes around 1.25 minutes on a 2.6 GHz CPU with 4 GB RAM running ubuntu server 16.04
    *
    * @requires extension zip
    */
    public function testArray2XlsxMemory()
    {
        $M=40;
        $N=1000;
        $P=10;

        // generate M x N table
        $data = array();
        foreach(range(1, $N) as $i) {
            array_push(
                $data,
                range(1, $M)
            );
        }

        // generate xlsx file P times
        foreach(range(1, $P) as $i) {
            Converters::array3d2xlsx(array("table"=>$data), true);

            // // use fwrite instead of echo for instant output
            // fwrite(
            // STDOUT,
            // $i
            // ." : "
            // .floor(memory_get_usage()/1000000)
            // ." / "
            // .floor(memory_get_peak_usage()/1000000)
            // ."\n"
            // );

            // $this->assertLessThan(20,floor(memory_get_usage()/1000000));
            $this->assertLessThan(40, floor(memory_get_peak_usage()/1000000));
        }
    }

    // will not fail if there are no string keys in the 3d array
    public function testArray2XlsxNoSheetName()
    {
      Converters::array3d2xlsx([$this->table], true);
    }

    // http://stackoverflow.com/a/3423692
    public function testArrayTranspose()
    {
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
                function ($row) {
                    return array_values($row);
                },
                $expected
            )
        );
        $actual = Converters::arrayTranspose($foo);
        $this->assertEquals($expected, $actual);
    }

}

