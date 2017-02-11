<?php

namespace Clumsy\CMS\Panels\Traits;

use Clumsy\CMS\Support\ExcelViewParser;
use HTTP;
use InvalidArgumentException;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;
use Maatwebsite\Excel\Writers\LaravelExcelWriter;

trait Exportable
{
    public function prepareExportable()
    {
        if ($this->isExportable()) {
            $this->setData([
                'exportLinks' => $this->getExportLinks(),
            ]);
        }
    }

    public function getExportFormats()
    {
        return (array)$this->getOptionalProperty('export', []);
    }

    public function hasExportLabels()
    {
        return Arr::isAssoc($this->getExportFormats());
    }

    public function getExportLabels()
    {
        return $this->hasExportLabels() ? array_values($this->getExportFormats()) : $this->getExportFormats();
    }

    public function isExportable()
    {
        return (count(array_filter(
            $this->hasExportLabels() ? array_keys($this->getExportFormats()) : $this->getExportFormats(),
            [$this, 'canExport']
        )) > 0);
    }

    public function getExportLinks()
    {
        $links = collect();
        foreach ($this->getExportFormats() as $format => $label) {
            if (!$this->hasExportLabels()) {
                $format = $label;
            }
            if (!$this->canExport($format)) {
                continue;
            }
            $links->put($label, HTTP::queryStringAdd(request()->fullUrl(), 'download', $format));
        }
        return $links;
    }

    public function resolve()
    {
        if (request()->has('download')) {
            return $this->resolveFormat(request()->get('download'));
        }

        return response($this->render());
    }

    protected function getFormatResolver($format)
    {
        $format = studly_case($format);
        $type = studly_case($this->getType());
        return "resolve{$type}ExportTo{$format}";
    }

    protected function canExport($format)
    {
        return method_exists($this, $this->getFormatResolver($format));
    }

    protected function resolveFormat($format)
    {
        if (!$this->canExport($format)) {
            $type = $this->getType();
            throw new InvalidArgumentException("Export method of current panel of type \"$type\" is not defined for format \"{$format}\"");
        }
        $resolver = $this->getFormatResolver($format);
        return $this->format($format)->$resolver();
    }

    protected function resolveTableExportToXls()
    {
        $this->itemsPerPage = $this->getOptionalProperty('exportMaximumRows', 60000);
        Excel::create($this->getLabelPlural(), function (LaravelExcelWriter $excel) {
            $excel->sheet($this->getLabelPlural(), function (LaravelExcelWorksheet $sheet) {
                $sheet->freezeFirstRow();
                $sheet->setParser(app(ExcelViewParser::class));
                $sheet->getView()->setHtml($sheet, $this->template('table')->render());
            });
        })->download('xls');
    }
}
