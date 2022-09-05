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
        $db = getDB();
        $query = "SELECT * FROM Movies JOIN Movie_details ON Movies.id = Movie_details.movie_id WHERE Movies.imdb_id = :imdb_id";
        $stmt = $db->prepare($query);
        $params = array(":imdb_id" => $search_imdb);
        $r = $stmt->execute($params);
        $results = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($results) {
            http_response_code(200);
            $response = array(
                "status" => "success",
                "data" => $results
            );
            echo json_encode($response);
        } else {
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
            if ($movie_info) {
                $movie_info = json_decode($movie_info);
                $maturity_rating = $movie_info->Rated;
                $runtime = $movie_info->Runtime;
                $genre = $movie_info->Genre;
                $director = $movie_info->Director;
                $writer = $movie_info->Writer;
                $actors = $movie_info->Actors;
                $plot = $movie_info->Plot;
                $language = $movie_info->Language;
                $country = $movie_info->Country;
                $awards = $movie_info->Awards;
                $metascore = $movie_info->Metascore;
                $imdb_rating = $movie_info->imdbRating;
                $box_office = $movie_info->BoxOffice;

                $db = getDB();
                $query = "INSERT INTO Movie_details (movie_id, imdb_id, maturity_rating, runtime, genre, director, writer, actors, plot, language, country, awards, metascore, imdb_rating, box_office) VALUES ((SELECT id FROM Movies WHERE imdb_id = :imdb_id), :imdb_id, :maturity_rating, :runtime, :genre, :director, :writer, :actors, :plot, :language, :country, :awards, :metascore, :imdb_rating, :box_office)";
                $stmt = $db->prepare($query);
                $params = array(":imdb_id" => $search_imdb, ":maturity_rating" => $maturity_rating, ":runtime" => $runtime, ":genre" => $genre, ":director" => $director, ":writer" => $writer, ":actors" => $actors, ":plot" => $plot, ":language" => $language, ":country" => $country, ":awards" => $awards, ":metascore" => $metascore, ":imdb_rating" => $imdb_rating, ":box_office" => $box_office);
                $r = $stmt->execute($params);
                $movie_id = $db->lastInsertId();
                if ($r) {
                    $query = "SELECT * FROM Movies JOIN Movie_details ON Movies.id = Movie_details.movie_id WHERE Movies.imdb_id = :imdb_id";
                    $stmt = $db->prepare($query);
                    $params = array(":imdb_id" => $search_imdb);
                    $r = $stmt->execute($params);
                    $results = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($results) {
                        http_response_code(200);
                        $response = array(
                            "status" => "success",
                            "data" => $results
                        );
                        echo json_encode($response);
                        exit();
                    } else {
                        http_response_code(404);
                        $response = array(
                            "status" => "error",
                            "message" => "Movie not found"
                        );
                        echo json_encode($response);
                    }
                } else {
                    http_response_code(500);
                    $response = array(
                        "status" => "error",
                        "message" => "Error finding movie"
                    );
                    echo json_encode($response);
                }
            }
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
