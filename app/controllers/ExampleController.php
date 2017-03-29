<?php

require_once "app/core/session/Auth.php";
require_once "app/models/User.php";
require_once "app/forms/ExampleForm.php";

class ExampleController extends Controller
{
    public function get_create_user($username, $password) {
        $user = new User();
        $user->username = $username;
        Auth::set_password($user, $password);
        $user->save();
        return new View("Created user $username succesfully !");
    }

    public function get_logout() {
        Auth::logout();
        return new View("You have logged out successfully !");
    }

    public function get_test($username=null, $password=null) {
        $data = array(
            "message" => "hello world!",
        );

        if ($username && $password) {
            $user = User::query()->where("username=?", $username)->first();

            if ($user && Auth::authenticate($user, $password)) {
                $data["message"] = "Successful login. Hello $username !";
            }
            else {
                $data["message"] = "Invalid username and/or password !";
            }
        }
        else {
            $user = Auth::get_user("User");
            if ($user) {
                $data["message"] = "Hello " . $user->username . " !";
                $data["message"] .= "\n". $user->created_at->format("r");
            }
        }

        return new TemplateView('example.html', $data);
    }

    public function get_form() {
        $data = array();
        $data['form'] = new ExampleForm();
        return new TemplateView('example_form.html', $data);
    }

    public function get() {
        $data = array(
            "message" => "Welcome to the land of awesomeness !"
        );
        return new TemplateView('example.html', $data);
    }
}

?>
