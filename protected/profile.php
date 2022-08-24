<?php

//set headers
require_once(__DIR__ . "/../lib/headers.php");
require_once(__DIR__ . "/../lib/db.php");
require_once(__DIR__ . "/../lib/secret_key.php");
require_once(__DIR__ . "/../vendor/autoload.php");

use \Firebase\JWT\JWT;
use Firebase\JWT\Key;

error_reporting(E_ERROR);

//define key
$secret_key = $key;
$data = json_decode(file_get_contents("php://input"));
$jwt = null;
$jwt = isset($data->token) ? $data->token : null;
if ($jwt) {
    try {
        $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
        $user_id = $decoded->data->id;
        $firstname = $decoded->data->firstname;
        $lastname = $decoded->data->lastname;
        $username = $decoded->data->username;
        $email = $decoded->data->email;
        $isValid = true;
        $response = array(
            "status" => "success",
            "data" => $decoded->body
        );
        http_response_code(200);
        echo json_encode($response);
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(array(
            "message" => "Access denied.",
            "error" => $e->getMessage()
        ));
        $isValid = false;
    }
} else {
    http_response_code(401);
    echo json_encode(array(
        "message" => "Access denied.",
        "error" => "No token provided.",
    ));
    $isValid = false;
}
