<?php

namespace modules\student\components;

use PhpOffice\PhpSpreadsheet\IOFactory;
use yii\helpers\Json;

class Excel {

    protected $spreadsheet;

    public function __construct($file) {
        if ($file instanceof \SplFileInfo) {
            $filename = $file->getRealPath();
        } else {
            $filename = $file;
        }
        $this->spreadsheet = IOFactory::load($filename);
        $this->spreadsheet->setActiveSheetIndex(0);
    }

    public function setActiveSheetIndex($index) {
        $this->spreadsheet->setActiveSheetIndex($index);
    }

    /**
     * Create array from worksheet
     *
     * @param mixed $nullValue Value returned in the array entry if a cell doesn't exist
     * @param boolean $calculateFormulas Should formulas be calculated?
     * @param boolean $formatData  Should formatting be applied to cell values?
     * @param boolean $returnCellRef False - Return a simple array of rows and columns indexed by number counting from zero
     *                               True - Return rows and columns indexed by their actual row and column IDs
     * @return array
     */
    public function toArray($nullValue = null, $calculateFormulas = true, $formatData = false) {

        
        $rows = $this->spreadsheet->getActiveSheet()->toArray($nullValue, $calculateFormulas, $formatData, false);
        $headers = array_shift($rows);

        array_walk($rows, function(&$values) use($headers) {
            $values = array_combine($headers, $values);
        });
        return $rows;
    }

    public function toJson($options = 0, $nullValue = null, $calculateFormulas = true, $formatData = false) {
        return Json::encode($this->toArray($nullValue, $calculateFormulas, $formatData), $options);
    }

}
