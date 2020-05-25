<?php

if (empty($token)){
    http_response_code(404);
    $res = [
        "status" => false,
        "message" => "empty token"
    ];
    echo json_encode($res);
    die();
}else {
    //получить информацию о копилках по id пользователя
    function getinfo($user, $connect, $token)
    {
        if (empty($token)) {
            http_response_code(404);
            $res = [
                "status" => false,
                "message" => "empty token"
            ];
            echo json_encode($res);
            die();
        }
        $moneybox = $connect->prepare("SELECT * FROM moneybox WHERE token =:token");
        $moneybox->bindValue(':token', $token, PDO::PARAM_STR);
        $moneybox->execute();
        $moneybox = $moneybox->fetchAll();

        $id = [];

        for ($i = 0; $i < count($moneybox); $i++) {
            $id[] = $moneybox[$i]['moneybox_id'];
        }

        $task = $connect->prepare("SELECT * FROM moneybox_info WHERE moneybox_id IN ('" . implode("','", $id) . "')");
        $task->execute();
        $task = $task->fetchAll();

        if ($moneybox == null) {
            http_response_code(404);
            $res = [
                "status" => false,
                "message" => "Moneybox not created"
            ];
            echo json_encode($res);
        } else {
            http_response_code(200);
            $res = [
                "status" => true,
                "message" => "Success",
                "data" => [
                    "moneybox" => $moneybox,
                    "task" => $task
                ]
            ];
            echo json_encode($res);
        }


    }

    //создание копилки
    function create($user, $connect, $token)
    {
        if ($token == null) {
            http_response_code(404);
            $res = [
                "status" => false,
                "message" => "empty token"
            ];
            echo json_encode($res);
            die();
        }

        $date = date('d.m.Y');
        $moneybox = $connect->prepare("INSERT INTO moneybox(token, moneybox_name, target, comment, amount, creation_date) VALUES (:token, :moneybox_name, :target, :comment, :amount, :creation_date)");
        $moneybox->bindValue(':token', $token, PDO::PARAM_STR);
        $moneybox->bindValue(':moneybox_name', $user['moneybox_name'], PDO::PARAM_STR);
        $moneybox->bindValue(':target', $user['target'], PDO::PARAM_STR);
        $moneybox->bindValue(':comment', $user['comment'], PDO::PARAM_STR);
        $moneybox->bindValue(':amount', $user['amount'], PDO::PARAM_STR);
        $moneybox->bindValue(':creation_date', $date, PDO::PARAM_STR);
        $moneybox = $moneybox->execute();

        if ($moneybox) {
            http_response_code(201);
            $res = [
                "status" => true,
                "message" => "Success"
            ];
            echo json_encode($res);
        } else {
            http_response_code(500);
            $res = [
                "status" => false,
                "message" => "Database Error"
            ];
            echo json_encode($res);
        }


    }

    //добавить task
    function addtask($user, $connect, $token)
    {
        $verify = $connect->prepare("SELECT moneybox_id FROM moneybox WHERE moneybox_id = :id AND token =:token");
        $verify->bindValue(':id', $user['moneybox_id'], PDO::PARAM_STR);
        $verify->bindValue(':token', $token, PDO::PARAM_STR);
        $verify->execute();
        $verify = $verify->fetch();

        if (empty($token)) {
            http_response_code(404);
            $res = [
                "status" => false,
                "message" => "empty token"
            ];
            echo json_encode($res);
            die();
        } elseif ($verify == null) {
            http_response_code(500);
            $res = [
                "status" => false,
                "message" => "Database error"
            ];
            echo json_encode($res);
            die();
        }

        function task($user, $connect, $i)
        {
            $task = $connect->prepare("INSERT INTO moneybox_info(moneybox_id, task, deposit) VALUES (:id, :task, :deposit)");
            $task->bindValue(':id', $user['moneybox_id'], PDO::PARAM_STR);
            $task->bindValue(':task', $user[$i]['task'], PDO::PARAM_STR);
            $task->bindValue(':deposit', $user[$i]['deposit'], PDO::PARAM_STR);
            $task = $task->execute();

            if ($task) {
                http_response_code(201);

                $res = [
                    "status" => true,
                    "message" => "Created"
                ];
                echo json_encode($res);
            } else {
                http_response_code(500);
                $res = [
                    "status" => false,
                    "message" => "Database error"
                ];
                echo json_encode($res);
            }

        }

        $count = count($user) - 1;

        for ($i = 0; $i < $count; $i++) {
            task($user, $connect, $i);
        }

    }

    //обновить копилку
    function update($user, $connect, $token)
    {
        $id = $_GET['id'];


        $validate = $connect->prepare("SELECT token FROM moneybox WHERE moneybox_id =:id");
        $validate->bindValue(':id', $id, PDO::PARAM_STR);
        $validate->execute();
        $validate = $validate->fetchAll();

        if (empty($validate)){
            http_response_code(404);
            $res = [
                "status" => false,
                "message" => "moneybox not created"
            ];
            echo json_encode($res);
            exit();
        }elseif ($validate[0]['token'] != $token){
            http_response_code(403);
            $res = [
                "status" => false,
                "message" => "wrong token"
            ];
            echo json_encode($res);
            exit();
        }

        if (isset($user['moneybox_name'])) {
            $update = $connect->prepare("UPDATE moneybox SET moneybox_name=:moneybox_name WHERE moneybox_id =:id");
            $update->bindValue(':moneybox_name', $user['moneybox_name'], PDO::PARAM_STR);
            $update->bindValue(':id', $id, PDO::PARAM_STR);
            $update = $update->execute();

            if ($update) {
                http_response_code(200);
                $res = [
                    "status" => true,
                    "message" => "moneybox_name updated"
                ];
                echo json_encode($res);
            } else {
                http_response_code(500);
                $res = [
                    "status" => false,
                    "message" => "Database Error"
                ];
                echo json_encode($res);
            }
        }
        if (isset($user['target'])) {
            $update = $connect->prepare("UPDATE moneybox SET target=:target WHERE moneybox_id =:id");
            $update->bindValue(':target', $user['target'], PDO::PARAM_STR);
            $update->bindValue(':id', $id, PDO::PARAM_STR);
            $update = $update->execute();

            if ($update) {
                http_response_code(200);
                $res = [
                    "status" => true,
                    "message" => "target updated"
                ];
                echo json_encode($res);
            } else {
                http_response_code(500);
                $res = [
                    "status" => false,
                    "message" => "Database Error"
                ];
                echo json_encode($res);
            }
        }
        if (isset($user['comment'])) {
            $update = $connect->prepare("UPDATE moneybox SET comment=:comment WHERE moneybox_id =:id");
            $update->bindValue(':comment', $user['comment'], PDO::PARAM_STR);
            $update->bindValue(':id', $id, PDO::PARAM_STR);
            $update = $update->execute();

            if ($update) {
                http_response_code(200);
                $res = [
                    "status" => true,
                    "message" => "comment updated"
                ];
                echo json_encode($res);
            } else {
                http_response_code(500);
                $res = [
                    "status" => false,
                    "message" => "Database Error"
                ];
                echo json_encode($res);
            }
        }
        if (isset($user['amount'])) {
            $update = $connect->prepare("UPDATE moneybox SET amount=:amount WHERE moneybox_id =:id");
            $update->bindValue(':amount', $user['amount'], PDO::PARAM_STR);
            $update->bindValue(':id', $id, PDO::PARAM_STR);
            $update = $update->execute();

            if ($update) {
                http_response_code(200);
                $res = [
                    "status" => true,
                    "message" => "amount updated"
                ];
                echo json_encode($res);
            } else {
                http_response_code(500);
                $res = [
                    "status" => false,
                    "message" => "Database Error"
                ];
                echo json_encode($res);
            }
        }

        if (isset($user['task'])) {
            $update = $connect->prepare("UPDATE moneybox_info SET task=:task WHERE info_id =:id");
            $update->bindValue(':task', $user['task'], PDO::PARAM_STR);
            $update->bindValue(':id', $user['info_id'], PDO::PARAM_STR);
            $update = $update->execute();

            if ($update) {
                http_response_code(200);
                $res = [
                    "status" => true,
                    "message" => "task updated"
                ];
                echo json_encode($res);
            } else {
                http_response_code(500);
                $res = [
                    "status" => false,
                    "message" => "Database Error"
                ];
                echo json_encode($res);
            }
        }
        if (isset($user['deposit'])) {
            $update = $connect->prepare("UPDATE moneybox_info SET deposit=:deposit WHERE info_id =:id");
            $update->bindValue(':deposit', $user['deposit'], PDO::PARAM_STR);
            $update->bindValue(':id', $user['info_id'], PDO::PARAM_STR);
            $update = $update->execute();

            if ($update) {
                http_response_code(200);
                $res = [
                    "status" => true,
                    "message" => "deposit updated"
                ];
                echo json_encode($res);
            } else {
                http_response_code(500);
                $res = [
                    "status" => false,
                    "message" => "Database Error"
                ];
                echo json_encode($res);
            }
        }
    }
}