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

Every controller is held in a class derived from the `Controller` base class and is stored inside the *controls* directory. A controller returns a `View` object. A view is constructed from a template, which defines the HTML page to be presented, and a model, which stores data that the template may use.

An example controller is:

```php
<?php

class ExampleController extends Controller
{
    public function get()
    {
        return new View($this->model, 'example.html');
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

A View object is created with an instance of `Model` which can store a bunch of data and a template file. The template can then use the data stored inside the model.

```php
$this->model["var1"] = value1;
return new View($this->model, 'my_template.html');
```

### Template

Templates are basic text pages, mostly HTML documents, that can be used as views or can be directly displayed by routing a path to it.

TODO Template tags

### ORM

Each database table can be accessed with a simple `ModelObject`. Every such table is defined as a PHP Class extending the `ModelObject` class with properties defined for the fields.

```php
<?php
require_once "../classes/ModelObject.php";

class Example extends ModelObject
{
    // When schema is not defined explicitly, it is automatically
    // deduced from the properties of the first object that is saved.

    public $example_data_string = "example";
    public $example_data_integer = 15;
    public $example_data_boolean = true;
}

?>
```

One can specify schema of the table explicitly without setting the properties like above. This is done as follows:

```php
<?php
require_once "../classes/ModelObject.php";

class Example extends ModelObject
{
    // Optionally, one may define the schema as follows.
    // Explicitly defining the schema has certain advantages:
    //
    // * One can specify further attributes for SQL fields like max_length
    // * One can set properties for an instance which are not fields in schema

    public function get_schema() {
        return array(
            array("example_data", "string"),
            array("example_data2", "string", "max_length"=>12)
        );
    }
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

Database migrations can be performed by storing files of names with syntax *&lt;table_name&gt;_&lt;version&gt;.sql* inside the *migrations* folder. Migrations are performed by checking current table version with all migration files for that table. Each new migration is then applied by running the sql stored.

TODO Running the migrations
