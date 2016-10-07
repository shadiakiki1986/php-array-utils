<?php

namespace shadiakiki1986\ArrayUtils;

class Converters {

  public static function arraykeys2header($arr,$thead) {
    // Find first entry that has a meaningful header
    for($thi=0;$thi<count($arr) && count(array_values($arr)[$thi])==0;$thi++) ;
    if($thi==count($arr)) $thi=0; // no meaninful header found
    $th2=array_keys(array_values($arr)[$thi]);
    if($thead!=null) $th2=array_map(function($row) use($thead) { if(array_key_exists($row,$thead)) return $thead[$row]; else return $row; }, $th2);

    return array("th2"=>$th2,"thi"=>$thi);
  }

  public static function arr2json($arr) { return json_encode(array_values($arr)); }

  /**
   * Pretty-print an array to console in tabular format
   *
   * @param array   $arr         2d array
   * @param string  $caption   table caption
   * @param array   $thead        table headers. Array map of format array("field 1 in $arr[0]"=>"string to replace it","field 2 in $arr[0]"=>"string to replace it",...)
   * @return string
   * @throws \Exception if $arr is not an array of arrays or if $arr is empty
   */
  public static function array2console($arr,$caption="",$thead=null) {
    if(count($arr)==0) throw new \Exception("Empty data passed to array2console");
    $arr=array_map(function($row) { if(!is_array($row)) return array($row); else return $row; }, $arr); // throw new \Exception("Only arrays of arrays supported"); 

    // Find first entry that has a meaningful header
    $th2 = self::arraykeys2header($arr,$thead)["th2"];

    $rowKeys = array_keys($arr); // backup for keys of 1st dim
    $arr = array_values($arr); // keys of 1st dimension of arr are ignored

    $arrNames=null;

    $colWidths = array();
    // check max column widths
    for($i=0;$i<count($arr);$i++) {
      // width for row key
      if(!array_key_exists(0,$colWidths)) $colWidths[0]=0;
      $colWidths[0]=max($colWidths[0],strlen($rowKeys[$i]));

      // width for row values
      for($j=0;$j<count($arr[$i]);$j++) {
        $cell = array_values($arr[$i]);
        $cell = $cell[$j];
        if(is_array($cell)) {
          if(!$arrNames) $arrNames=array_keys($cell);
          $cell=self::arr2json($cell);
        }
        if(!array_key_exists($j+1,$colWidths)) $colWidths[$j+1]=0;
        $colWidths[$j+1] = max($colWidths[$j+1],strlen($cell));
      }
    }
    // do same for header lengths
    for($j=0;$j<count($th2);$j++) {
      $cell = $th2[$j];
      $colWidths[$j+1] = max($colWidths[$j+1],strlen($cell));
    }

    $colFormats = array_map(function($row) { return sprintf("%%%gs\t",$row+2); }, $colWidths); // e.g. "%20s\t"

    $headers = array();
    $headers[0] = sprintf($colFormats[0]." | ","");
    for($i=0;$i<count($th2);$i++) {
      $headers[$i+1] = sprintf($colFormats[$i+1],$th2[$i]);
    }

    $body = array();
    for($i=0;$i<count($arr);$i++) {
      $body[$i] = array();
      $row = array_values($arr[$i]);
      $body[$i][0] = sprintf($colFormats[0]." | ",$rowKeys[$i]);
      for($j=0;$j<count($row);$j++) {
        $body[$i][$j+1] = sprintf($colFormats[$j+1],is_array($row[$j])?self::arr2json($row[$j]):$row[$j]);
      }
    }

    $caption2 = sprintf(
      "%s (%1.0f rows%s)",
      $caption,
      count($arr),
      !!$arrNames?", [".implode(", ",$arrNames)."]":""
    );

    return sprintf("%s\n%s\n%s\n%s\n%s\n%s",
      preg_replace('/./',"-",$caption2),
      $caption2,
      preg_replace('/./',"-",$caption2),
      implode($headers),
      preg_replace('/[a-zA-Z0-9_\/]/',"-",implode($headers)),
      implode(array_map(
        function($row) { return sprintf("%s\n",implode($row)); },
        $body
      ))
    );

  }

  /**
   *  Convert array of arrays of rows to Excel 2007 xlsx file
   *
   *  If a field is of type \DateTime, it becomes an excel date in the cell (and hence when you filter on the column, you get the year/month breakdown)
   *
   *  Example:
   *    array3d2xlsx(
   *      array(
   *        array(array(1,2,3),array(4,5,6)),
   *        array(array(1,2,3),array(4,5,6)),
   *        array(array(1,2,3),array(4,5,6))
   *      )
   *    )
  **/

