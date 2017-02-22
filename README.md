
# Database Manager Web Interface

DBManager (DBM) is a web interface generator that help you to implement a database 
manager on your website.

Features include:
* Action control on entity
    * Add
    * Edit
    * Remove
* Personalize interface

`v1.2` `22 FEV 17`

## Installation

#### Composer requirement

Add repositories to your `composer.json`

    "repositories" : [
        {
            "type" : "vcs",
            "url" : "https://github.com/.../....git",
            "no-api": true
        }
    ]

Add requirement :

    "require": {
        "hello/worldbundle": "*",
        //...
    },

Update your requirements with `composer update` command.

#### Bundle configuration

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
        entities:
            DisplayName:
                fullName: YourBundle:RealName

## About

DBManagerBundle is a FOUQUET initiative.
See also the [creator](https://github.com/hugo082).

## License

This bundle is under the MIT license. See the complete license [in the bundle](LICENSE)

## Documentation

1. Add an entity

DBM load your entities with your configuration file. You can specify an entity to follow by adding it in your config.yml

    db_manager:
        entities:
            DisplayName:
                fullName: YourBundle:RealName

You can configure different actions on each entity :

     DisplayName:
        fullName: YourBundle:RealName
        listView: myFile_1.html.twig                        # Optional
        formView: myFile_2.html.twig                        # Optional
        mainView: myFile_3.html.twig                        # Optional
        fullPath: YourBundle\Entity\Airport                 # Optional
        formType: AirportEditType                           # Optional
        fullFormType: AnotherBundle\Form\AirportEditType    # Optional
        permission: [ "edit" ]                              # Optional - add | edit | remove

By default, DBM load your entity in `YourBundle\Entity\RealName`, name the form with `RealNameType` and load your form type in 
`YourBundle\Form\formType` (so `YourBundle\Form\RealNameType`)
- `DisplayName` is used by DBM for display on template and in url, you can enter the same name of RealName.
- `permission` is by default full authorized. This parameter is optional but recommended.
- You can call your custom views :
    - `listView` is the view that list the entity.
    - `formView` is the view that display the form
    - `mainView` is the view that call `listview` and `formview`.


2. Configure views

You can configure your views by adding the `views` keyword in your configuration file.

    db_manager:
        views: ~

By default, DBM insert the add form in listing view and list in edit view. But if you want to separate all views, you can 
set the options to `false`.

    db_manager:
        views:
            indexView: index.html.twig
            list:
                add: false
            edit:
                list: false

If you do not specify an argument, the argument takes its default value (`true`)
You can also specify your custom index view with the option `indexView`.

