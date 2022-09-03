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
        $search_request = $data->movie;
        if ($user_id) {
            $db = getDB();
            $query = "SELECT * FROM Movies WHERE title LIKE :search_request";
            $stmt = $db->prepare($query);
            $params = array(
                ":search_request" => '%' . $search_request . '%'
            );
            $stmt->execute($params);
            $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($movies && count($movies) > 0) {
                http_response_code(200);
                $response = array(
                    "status" => "success",
                    "type" => "db",
                    "data" => array(
                        "user_id" => $user_id,
                        "movies" => $movies
                    )
                );

                echo json_encode($response);
            } else {
                $curl = curl_init();
                $search_request = str_replace(' ', '%20', $search_request);
                curl_setopt_array($curl, [
                    CURLOPT_URL => "https://movie-database-alternative.p.rapidapi.com/?s=" . $search_request . "&r=json&page=1",
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
                $api_res = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);
                $movie_data = json_decode($api_res);
                if ($api_res) {
                    foreach ($movie_data->Search as $movie) {
                        $query = "INSERT INTO Movies (imdb_id, title, release_year, mtype, poster) VALUES (:imdb_id, :title, :release_year, :mtype, :poster)";
                        $stmt = $db->prepare($query);
                        $params = array(
                            ":imdb_id" => $movie->imdbID,
                            ":title" => $movie->Title,
                            ":release_year" => $movie->Year,
                            ":mtype" => substr($movie->Type, 0, 1),
                            ":poster" => $movie->Poster
                        );
                        $stmt->execute($params);
                        $e = $stmt->errorInfo();
                    }
                    if ($e[0] != "00000") {
                        http_response_code(500);
                        echo json_encode(array(
                            "status" => "error",
                            "message" => "Error inserting movie into database.",
                            "error" => $e[2]
                        ));
                    } else {
                        http_response_code(200);
                        $response = array(
                            "status" => "success",
                            "type" => "api",
                            "data" => array(
                                "user_id" => $user_id,
                                "movies" => $movie_data->Search
                            )
                        );
                        echo json_encode($response);
                    }
                } else {
                    http_response_code(500);
                    echo json_encode(array(
                        "status" => "error",
                        "message" => "Server error.",
                        "error" => $err
                    ));
                }
            }
        } else {
            http_response_code(401);
            echo json_encode(array(
                "status" => "error",
                "message" => "Access denied.",
                "error" => "No token provided.",
            ));
        }
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(array(
            "status" => "error",
            "message" => "Access denied.",
            "error" => $e->getMessage()
        ));
        $isValid = false;
    }
}
