<?php

$connect = new PDO('mysql:host=127.0.0.1;dbname=test', 'test', 'test');
$connect->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);