  public static function array3d2xlsx($arr3d) {

    array_map(function($row) {
      if(!is_array($row)) throw new \Exception("Only arrays of arrays of arrays supported");
      array_map(function($cell) use($row) {
        if(!is_array($cell)) throw new \Exception("Only arrays of arrays of arrays supported");
      }, $row);
    }, $arr3d);

    // check that all rows in a sheet have the same set of keys
    foreach($arr3d as $k1=>$sheet) {
      $keys1=null;
      $nIncons=0;
      foreach($sheet as $k2=>$row) {
        $keys2 = implode(",",array_keys($row));
        if(is_null($keys1)) {
          $keys1=$keys2;
          continue;
        }
        if($keys1!=$keys2) {
          $nIncons++;
          echo(
            "Inconsistency: sheet '$k1', row '$k2', $keys2 != $keys1, ".
            json_encode($row,JSON_PRETTY_PRINT)."\n"
          );
        }
      }

      if($nIncons>0) {
        throw new \Exception("It seems that sheet '$k1' has rows with $nIncons inconsistent headers. Aborting");
      }

    }

    // Create new PHPExcel object
    $objPHPExcel = new \PHPExcel();
    $objPHPExcel->removeSheetByIndex(0);

    // populate
    for($i=0;$i<count($arr3d);$i++) {
      $kkk2=array_keys($arr3d);
      $kkk2=$kkk2[$i];

      $arr2d=$arr3d[$kkk2];

      // Create a new worksheet called “My Data”
      $myWorkSheet = new \PHPExcel_Worksheet($objPHPExcel, $kkk2);
      $objPHPExcel->addSheet($myWorkSheet);// Attach the “My Data” worksheet as the first worksheet in the PHPExcel object

      $objPHPExcel->setActiveSheetIndex($i);

      if(count($arr2d)==0) {
        $objPHPExcel->getActiveSheet()->setCellValue("A1","No data");
        continue;
      }

      // counter manager
      $ccm=new CellCounterManager();

      // header
      $kkk=array_keys($arr2d);
      foreach(array_keys($arr2d[$kkk[0]]) as $k) {
        $objPHPExcel->getActiveSheet()->setCellValue($ccm->cellCurrent(), $k);
        $ccm->cellIncrement(true,false);
      }
      $ccm->cellReset(true,false);
      $ccm->cellIncrement(false,true);

      foreach($arr2d as $k=>$v) {
        foreach($v as $k2=>$v2) {
          if($v2 instanceof \DateTime) {
            // https://github.com/PHPOffice/PHPExcel/blob/1.8/Examples/02types.php#L96
            $v2 = \PHPExcel_Shared_Date::PHPToExcel($v2->format('U'));
            $objPHPExcel->getActiveSheet()->getStyle($ccm->cellCurrent())->getNumberFormat()->setFormatCode(
              \PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2
            );
          }
          $objPHPExcel->getActiveSheet()->setCellValue($ccm->cellCurrent(), $v2);
          $ccm->cellIncrement(true,false);
        }
        $ccm->cellReset(true,false);
        $ccm->cellIncrement(false,true);
      }
    } // end for loop

    // save to file
    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $filename=sprintf("%s.xlsx",tempnam(sys_get_temp_dir(), 'Tux'));
    $objWriter->save($filename);
    return $filename;
  }

  # rightAligns: keys for which td should be right-aligned
  # ...
  # thead: table headers. Array map of format array("field 1 in $arr[0]"=>"string to replace it","field 2 in $arr[0]"=>"string to replace it",...)
  # urls: add a url to a field
  public static function array2html($arr,$caption="",$rightAligns=array(),$importantCol=array(),$thead=null,$urls=null,$skipRowLabels=false) {

    if(count($arr)==0) return "<div>Empty array</div>";

    if(!is_array($rightAligns)) $rightAligns=array($rightAligns);
    if(!is_array($importantCol)) $importantCol=array($importantCol);

    array_map(function($row) { if(!is_array($row)) throw new \Exception("Only arrays of arrays supported"); }, $arr);

    $ak2h=self::arraykeys2header($arr,$thead);
    $th2=$ak2h["th2"];
    if($urls!=null) $th2=array_diff($th2,array_values($urls));
    
    $arrNames=null;
    $tia=array_values($arr)[$ak2h["thi"]];
    if(count($tia)>0) {
      $tia=array_values($tia)[0];
      if(is_array($tia)) $arrNames=json_encode(array_keys($tia));
    }

    $html=array();
    array_push($html, "<table border=1 width='100%'>");
    array_push($html, sprintf("<caption style='text-align:left'>%s</caption>",sprintf("%s (%1.0f rows%s)",$caption,count($arr),$arrNames?", $arrNames":"")));
    array_push($html, sprintf("<thead><tr>%s%s</tr></thead>",$skipRowLabels?"":"<th>&nbsp;</th>",implode(array_map(function($row) { return "<th>{$row}</th>"; }, $th2 ))));
    array_push($html, "<tbody>");
    foreach($arr as $k=>$row) {
      array_push($html, "<tr>");
      if(!$skipRowLabels) array_push($html, sprintf("<td>%s</td>",$k));
      foreach($row as $k=>$y) {
        if($urls==null||!in_array($k,$urls)) {
          //var_dump($row,$k,$urls);
          array_push($html,
            sprintf(
        "<td nowrap style='%s'>%s%s</td>",
        implode(';',
          array(
          (in_array($k,$rightAligns)?"text-align:right":""),
          (in_array($k,$importantCol)?"background:lightgrey":"")
          )
        ),
        is_array($y)?json_encode(array_values($y)):$y,
              $urls!=null&&array_key_exists($k,$urls)&&array_key_exists($urls[$k],$row)?sprintf("&nbsp;<a href='%s'>&gt</a>",$row[$urls[$k]]):""
            )
          );
        } // end if
      }
      array_push($html, "</tr>");
    }
    array_push($html, "</tbody>");
    array_push($html, "</table>");

    return implode("\n",$html);
  }

  // http://stackoverflow.com/a/3423692
  // Note that this loses the keys. Check unit test for example
  public static function array_transpose($array) {
        array_unshift($array, null);
            return call_user_func_array('array_map', $array);
  }
}
