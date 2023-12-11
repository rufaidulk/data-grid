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
                $iconName = 'bi bi-eye';
                $url = route($this->config['routePrefix'] . '.show', $this->model);
                break;

            case 'update':
                $title = 'Update';
                $btnClass= 'btn-warning';
                $iconName = 'bi bi-pencil';
                $url = route($this->config['routePrefix'] . '.edit', $this->model);
                break;

            case 'delete':
                $title = 'Delete';
                $btnClass= 'btn-danger';
                $iconName = 'bi bi-trash3';
                $url = '#';
                $formId = $this->model->id . '-delete-form';
                $onclick = "confirmDelete(\"{$formId}\")";
                $formUrl = route($this->config['routePrefix'] . '.destroy', $this->model);
                $form = '<form id="' . $formId . '" action="' . $formUrl . '" method="POST" style="display: none;">
                            ' . csrf_field() . '
                            ' . method_field("DELETE") . '
                        </form>';
                break;
        }
        
        $btn = "<a href='{$url}' class='btn {$btnClass} btn-icon waves-effect waves-light me-1' ";
        $btn .= $action === 'delete' ? "onclick='{$onclick}' title='{$title}'>" : "title='{$title}'>";
        $btn .= "<i class='{$iconName}'></i></a>";

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