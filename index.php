<?php
require_once('apiclient.inc.php');

$ApiClient = new DiCMSApiClient(
    array(
        'login' => 'admin',
        'api_key' => '585bf595d6616d4726db763ace3c87aa',
        'api_url' => 'http://www.testsite.ru/adm/api/'
    )
);

$data = array(
    firstname => 'Дима',
    phone => '123234',
    s_phone => '+7 123234',
    s_address => 'asdasd'
);


try {
    $users = $ApiClient->get("users", array( parent_oid=>131,  start => 0 , limit =>10 ));
    print_r($users);
} catch (Exception $e) {
    print $e->getMessage();
}

?>

