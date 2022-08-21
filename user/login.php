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
                $data = array(
                    "header" => array("status" => 200),
                    "body" => array(
                        "id" => $user["id"],
                        "firstname" => $user["fname"],
                        "lastname" => $user["lname"],
                        "email" => $user["email"],
                        "username" => $user["username"]
                    )
                );
                $json = json_encode($data);
                echo $json;
            } else {
                echo json_encode(array(
                    "header" => array("status" => 400),
                    "body" => array(
                        "error" => "Invalid username or password"
                    )
                ));
            }
        } else {
            echo json_encode(array(
                "header" => array("status" => 400),
                "body" => array(
                    "error" => "Invalid username or password"
                )
            ));
        }
    } else {
        echo json_encode(array(
            "header" => array("status" => 400),
            "body" => array(
                "error" => "An error has occurred"
            )
        ));
    }
}
