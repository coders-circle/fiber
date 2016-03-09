# fiber

**fiber** is a simple, lightweight php framework based on MVC pattern. *fiber* is built for helping development of php projects, when heavy frameworks are not desired.

## User Guide

### Router

All routes are defined as rules in the `Router` class inside the *classes/Router.php* file. A rule defines the controller or the template that handles a path.

Each rule is of the form:

```php
// type can be 'controller' or 'template'
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

Here the path `/fiber/` is routed to the template file *views/fiber.html* and the path `/example/` is handled by the controller `ExampleController` in the file *controls/ExampleController.php*. The default rule, for when no path is
provided in the url, is the "fiber" template rule.

Templates are just HTML pages that may include some template tags for server side processing. For simple pages that has no need to access database or perform any complicated processing, one can simply route the url directly to a template.

Controllers perform some processing, optionally accessing the database, and pass the processed data to a corresponding view. The view is then responsible for presenting text or HTML output to the user. A view is mostly built from a template.

### Controllers and Views

Every controller is held in a class derived from the `Controller` base class and is stored inside the *controls* directory. A controller returns a `View` object which acts as a renderer, that returns what is to be presented to the user.

A view can be constructed directly from some text that you want to present or can be constructed from a template, which defines the HTML page to be presented. When creating from template, you can pass data to the view, which data you can
use in the template. The `TemplateView` processes template which includes replacing the data passed from the controller and
returns the final result.

An example controller is:

```php
<?php

class ExampleController extends Controller
{
    public function get()
    {
        return new TemplateView('example.html');
        // return new TemplateView('example.html', array("my_data"=>"my_value"));
        // return new View("Some text that is displayed as it is");
    }
}

?>
```

A controller class can have different methods that are called by the framework to handle different url requests.

* `get()` : handles GET requests
* `post()` : handles POST requests
* in general *`method()`* handles HTTP request of type *method*
* `get_foo()` : handles GET requests for urls of form *&lt;controller&gt;/foo/*
* `get_foo($x, $y)` : handles GET requests for urls of form *&lt;controller&gt;/foo/x/y/* where `x` and `y` are again passed as parameters to the method
* `post_foo()` : similar to `get_foo()` but handles POST requests
* ...

### Templates

Templates are basic text pages, mostly HTML documents, that can be used as views or can be directly displayed by routing a path to it.

TODO Template tags

### Models

Each database table can be accessed with a simple `Model`, where the schema is defined using array of properties. Each property is an array defining the name of the field, its type and other attributes like max_length.

```php
<?php
require_once "../classes/Model.php";

class Example extends Model
{
    public function get_schema() {
        // schema is array of properties
        // each property being the tuple: (field-name, type, [attribute,...])

        return array(
            array("example_data", "string", "max_length"=>12),
            array("example_data2", "string", "extra"=>"UNIQUE"),
            array("example_data3", "integer")
            array("example_data4", "datetime")
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
$test->example_data4 = new DateTime();
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

### Migrations

Every time, you create a model and define its schema, and every time you change schema of any model, you should *migrate* the changes for them to be reflected in your database.

Each migration is defined by the sql statements that perform the required changes to the database. The migration is stored in a file *&lt;table_name&gt;_&lt;version&gt;.sql* inside the *migrations* directory. The version of each schema is kept tracked by a special table *schema_versions* in the database. This way, any new migration can be detected and applied whenever required. Note that you do not need to create these migration files yourself, though you can if you need to.

Two utilities `makemigration` and `migrate`, which are basically php scripts, are available for migration purposes. The `makemigration` command automatically detects any new changes in the schema definition and generates a migration file with naming convention mentioned above. The `migrate` command then apply any new migrations present for a model, effectively changing the database.

```bash
php makemigration MyModel
php migrate MyModel
```


### Authentication and Session

*fiber* provides some utility functions for basic session and authentication handling.

`Session` class provides static functions for simple session data handling.

```php
Session::store("key", "value");
Session::remove("key");
$value = Session::get("key");
Session::reset();
Session::destroy();
```

`Auth` class provides authentication related functions, again all static, and uses the `Session` class for remembering logged-in users across pages. For this, it stores the user's id in the session variable *user*.

To work with these functions, you must have some `User` model class, which extends from `Model` and has `id : integer` and `password : string(max_length >= 255)` fields. Note that the password field stores a hash value concatenated with other info including salt and hashing method used, as returned by the php `password_hash()` method.

```php
// Assuming you have a User model class with id and password fields

// Set password field of the user object, automatically performing the hashing
Auth::set_password($user, $password);

// Verify password for the user object, checking for valid hash
if (Auth::verify_user($user, $password)) { /*...*/ }

// Login with the user object for the current session
Auth::login($user);

// Logout from the current session
Auth::logout();

// Get currently logged-in user object, null if no-one is logged in
// Note that the 'User' is your custom model class name
$user = Auth::get_user("User");

// Verify password for the user object and login when verified
if (Auth::authenticate($user, $password)) { /*...*/ }
```
