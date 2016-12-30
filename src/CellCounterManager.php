<?php

namespace shadiakiki1986\ArrayUtils;

class CellCounterManager
{

    // column/row counters
    var $alphas, $col, $row;

    function __construct() 
    {
        $alphas1 = range('A', 'Z');
          $alphas2 = array_map(
              function ($x) {
                    return "A".$x; 
                }, $alphas1
          );
            $this->alphas=array_merge($alphas1, $alphas2);
            $this->col = 0;
            $this->row = 1;
    }

    function cellReset(bool $c, bool $r) 
    {
        if($c) { $this->col = 0;
        }
        if($r) { $this->row = 1;
        }
    }

    function cellCurrent() 
    {
          return
         $this->alphas[$this->col]
         .$this->row;
    }

    function cellIncrement(bool $c, bool $r) 
    {
        if($c) {
            if($this->col+1 > count($this->alphas)) {
                throw new \Exception("Columns beyond ".$this->alphas[count($this->alphas)-1]." not supported");
            }
            $this->col++;
        }
        if($r) { $this->row++;
        }
    }

};

