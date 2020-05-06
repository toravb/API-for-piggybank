<?php
//получаю method, action, метод запроса
$method = explode('/', $_GET['q']);
$type = $_SERVER['REQUEST_METHOD'];
$action = $method[1];
$methodPath = ROOT.'/methods/'.$method[0].'.php';

//декодирую входные данные
$data = file_get_contents('php://input');
$user = json_decode($data, true);

//header
$token = apache_request_headers();
$token = $token['PIGGY-BANK-TOKEN'];

//если method и action существуют - вызываю их
if ($type == 'OPTIONS'){
    http_response_code(204);
}else {
    if (file_exists($methodPath)) {
        require_once($methodPath);
    } else {
        http_response_code(404);
        echo 'Method ' . $method[0] . ' not found';
    }

    if ($action == null) {
        http_response_code(404);
        echo 'Action is null';
    } elseif (function_exists($action)) {
        $action($user, $connect, $token);
    } else {
        http_response_code(404);
        echo 'Action ' . $action . ' not found';
    }
}
