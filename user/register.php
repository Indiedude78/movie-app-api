<?php
//set headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
HEADER("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
//listen for post data


//check if request method is post
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //get data from post
    $data = json_decode(file_get_contents("php://input"));
    //sanitize data
    $fname = null;
    $lname = null;
    $email = null;
    $username = null;
    $password = null;
    $confirm_password = null;
    $e = null;
    if (isset($data->firstname)) {
        $fname = htmlspecialchars(strip_tags($data->firstname));
    }
    if (isset($data->lastname)) {
        $lname = htmlspecialchars(strip_tags($data->lastname));
    }
    if (isset($data->email)) {
        $email = htmlspecialchars(strip_tags($data->email));
    }
    if (isset($data->username)) {
        $username = htmlspecialchars(strip_tags($data->username));
    }
    if (isset($data->password)) {
        $password = htmlspecialchars(strip_tags($data->password));
    }
    if (isset($data->confirm_password)) {
        $confirm_password = htmlspecialchars(strip_tags($data->confirm_password));
    }
    //check if all fields are set
    $isValid = true;

    //check if password and confirm password match
    if ($password != $confirm_password) {
        $isValid = false;
    }

    //check if email is valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $isValid = false;
    }
    //check if all fields are set
    if ($fname == null || $lname == null || $email == null || $username == null || $password == null || $confirm_password == null) {
        $isValid = false;
    }


    if ($isValid) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        require_once(__DIR__ . "/../lib/db.php");
        $db = getDB();
        $query = "INSERT INTO Users (fname, lname, email, username, `password`) ";
        $query .= "VALUES (:fname, :lname, :email, :username, :password)";
        $stmt = $db->prepare($query);
        $params = array(":fname" => $fname, ":lname" => $lname, ":email" => $email, ":username" => $username, ":password" => $hashed_password);
        $r = $stmt->execute($params);
        $e = $stmt->errorInfo();
        if ($e[0] != "00000") {
            echo json_encode(array(
                "header" => array("status" => 400),
                "body" => array(
                    "error" => "There was an error creating your account. Please try again."
                )
            ));
        } elseif ($e[0] == "23000") {
            echo json_encode(array(
                "header" => array("status" => 400),
                "body" => array(
                    "error" => "There was an error creating your account. Please try again."
                )
            ));
        } else {
            echo json_encode(array(
                "header" => array("status" => 200),
                "body" => array(
                    "success" => "Account created successfully."
                )
            ));
        }
    } else {
        echo json_encode(array(
            "header" => array("status" => 400),
            "body" => array(
                "error" => "An error occurred. Please try again."
            )
        ));
    }
}
