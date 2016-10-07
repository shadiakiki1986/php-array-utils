<?php

namespace shadiakiki1986\ArrayUtils;

class CellCounterManager {

	// column/row counters
	var $alphas;
	var $counter;

	function __construct() {
		$this->alphas1 = range('A', 'Z');
    $this->alphas2 = array_map(function($x) { return "A".$x; },$this->alphas1);
    $this->alphas=array_merge($this->alphas1,$this->alphas2);
		$this->counter=array("col"=>0,"row"=>1);
	}

	function cellReset($c,$r) {
		$this->counter=array(
			"col"=>($c?0:$this->counter["col"]),
			"row"=>($r?1:$this->counter["row"])
		);
	}
	function cellCurrent() { return $this->alphas[$this->counter["col"]].$this->counter["row"]; }
	function cellIncrement($c,$r) {
		if($c) $this->counter["col"]++;
		if($r) $this->counter["row"]++;
	}

};

