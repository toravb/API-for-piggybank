<?php
//получение информации о внесенных средствах по id копилки
function getMoney($user, $connect)
{
    $money = $connect->prepare("SELECT * FROM moneybox_info WHERE moneybox_id =:moneybox_id");
    $money->bindValue('moneybox_id', $user['moneybox_id']);
    $money->execute();
    $money = $money->fetchAll();

    if ($money){
        http_response_code(200);
        $res = [
            "status" => true,
            "message" => "Success",
            "data" => $money
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
//изменение баланса цели
function patchMoney($user, $connect)
{

    $patch = $connect->prepare("UPDATE moneybox_info SET deposit = deposit + :deposit WHERE moneybox_id =:moneybox_id");
    $patch->bindValue(':deposit', $user['deposit'], PDO::PARAM_STR);
    $patch->bindValue(':moneybox_id', $user['moneybox_id'], PDO::PARAM_STR);
    $patch = $patch->execute();

    if ($patch){
        http_response_code(200);

        $res = [
            "status" => true,
            "message" => "Success"
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