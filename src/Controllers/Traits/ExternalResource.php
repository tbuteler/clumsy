<?php

namespace Clumsy\CMS\Controllers\Traits;

use Illuminate\Support\MessageBag;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Clumsy\CMS\Facades\Labeler;

trait ExternalResource
{
    protected function hasImporter()
    {
        return $this->model->importable && strpos($this->model->importer, '@');
    }

    protected function fireImport()
    {
        list($class, $method) = explode('@', $this->model->importer);
        return call_user_func_array([$class, $method], (array)$this->model->importer_parameters) ;
    }

    public function import()
    {
        if ($this->hasImporter()) {

            $import = $this->fireImport();

            if ($import !== false) {
                if ($import instanceof SymfonyResponse) {
                    return $import;
                }

                if ($import instanceof MessageBag && $import->any()) {
                    if ($import->has('error')) {
                        return redirect()->route("{$this->routePrefix}.index")->withAlert([
                            'warning' => $import->first('error'),
                        ]);
                    }

                    return redirect()->route("{$this->routePrefix}.index")->withAlert([
                        'success' => $import->first(),
                    ]);
                }

                // General success
                return redirect()->route("{$this->routePrefix}.index")->withAlert([
                    'success' => trans('clumsy::alerts.import.success', ['resources' => Labeler::displayNamePlural($this->model)]),
                ]);
            }

            // General failure message
            return redirect()->route("{$this->routePrefix}.index")->withAlert([
                'warning' => trans('clumsy::alerts.import.fail', ['resources' => Labeler::displayNamePlural($this->model)]),
            ]);

        } else {
            return redirect()->route("{$this->routePrefix}.index")->withAlert([
                'warning' => trans('clumsy::alerts.import.undefined', ['resources' => Labeler::displayNamePlural($this->model)]),
            ]);
        }
    }
}
