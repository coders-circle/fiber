# fiber

**fiber** is a simple, lightweight php framework based on MVC pattern. *fiber* is built for helping development of php projects, when heavy frameworks are not desired.

## Basic User Guide

### Router

All routes are defined as rules in the `Router` class inside the *classes/Router.php* file. Each rule defines the 'path' and the corresponding template or controller that handles that path.

Each rule is of the form:

```php
'path' => array(type, controller_or_template_name)
```

An example of a set of rules is:

```php
$this->routing_rules = array(
    // Default rule
    "default" => "fiber",

    // Template redirection rules
    "fiber" => array("template", "fiber.html"),

    // Controller redirection rules
    "example" => array('controller', 'ExampleController'),
);
```

Here the path `/fiber/` is routed to the template file *views/fiber.html* and the path `/example/` is handled by the controller `ExampleController` in the file *controls/ExampleController.php*.

Templates are just HTML pages that may include some template tags for server side processing. For simple pages that has no need to access database or perform any complicated processing, one can simply route the url directly to a template.

Controllers perform some processing, optionally accessing the database, and pass the processed data to a corresponding view. A view is again built from a template.

### Controllers and Views

Every controller is held in a class derived from the `Controller` base class and is stored inside the *controls* directory. A controller returns a `View` object. A view is constructed from a template, which defines the HTML page to be presented, and optionally some data that the template may use.

An example controller is:

```php
<?php

class ExampleController extends Controller
{
    public function get()
    {
        return new View('example.html');
        // return new View('example.html', array("my_data"=>"my_value"));
    }
}

?>
```

A controller class can have different methods that are called by the framework to handle different url requests.

* `get()` : handles GET requests
* `post()` : handles POST requests
* in general *`method()`* handles HTTP request of type *method*
* `get($x, $y)` : handles GET requests for urls of form *&lt;controller&gt;/x/y/* where strings `x` and `y` are passed as parameters to the method
* `get_foo($x, $y)` : handles GET requests for urls of form *&lt;controller&gt;/foo/x/y/* where `x` and `y` are again passed as parameters to the method

### Templates

Templates are basic text pages, mostly HTML documents, that can be used as views or can be directly displayed by routing a path to it.

TODO Template tags

### Models

Each database table can be accessed with a simple `Model`, where the schema is defined using array of properties, where each property is an array defining the name of the field, its type and other attributes like max_length.

```php
<?php
require_once "../classes/Model.php";

class Example extends Model
{
    public function get_schema() {
        // schema is array of properties
        // each property being the tuple: (field-name, type, [attribute,...])

        return array(
            array("example_data", "string"),
            array("example_data2", "string", "max_length"=>12),
            array("example_data3", "integer")
        );
    }

    public $example_data3 = 15; // default value
}

?>
```

New data can be saved in a table, by calling the `save()` in an instance.

```php
$test = new Example();
$test->example_data = "hello";
$test->example_data2 = "world";
$test->save();
```

One can easily query objects with raw sql queries or using the built-in query builder.

```php
// Get all users
$users = User::get_all();
$users = User::query()->get();

// Raw query
$users = User::raw_query("SELECT * FROM user WHERE first_name='Bibek' AND last_name='Dahal'");

// Query-Builder: Filter users (unsafe)
$users = User::query()->where("first_name='Bibek' AND last_name='Dahal'")->get();

// Query-Builder: Filter users (safe)
$users = User::query()->where("first_name=? AND last_name=?", "Bibek", "Dahal")->get();

// Query-Builder: Filter and project only the last name
$users = User::query()->where("first_name=?", "Bibek")->select("last_name")->get();
```

### Migration

Every time, you create a model and define its schema, and every time you change schema of any model, you should *migrate* the changes for them to be reflected in your database.

Each migration is defined by the sql statements that perform the required changes to the database. The migration is stored in a file *&lt;table_name&gt;_&lt;version&gt;.sql&* inside the *migrations* directory. The version of each schema is kept tracked by a special table *schema_versions* in the database. This way, any new migration can be detected and applied whenever required. Note that you do not need to create these migration files yourself, though you can if you need to.

Two utilities `makemigration` and `migrate`, which are basically php scripts, are available for migration purposes. The `makemigration` command automatically detects any new changes in the schema definition and generates a migration file with naming convention mentioned above. The `migrate` command then apply any new migrations present for a model, effectively changing the database.

```bash
php makemigration MyModel
php migrate MyModel
```
