<?php

namespace shadiakiki1986\ArrayUtils;

class Array2Xlsx
{

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

    public function __construct(array $tensor)
    {
        $this->tensor = $tensor;

        // counter manager
        $this->ccm=new CellCounterManager();
    }

    /**
     * for large files
     *
     * https://github.com/PHPOffice/PHPExcel/blob/1c8c2379ccf5ab9dd7cb46be965821d22173bcf4/Documentation/markdown/Overview/04-Configuration-Settings.md
     * https://github.com/PHPOffice/PHPExcel/blob/1.8/Examples/06largescale-with-cellcaching.php
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function isLarge()
    {
        //$cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_to_sqlite3;
        if (!\PHPExcel_Settings::setCacheStorageMethod($cacheMethod)) {
            throw new \Exception($cacheMethod . " caching method is not available");
        }
    }

    private function isArray3d()
    {
        array_map(
            function ($row) {
                if (!is_array($row)) {
                    throw new \Exception("Only arrays of arrays of arrays supported");
                }
                array_map(
                    function ($cell) use ($row) {
                        if (!is_array($cell)) {
                            throw new \Exception("Only arrays of arrays of arrays supported");
                        }
                    },
                    $row
                );
            },
            $this->tensor
        );
    }

    private function checkConsistentHeaders()
    {
        // check that all rows in a sheet have the same set of keys
        foreach ($this->tensor as $k1 => $sheet) {
            $keys1=null;
            $nIncons=0;
            foreach ($sheet as $k2 => $row) {
                $keys2 = implode(",", array_keys($row));
                if (is_null($keys1)) {
                    $keys1=$keys2;
                    continue;
                }
                if ($keys1!=$keys2) {
                    $nIncons++;
                    echo(
                    "Inconsistency: sheet '$k1', row '$k2', $keys2 != $keys1, ".
                    json_encode($row, JSON_PRETTY_PRINT)."\n"
                    );
                }
            }

            if ($nIncons>0) {
                throw new \Exception("It seems that sheet '$k1' has rows with $nIncons inconsistent headers. Aborting");
            }
        }
    }

    public function generate()
    {
        // checks
        $this->isArray3d();
        $this->checkConsistentHeaders();

        // Create new PHPExcel object
        $this->objPHPExcel = new \PHPExcel();
        $this->objPHPExcel->removeSheetByIndex(0);

        // populate
        for ($index=0; $index<count($this->tensor); $index++) {
            $this->addSheet($index);
        } // end for loop

        return $this->objPHPExcel;
    }

    private function addSheet($index)
    {
        $kkk2=array_keys($this->tensor);
        $kkk2=$kkk2[$index];

        $matrix=$this->tensor[$kkk2];

        // Create a new worksheet called “My Data”
        $myWorkSheet = new \PHPExcel_Worksheet($this->objPHPExcel, $kkk2);
        $this->objPHPExcel->addSheet($myWorkSheet);// Attach the “My Data” worksheet as the first worksheet in the PHPExcel object

        $this->objPHPExcel->setActiveSheetIndex($index);
        $this->activeSheet = $this->objPHPExcel->getActiveSheet();

        if (count($matrix)==0) {
            $this->activeSheet->setCellValue("A1", "No data");
            return;
        }

        $this->ccm->cellReset(true, true);

        // header
        $kkk=array_keys($matrix);
        foreach (array_keys($matrix[$kkk[0]]) as $k) {
            $this->activeSheet->setCellValue($this->ccm->cellCurrent(), $k);
            $this->ccm->cellIncrement(true, false);
        }
        $this->ccm->cellReset(true, false);
        $this->ccm->cellIncrement(false, true);

        foreach ($matrix as $row) {
            foreach ($row as $cell) {
                $this->addCell($cell);
            }
            $this->ccm->cellReset(true, false);
            $this->ccm->cellIncrement(false, true);
        }
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function addCell($cell)
    {
        if ($cell instanceof \DateTime) {
            // https://github.com/PHPOffice/PHPExcel/blob/1.8/Examples/02types.php#L96
            $cell = \PHPExcel_Shared_Date::PHPToExcel($cell->format('U'));
            $this->activeSheet->getStyle($this->ccm->cellCurrent())->getNumberFormat()->setFormatCode(
                \PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2
            );
        }
        $this->activeSheet->setCellValue($this->ccm->cellCurrent(), $cell);
        $this->ccm->cellIncrement(true, false);
    }
}
