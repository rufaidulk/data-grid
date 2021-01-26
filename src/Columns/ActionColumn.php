<?php

namespace Rufaidulk\DataGrid\Columns;

use InvalidArgumentException;

final class ActionColumn
{
    /**
     * action config
     * 
     * @var array
     */
    private $config;

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    private $model;

    /**
     * @var array
     */
    private $actions;

    /**
     * @var string
     */
    private $actionHtml;

    /**
     * @var string
     */
    private $customActionHtml;

    public function __construct($config, $model)
    {
        $this->config = $config;
        $this->model = $model;
        $this->customActionHtml = '';

        $this->setActions();
    }
    
    /**
     * @return string
     */
    public function render()
    {
        $this->createActionColumn();

        return $this->getActionViewHtml();
    }

    public function createActionColumn()
    {
        $resultHtml = $form = $onclick = '';

        foreach ($this->actions as $action)
        {
            switch ($action)
            {
                case 'view':
                    $custom = false;
                    $title = 'View';
                    $btnClass= 'btn-success';
                    $iconName = 'ion-eye';
                    $url = route($this->config['routePrefix'] . '.show', $this->model);
                    break;

                case 'update':
                    $custom = false;
                    $title = 'Update';
                    $btnClass= 'btn-info';
                    $iconName = 'ion-edit';
                    $url = route($this->config['routePrefix'] . '.edit', $this->model);
                    break;

                case 'delete':
                    $custom = false;
                    $title = 'Delete';
                    $btnClass= 'btn-danger';
                    $iconName = 'ion-trash-a';
                    $url = '#';
                    $formId = $this->model->id . '-delete-form';
                    $onclick = "confirmDelete(\"{$formId}\")";
                    $formUrl = route($this->config['routePrefix'] . '.destroy', $this->model);
                    $form = '<form id="' . $formId . '" action="' . $formUrl . '" method="POST" style="display: none;">
                                ' . csrf_field() . '
                                ' . method_field("DELETE") . '
                            </form>';
                    break;
                
                default:
                    $custom = true;
                    $this->appendCustomAction($action);
            }

            if ($custom) {
                continue;
            }
            
            $btn = "<a href='{$url}' class='btn {$btnClass} btn-icon waves-effect waves-light m-b-5 mr-1' ";
            $btn .= $action === 'delete' ? "onclick='{$onclick}' title='{$title}'>" : "title='{$title}'>";
            $btn .= "<span class='{$iconName}'></span></a>";
            $resultHtml .= $btn . $form;
        }
        
        $this->actionHtml = $resultHtml;
    }

    /**
     * @return string
     */
    private function getActionViewHtml()
    {
        return $this->actionHtml . $this->customActionHtml;
    }

    private function setActions()
    {
        $this->actions = $this->getDefaultActions();

        if (isset($this->config['buttons'])) {
            $this->actions = $this->config['buttons'];
        }
    }

    /**
     * @return array
     */
    private function getDefaultActions()
    {
        return ['view', 'update', 'delete'];
    }

    /**
     * @param string
     * 
     * @throws \InvalidArgumentException
     */
    private function appendCustomAction($action)
    {
        if (! isset($this->config[$action])) {
            return;
        }

        if (is_string($this->config[$action])) {
            $this->customActionHtml .= $this->config[$action];
        }
        else if (is_callable($this->config[$action])) {
            $this->customActionHtml .= call_user_func($this->config[$action], $this->model);
        }
        else {
            throw new InvalidArgumentException('button definition must be a callable or a string');
        }

    }
}