<?php

namespace shadiakiki1986\ArrayUtils;

class CellCounterManagerTest extends \PHPUnit_Framework_TestCase {

  public function test1() {
    $cm=new CellCounterManager();

    // expected
    $exp=array(
      array("A1","B1","C1","D1"),
      array("A2","B2","C2","D2"),
      array("A3","B3","C3","D3"),
      array("A4","B4","C4","D4")
    );

    // header
    foreach(range(0,3) as $k1) {
      foreach(range(0,3) as $k2) {
        $this->assertTrue($cm->cellCurrent()==$exp[$k1][$k2]);
        $cm->cellIncrement(true,false);
      }
      $cm->cellReset(true,false);
      $cm->cellIncrement(false,true);
    }
  }

  public function test2() {
    $cm=new CellCounterManager();

    // check that can go to AZ
    $cm->cellReset(true,false);
    $cm->cellReset(false,true);
    $this->assertTrue($cm->cellCurrent()=="A1");
    foreach(range(1,26) as $k1) $cm->cellIncrement(true,false);
    $this->assertTrue($cm->cellCurrent()=="AA1");
    foreach(range(1,25) as $k1) $cm->cellIncrement(true,false);
    $this->assertTrue($cm->cellCurrent()=="AZ1");
  }

}
