# init apiclient
```
$ApiClient = new DiCMSApiClient(
    array(
        'login' => 'admin',
        'api_key' => '585bf595d6616d4726db763ace3c87aa',
        'api_url' => 'http://www.testsite.ru/adm/api/'
    )
);
```
##getters
###get groups
```
$groups = $ApiClient->get("groups", array( start => 0 , limit =>10 ));
```
###get all users from group id 131
```
$users = $ApiClient->get("users", array( parent_oid => 131, start => 0 , limit =>10 ));
```
###get all user information
```
$users = $ApiClient->get("users/3", array( ));
```
###set user fields
You could set some user fields, for example 'carma' with user id 3
```
$users = $ApiClient->update("users/3", array( carma => 1000) );
```
###create new user
```
$data = array(
    login => 'unique@mail.ru'
    name => 'Дима',
    phone => '123234',
    email => 'asdasd'
);
$users = $ApiClient->create("users", $data );
```
