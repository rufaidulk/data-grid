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
        $resultHtml = '';
        
        foreach ($this->actions as $action)
        {
            if (array_key_exists($action, $this->config) || ! in_array($action, $this->getDefaultActions())) {
                if (! is_callable($this->config[$action])) {
                    throw new InvalidArgumentException('Custom actions must be a callable');
                }

                $resultHtml .= call_user_func($this->config[$action], $this->model);
            }
            else {
                $resultHtml .= $this->createDefaultButton($action);
            }
        }
        
        $this->actionHtml = $resultHtml;
    }

    /**
     * @param string $action
     */
    private function createDefaultButton($action)
    {
        $form = $onclick = '';

        switch ($action)
        {
            case 'view':
                $title = 'View';
                $btnClass= 'btn-success';
                $iconName = 'ion-eye';
                $url = $this->getActionRoute('show');
                break;

            case 'update':
                $title = 'Update';
                $btnClass= 'btn-info';
                $iconName = 'ion-edit';
                $url = $this->getActionRoute('edit');
                break;

            case 'delete':
                $title = 'Delete';
                $btnClass= 'btn-danger';
                $iconName = 'ion-trash-a';
                $url = '#';
                $formId = $this->model->id . '-delete-form';
                $onclick = "confirmDelete(\"{$formId}\")";
                $formUrl = $this->getActionRoute('destroy');
                $form = '<form id="' . $formId . '" action="' . $formUrl . '" method="POST" style="display: none;">
                            ' . csrf_field() . '
                            ' . method_field("DELETE") . '
                        </form>';
                break;
        }
        
        $btn = "<a href='{$url}' class='btn {$btnClass} btn-icon waves-effect waves-light m-b-5 mr-1' ";
        $btn .= $action === 'delete' ? "onclick='{$onclick}' title='{$title}'>" : "title='{$title}'>";
        $btn .= "<span class='{$iconName}'></span></a>";

        return $btn . $form;
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
     * @param string $action
     * 
     * @return string
     */
    private function getActionRoute($action)
    {
        $routeName = $this->config['routePrefix'] . '.' . $action;
        if (isset($this->config['routeParams'])) 
        {
            if (! is_callable($this->config['routeParams'])) {
                throw new InvalidArgumentException('Route params must be a callable');
            }

            $routeParams = call_user_func($this->config['routeParams'], $this->model);
        
            return route($routeName, $routeParams);
        }

        return route($routeName, $this->model);
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