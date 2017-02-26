
# Database Manager Web Interface

DBManager (DBM) is a web interface generator that help you to implement a database manager on your website.

Features include:
* Action control on entity
    * Add
    * Edit
    * Remove
* Personalize interface

`v1.5` `25 FEV 17`

## Installation

### Step 1: Composer requirement

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

### Step 2: Bundle configuration

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

### Add an entity

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
        ppermissions: [ "edit" ]                            # Optional - add | edit | remove | list

By default, DBM load your entity in `YourBundle\Entity\RealName`, name the form with `RealNameType` and load your form type in 
`YourBundle\Form\formType` (so `YourBundle\Form\RealNameType`)
- `DisplayName` is used by DBM for display on template and in url, you can enter the same name of RealName.
- `permissions` is by default full authorized. This parameter is optional but recommended.
- You can call your custom views :
    - `listView` is the view that list the entity.
    - `formView` is the view that display the form
    - `mainView` is the view that call `listview` and `formview`.

<span style="color:#FFC107">**WARNING** :</span> if you do not override views, your entity must be compatible with default views.
To do that, you can extends your entity with `DB\ManagerBundle\Model\BaseManager` class. This abstract class implement necessary methods.<br>
If your entity is already extended, you can also implements all necessary interface :
- DB\ManagerBundle\Model\ListingInterface

### Configure views

You can configure your views by adding the `views` keyword in your configuration file.

    db_manager:
        views: ~

By default, DBM insert the add form in listing view and list in edit view. But if you want to separate all views, you can 
set the options to `false`.

    db_manager:
        views:
            indexView: index.html.twig
            list:
                form: false
            edit:
                list: false

If you do not specify an argument, the argument takes its default value (`true`)
You can also specify your custom index view with the option `indexView`.

### Entity access

#### Role access

You can setup for each entity roles that are necessary to execute specific action or access to a specific information.<br>
For example, if you want that the entity is accessible only to admin users, you can specify the `access` config

    DisplayName:
        access: ROLE_ADMIN
        #...

You can also defined multi-roles :

    DisplayName:
        access: [ ROLE_ADMIN, ROLE_REDACTOR ]
        #...

If you want that users can list and so access to entity information but admins can execute actions on this entity, you
you can defined the parameter `access_details`. This parameter **must** defined roles for all actions :

    Flight:
        access_details:
            add: ROLE_SUPER_ADMIN
            edit: [ ROLE_ADMIN, ROLE_REDACTOR ]
            remove: ROLE_SUPER_ADMIN
            list: ROLE_USER

<span style="color:#FFC107">**WARNING** :</span> if you defined the access_details property, this parameter override access 
and so access is no longer taken into consideration.<br>
<span style="color:#FFC107">**WARNING** :</span> if list action isn't accessible for a user, this user don't have access to
 `.../DisplayName` url, so it can't access to add path/form. Moreover, if it can't list entity, it can't click on links 
 to remove or edit, but, the link is accessible.<br>
<span style="color:#FFC107">**WARNING** :</span> if FOSUserBundle does not installed or enabled, `access` and `access_details`
parameters will be ignored.


#### Custom constraints

You can implement an actionMethod to process a custom constraint. Your method will call for each entity and must return
a boolean (`true` to allow access and `false` to prevent).

    access_details:
        add: ROLE_USER
        addMethod: AppBundle\Entity\Flight::checkAdd
        #...

Your method must a static method and take in parameters user and entity object. For the addMethod, entity object is NULL.

    public static function checkAdd(User $user, Flight $obj = NULL) {
        return $user->isUnabled();
    }

When DBM list your entity, you can also choose your method repository. By default, DBManager use `findAll()` but you can 
override this easily :

    Flight:
        fullName: AppBundle:Flight
        listingMethod: myRepositoryMethod


#### Events

For add, edit and remove actions, events are called. You can listen them and execute a custom process :

    namespace AppBundle\EventListener;
    
    use AppBundle\Entity\Flight;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Doctrine\ORM\EntityManager;
    use DB\ManagerBundle\DBManagerEvents;
    use DB\ManagerBundle\Event\ActionEvent;

    class ActionSubscriber implements EventSubscriberInterface {
        private $em;

        public function __construct(EntityManager $em) {
            $this->em = $em;
        }

        public static function getSubscribedEvents() {
            return array(
                DBManagerEvents::ACTION_REMOVE_BEFORE => 'beforeRemove',
                //..
            );
        }

        public function beforeRemove(ActionEvent $event) {
            $e = $event->getEntityObject();
            if ($e instanceof Flight) {
                if ($e->getId() == 13) {
                    $event->setExecuted(true); // DBM default action ignored
                    $event->setFlash('ERROR', 'You want remove VIP Flight');
                } else
                    $event->setFlash('SUCCESS', 'Your Flight have been removed');
            }
        }
    }

At the end of your process, if `executed` property of event are set to true, DBM will ignore the default action. By default,
the `executed` property is set to false.<br>
Of course you must register your subscriber in your services.