<?php

$connect = new PDO('mysql:host=127.0.0.1;dbname=piggybank', 'root', 'root');
$connect->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);