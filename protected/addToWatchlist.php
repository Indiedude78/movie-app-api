<?php
require_once(__DIR__ . "/../lib/headers.php");
require_once(__DIR__ . "/../lib/db.php");
require_once(__DIR__ . "/../lib/secret_key.php");
require_once(__DIR__ . "/../vendor/autoload.php");


use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

error_reporting(E_ERROR);

//define key
$secret_key = $key;
$data = json_decode(file_get_contents("php://input"));
$jwt = null;

$jwt = isset($data->token) ? $data->token : null;
if ($jwt) {
    try {
        $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
        $user_id = $decoded->body->id;
        //Continue Here
    } catch (Exception $e) {
        http_response_code(401);
        $response = array(
            "status" => "error",
            "message" => "Access denied.",
            "data" => array(
                "error" => $e->getMessage()
            )
        );
        echo json_encode($response);
    }
}
