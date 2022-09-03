<?php
require_once(__DIR__ . "/../lib/headers.php");
require_once(__DIR__ . "/../lib/db.php");
require_once(__DIR__ . "/../lib/secret_key.php");
require_once(__DIR__ . "/../lib/secret_api_key.php");
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
        $search_imdb = $data->imdb_id;
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://movie-database-alternative.p.rapidapi.com/?r=json&i=" . $search_imdb,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "X-RapidAPI-Host: movie-database-alternative.p.rapidapi.com",
                "X-RapidAPI-Key: $api_key"
            ],
        ]);

        $movie_info = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $response = array(
                "status" => "error",
                "type" => "api",
                "data" => array(
                    "user_id" => $user_id,
                    "error" => $err
                )
            );
            http_response_code(500);
            echo json_encode($response);
        } else {

            $response = array(
                "status" => "success",
                "type" => "api",
                "data" => array(
                    "user_id" => $user_id,
                    "movie_info" => json_decode($movie_info)
                )
            );
            http_response_code(200);
            echo json_encode($response);
        }
    } catch (Exception $e) {
        http_response_code(401);
        $response = array(
            "status" => "error",
            "type" => "jwt",
            "message" => "Access denied.",
            "data" => array(
                "error" => $e->getMessage()
            )
        );
        echo json_encode($response);
        exit();
    }
}
