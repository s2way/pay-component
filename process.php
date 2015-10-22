<?php

    require 'src/PayComponent.php';

    $pay = new PayComponent();
    $pay->setAuthToken('token_floripa');
    if ($pay->purchaseByCard($_POST)) {
        die(var_dump($pay->getToken(), $pay->getAuthenticationURL()));
        header("Location: {$pay->getAuthenticationURL()}");
    } else {
        echo 'errroooooou';
        die(var_dump($pay->getError()));
    }
