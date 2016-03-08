<?php

require_once "../classes/Auth.php";
require_once "../models/User.php";

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

    public function get_test($username=null, $password=null)
    {
        $data = array(
            "message" => "hello world!"
        );

        if ($username && $password) {
            $user = User::query()->where("username=?", $username)->first();

            if (Auth::authenticate($user, $password)) {
                $data["message"] = "Successful login. Hello $username !";
            }
            else {
                $data["message"] = "Invalid username and/or password !";
            }
        }
        else {
            $user = Auth::get_user("User");
            if ($user)
                $data["message"] = "Hello " . $user->username . " !";
        }

        return new TemplateView('example.html', $data);
    }

    public function get()
    {
        $data = array(
            "message" => "hello world!"
        );
        return new TemplateView('example.html', $data);
    }
}

?>
