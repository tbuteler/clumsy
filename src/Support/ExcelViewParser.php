<?php

namespace Clumsy\CMS\Support;

use Maatwebsite\Excel\Parsers\ViewParser;

class ExcelViewParser extends ViewParser
{
    protected $html;

    /**
     * Set raw HTML into the parser
     *
     * @param  \Maatwebsite\Excel\Classes\LaravelExcelWorksheet $sheet
     * @param  string                                           $html
     * @return \Maatwebsite\Excel\Classes\LaravelExcelWorksheet
     */
    public function setHtml($sheet, $html)
    {
        return $this->html = $html;
    }

    /**
     * Parse the view
     * @param  \Maatwebsite\Excel\Classes\LaravelExcelWorksheet $sheet
     * @return \Maatwebsite\Excel\Classes\LaravelExcelWorksheet
     */
    public function parse($sheet)
    {
        return $this->reader->load($this->html, true, $sheet);
    }
}
