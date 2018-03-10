# init apiclient
```
use ShopExpress\ApiClient\ApiClient;
use ShopExpress\ApiClient\Response\ApiResponse;
$ApiClient = new ApiClient(
    array(
        'login' => 'admin',
        'api_key' => 'API_KEY',
        'api_url' => 'http://www.site.com/adm/api/'
    )
);
```
## getters
### get groups
```
$groups = $ApiClient->get("groups", array( start => 0 , limit =>10 ));
```
### get all users from group id 131
```
$users = $ApiClient->get("users",
    [
        parent_oid => 131,
        start => 0,
        limit =>10
    ]
);
```
### get all user information
```
$users = $ApiClient->get("users/3", array( ));
```
### set user fields
You could set some user fields, for example 'carma' with user id 3
```
$users = $ApiClient->update(
    "users/3", [ 'content' => [ 'carma' => 1000 ] ]
);
```
### create new user
```
$data = array(
    login => 'unique@mail.ru'
    name => 'Дима',
    phone => '123234',
    email => 'asdasd'
);
$users = $ApiClient->create("users", $data );
```
### create new order
```
$order = $siteApi->create(
    'orders',
    [
        'email' => 'test@test',
        'phone' => '',

        'currency' => 'RUB',

        'comment' => 'Комментарий к заказу',

        'summ' => 100,
        'paid' => 0,

        'pay_method' => 'BALANCE',
        'pay_status' => 'S',

        'master_oid' => 3,
    ]
);
```
### update exists order
```
$order = $siteApi->update(
    'orders/10',
    [
        'pay_status' => 'FP',
        'products' => [
            [
                'oid' => 1117,
                'count' => 1,
                'params' => ['color' => 0]
            ],
            [
                'oid' => 1117,
                'count' => 1
            ],
        ],
    ]
);
```
