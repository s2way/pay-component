<?php

    require 'src/PayComponent.php';

    // TODO: testar retorno de internal server error

    $pay = new PayComponent();
    $pay->setAuthToken('token_floripa');
    
    // $_POST['token'] = 'HYcQ0MQ39fl8kn9OR7lFsTtxa+wNuM4lqQLUeN5SYZY=';
    // if ($pay->purchaseByToken($_POST)) {
    //     header("Location: {$pay->getRedirectURL()}");
    // } else {
    //     echo 'errroooooou<br>';
    //     var_dump($pay->getError());
    // }

    $_POST['id'] = rand();
    $_POST['return_url'] = 'http://www.terra.com.br';

    if ($pay->purchaseByCard($_POST)) {
        // var_dump($pay->getToken(), $pay->getRedirectURL());
        header("Location: {$pay->getRedirectURL()}");
    } else {
        echo 'errroooooou<br>';
        var_dump($pay->getError());
    }

