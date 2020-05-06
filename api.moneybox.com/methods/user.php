<?php
//логин пользователя
function login($user, $connect, $token)
{
    $login = $connect->prepare("SELECT * FROM users WHERE login =:login");
    $login->bindValue(':login', $user['login'], PDO::PARAM_STR);
    $login->execute();
    $login = $login->fetch();

    $password = password_verify($user['password'], $login['password']);

    if (empty($user['login']) or empty($user['password'])){

        http_response_code(400);
        $res = [
            "status" => false,
            "message" => "Empty data"
        ];
        echo json_encode($res);

    } elseif ($login == null){
        http_response_code(404);
        $res = [
            "status" => false,
            "message" => "User not found"
        ];
        echo json_encode($res);

    }elseif (!$password){

        http_response_code(400);
        $res = [
            "status" => false,
            "message" => "Wrong password"
        ];
        echo json_encode($res);

    }else{
        $login['password'] = $user['password'];
        http_response_code(200);
        $res = [
            "status" => true,
            "message" => "Success",
            "data" => $login
        ];
        echo json_encode($res);
    }
}
//регистрация пользователя
function register($user, $connect, $token)
{
    $register = $connect->prepare("SELECT login, email FROM users WHERE login =:login OR email =:email");
    $register->bindValue(':login', $user['login'], PDO::PARAM_STR);
    $register->bindValue(':email', $user['email'], PDO::PARAM_STR);
    $register->execute();
    $register = $register->fetchAll();

    $validate_email = filter_var($user['email'], FILTER_VALIDATE_EMAIL);

    //проверка логина или email, ну существование, если проверки пройдены - регистрация
    if (empty($user['login']) or empty($user['password']) or empty($user['email']) or empty($user['name']) or empty($user['surname'])) {
        http_response_code(400);
        $res = [
            "status" => false,
            "message" => "Empty data"
        ];
        echo json_encode($res);

    } elseif ($validate_email == false) {
        http_response_code(400);
        $res = [
            "status" => false,
            "message" => "Wrong email"
        ];
        echo json_encode($res);

    } elseif ($register[0]['login'] == $user['login'] or $register[1]['login'] == $user['login'] or $register['login'] == $user['login']) {
        http_response_code(400);
        $res = [
            "status" => false,
            "message" => "User with username already exists"
        ];
        echo json_encode($res);
    } elseif ($register[0]['email'] == $user['email'] or $register[1]['email'] == $user['email'] or $register['email'] == $user['email']) {
        http_response_code(400);
        $res = [
            "status" => false,
            "message" => "User with the email address provided already exists"
        ];
        echo json_encode($res);

    } else {
        $password = password_hash($user['password'], PASSWORD_DEFAULT);

        do{
            $tokenGen = random_bytes(16);
            $token = bin2hex($tokenGen);

            $data = $connect->prepare("SELECT token FROM users WHERE token =:token");
            $data->bindValue(':token', $token, PDO::PARAM_STR);
            $data->execute();
            $data = $data->fetch();
            
        }while($data != null);

        $register = $connect->prepare("INSERT INTO users (`login`, `password`, `email`, `name`, `surname`, `token`) VALUES (:login, :password, :email, :first_name, :surname, :token)");
        $register->bindValue(':login', $user['login'], PDO::PARAM_STR);
        $register->bindValue(':password', $password, PDO::PARAM_STR);
        $register->bindValue(':email', $user['email'], PDO::PARAM_STR);
        $register->bindValue(':first_name', $user['name'], PDO::PARAM_STR);
        $register->bindValue(':surname', $user['surname'], PDO::PARAM_STR);
        $register->bindValue(':token', $token, PDO::PARAM_STR);
        $register = $register->execute();
        if (!$register) {
            http_response_code(500);
            $res = [
                "status" => false,
                "message" => "Database Error"
            ];
            echo json_encode($res);
        } else {
            http_response_code(201);
            $res = [
                "status" => true,
                "message" => "Success",
            ];
            echo json_encode($res);
        }
    }
}
//смена email адреса и пароля
function update($user, $connect, $token)
{
    if (empty($token)){
        http_response_code(404);
        $res = [
            "status" => false,
            "message" => "empty token"
        ];
        echo json_encode($res);
        die();
    }
    if (isset($user['email'])) {
        $validate_email = filter_var($user['email'], FILTER_VALIDATE_EMAIL);

        if (empty($user['email'])){
            http_response_code(400);
            $res = [
                "status" => false,
                "message" => "Empty data"
            ];
            exit(json_encode($res));
        }elseif ($validate_email == false){
            http_response_code(400);
            $res = [
                "status" => false,
                "message" => "Wrong email"
            ];
            exit(json_encode($res));
        }

        $email = $connect->prepare("SELECT email FROM users WHERE email =:email");
        $email->bindValue(':email', $user['email'], PDO::PARAM_STR);
        $email->execute();
        $email = $email->fetch();
    //проверка нового email на существование в бд
        if ($email == null) {
            $email = $connect->prepare("UPDATE users SET email =:email WHERE token =:token");
            $email->bindValue(':email', $user['email'], PDO::PARAM_STR);
            $email->bindValue(':token', $token, PDO::PARAM_STR);
            $email->execute();

            http_response_code(200);
            $res = [
                "status" => true,
                "Message" => "Email has been changed"
            ];
            echo json_encode($res);
        } else {
            http_response_code(400);
            $res = [
                "status" => false,
                "message" => "User with the email address provided already exists"
            ];
            echo json_encode($res);
        }
    }
    if (isset($user['password'])){
        if (empty($user['password'])){
            http_response_code(400);
            $res = [
                "status" => false,
                "message" => "Empty data"
            ];
            exit(json_encode($res));
        }

        $pass = password_hash($user['password'], PASSWORD_DEFAULT);

        $password = $connect->prepare("UPDATE users SET password =:password WHERE token =:token");
        $password->bindValue(':password', $pass, PDO::PARAM_STR);
        $password->bindValue(':token', $token, PDO::PARAM_STR);
        $password = $password->execute();

        if($password){
            http_response_code(200);
            $res = [
                "status" => true,
                "message" => "Password has been changed"
            ];
            echo json_encode($res);
        }else{
            http_response_code(500);
            $res = [
                "status" => false,
                "message" => "Database error"
            ];
            echo json_encode($res);
        }
    }

}
