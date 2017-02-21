
# Database Manager Web Interface

DBManager is a web interface generator that help you to implement a database 
manager on your website.

Features include:
* Action control on entity
    * Add
    * Edit
    * Remove
* Personalize interface

`v1.0` `21 FEV 17`

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
            new FOS\UserBundle\FOSUserBundle()
        );
    }

Update your `config.yml` :

    db_manager:
        views:
            list:
                add: false
            edit:
                list: false
        entities:
            DisplayName: # Display and URL name
                name: RealName
                bundle: AppBundle
                fullpath: AppBundle\Entity\RealName
                formtype: AppBundle\Form\RealNameType
                permission:
                    add: true
                    edit: true
                    remove: false

## About

UserBundle is a FOUQUET initiative.
See also the [creator](https://github.com/hugo082).

## License

This bundle is under the MIT license. See the complete license [in the bundle](LICENSE)