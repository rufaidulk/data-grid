<?php

namespace Packages\DataGrid;

final class DefaultAction
{
    private $model;
    private $routePrefix;
    private $defaultActions;

    public function __construct($routePrefix, $model)
    {
        $this->model = $model;
        $this->routePrefix = $routePrefix;
        $this->defaultActions = ['view', 'update', 'delete'];
    }
    
    public function render()
    {
        $resultHtml = $form = $onclick = '';

        foreach ($this->defaultActions as $action)
        {
            switch ($action)
            {
                case 'view':
                    $title = 'View';
                    $btnClass= 'btn-success';
                    $iconName = 'ion-eye';
                    $url = route($this->routePrefix . '.show', $this->model);
                    break;

                case 'update':
                    $title = 'Update';
                    $btnClass= 'btn-info';
                    $iconName = 'ion-edit';
                    $url = route($this->routePrefix . '.edit', $this->model);
                    break;

                case 'delete':
                    $title = 'Delete';
                    $btnClass= 'btn-danger';
                    $iconName = 'ion-trash-a';
                    $url = '#';
                    $formId = $this->model->id . '-delete-form';
                    $onclick = "confirmDelete(\"{$formId}\")";
                    // $onclick = "event.preventDefault(); document.getElementById(\"{$formId}\").submit();";
                    // $onclick = "(function(){if(confirm('Are you sure?')){$('form#delete-form').submit()}})()";
                    // $onclick = "(function() { if(confirm(\'Are you sure?\')) { document.getElementById(\"{$formId}\").submit(); } })()";
                    // $onclick .= "document.getElementById(\"{$formId}\").submit();";
                    $formUrl = route($this->routePrefix . '.destroy', $this->model);
                    $form = '<form id="' . $formId . '" action="' . $formUrl . '" method="POST" style="display: none;">
                                ' . csrf_field() . '
                                ' . method_field("DELETE") . '
                            </form>';
                    // $url = $formUrl;
                    break;
            }

            $btn = "<a href='{$url}' class='btn {$btnClass} btn-icon waves-effect waves-light m-b-5 mr-1' ";
            // if ($action == 'delete') {
            //     $btn = "<a type='button' class='btn {$btnClass} btn-icon waves-effect waves-light m-b-5 mr-1' ";
            // }

            $btn .= $action === 'delete' ? "onclick='{$onclick}' title='{$title}'>" : "title='{$title}'>";
            $btn .= "<span class='{$iconName}'></span></a>";
            // if ($action == 'delete') dd($btn);
            $resultHtml .= $btn . $form;
        }
        
        return $resultHtml;
    }
}