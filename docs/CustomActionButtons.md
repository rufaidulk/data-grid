## Custom Action Buttons
Customizing default action column
>Default actions are **view, update and delete**

Here only **view** button will show and a custom button **complete**
```
    ....
    'action' => [
        'buttons' => ['view', 'complete'],
        'routePrefix' => 'admin.city',
        'complete' => function ($model) {
            return '<button class="btn btn-md btn-primary">Custom<button>';
        }
    ....
```
