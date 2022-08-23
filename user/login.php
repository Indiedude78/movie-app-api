<?php
//include necessary files
require_once('../lib/secret_key.php');
require_once('../vendor/autoload.php');

use \Firebase\JWT\JWT;

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
    $username = null;
    $password = null;

    if (isset($data->username)) {
        $username = htmlspecialchars(strip_tags($data->username));
    }
    if (isset($data->password)) {
        $password = htmlspecialchars(strip_tags($data->password));
    }
    //check if all fields are set
    $isValid = true;

    //check if all fields are set
    if ($username == null || $password == null) {
        $isValid = false;
    }


    if ($isValid) {
        require_once(__DIR__ . "/../lib/db.php");
        $db = getDB();
        $query = "SELECT * FROM Users WHERE username = :username";
        $stmt = $db->prepare($query);
        $params = array(
            ":username" => $username
        );
        $stmt->execute($params);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $hash = $user["password"];
            if (password_verify($password, $hash)) {
                unset($user["password"]);
                unset($hash);
                $secret_key = $key;
                $issuer_claim = "dev-server"; //this can be the domain name of the server
                $audience_claim = "app";
                $issuedat_claim = time(); // issued at
                $notbefore_claim = $issuedat_claim; //not before in seconds
                $expire_claim = $issuedat_claim + (7 * 24 * 60 * 60); //expire time in seconds
                $data = array(
                    "iss" => $issuer_claim,
                    "aud" => $audience_claim,
                    "iat" => $issuedat_claim,
                    "nbf" => $notbefore_claim,
                    "exp" => $expire_claim,
                    "body" => array(
                        "id" => $user["id"],
                        "firstname" => $user["fname"],
                        "lastname" => $user["lname"],
                        "email" => $user["email"],
                        "username" => $user["username"]
                    )
                );
                $jwt = JWT::encode($data, $secret_key, "HS256");
                $json = json_encode(array(
                    "message" => "Successful login.",
                    "jwt" => $jwt
                ));
                http_response_code(200);
                echo $json;
            } else {
                http_response_code(401);
                echo json_encode(array(
                    "error" => "Invalid username or password"
                ));
            }
        } else {
            http_response_code(401);
            echo json_encode(array(
                "error" => "Invalid username or password"
            ));
        }
    } else {
        http_response_code(401);
        echo json_encode(array(
            "error" => "Invalid username or password"
        ));
    }
}
