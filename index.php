<?php
require_once('apiclient.inc.php');

$ApiClient = new DiCMSApiClient(
    array(
        'login' => 'admin',
        'api_key' => 'API_KEY',
        'api_url' => 'http://www.site.com/adm/api/'
    )
);

$data = array(
    firstname => 'Dmitry',
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

