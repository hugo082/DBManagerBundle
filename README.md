
# Database Manager Web Interface

DBManager (DBM) is a web interface generator that help you to implement a database manager on your website. 
It's an implementation of FQTDBCoreManager with web interface.

For more information about FQTDBCoreManager, see her [documentation](https://github.com/hugo082/FQTDBCoreManagerBundle)

`v2.0` `17 MAI 17`

## Installation

### Step 1: Install FQTDBCoreManager

For more information about FQTDBCoreManager, see her [documentation](https://github.com/hugo082/FQTDBCoreManagerBundle)

### Step 2: Composer requirement

Add repositories to your `composer.json`

    "repositories" : [
        {
            "type" : "vcs",
            "url" : "https://github.com/hugo082/DBManagerBundle.git",
            "no-api": true
        }
    ]

Add requirement :

    "require": {
        "db/managerbundle": "2.*",
        //...
    },

Update your requirements with `composer update` command.

### Step 3: Bundle configuration

Enable the bundle in the kernel :

    <?php
    // app/AppKernel.php

    public function registerBundles()
    {
        $bundles = array(
            // ...
            new DB\ManagerBundle\DBManagerBundle()
        );
    }

Update your `routing.yml` :

    db.manager:
        resource: "@DBManagerBundle/Resources/config/routing.yml"
        prefix:   /database

Set up your `config.yml` :

    db_manager:

## About

DBManagerBundle is a FOUQUET initiative.
See also the [creator](https://github.com/hugo082).

## License

This bundle is under the MIT license. See the complete license [in the bundle](LICENSE)

## Documentation

### Override templates

DBM use 2 templates (`index` and `main`). Index view is the view that list all your entity and index view execute action on your entity.
You can override this template with `templates` argument in your configuration file.

    templates:
        index_view: MyBundle:PathTo:MyView.html.twig

### Views configuration

Each action can have a specific view. To do this, you can define `views` property in your configuration file.

    views:
        - { action: myAction1, default_view: list, container: [] }
        — { action: myAction2, custom_view: MyBundle:PathTo:MyView.html.twig, container: [ add ]}

For each view, you can define :
- template : the template is load with `default_view` or `custom_view` argument. `default_view` can be `form` or `list`
and load default template.
- container : `container` is an array that contains all actions that the main action `action` contain in her view.

### Links configuration

Each action can have links that will be inserted into its view. To do this, you can define `links` property in your configuration file.
The approach to the links is very similar to the views

    links:
        - { action: myAction1, container: [] }
        — { action: myAction2, container: [ add ]}

An action can be linked only if it's a `global` action. See [FQTDBCoreManagerBundle](https://github.com/hugo082/FQTDBCoreManagerBundle)