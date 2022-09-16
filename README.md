# CakePHP_JWT

CakePHP_JWT is a Plugin for CakePHP 4 that allows you to authenticate users by JSON Web Tokens

## Installation

Use the package manager [composer](https://getcomposer.org) to install CakePHP_JWT.

```bash
composer require wirecore/cakephp_jwt
```

## Usage

First you need to load the plugin in the Application.php of your project.
```php
$this->addPlugin('Wirecore/CakePHP_JWT');
```
After the plugin was loaded you need to load the JWT component of the plugin.
```php
$this->loadComponent("Wirecore/CakePHP_JWT.Jwt");
```
This was all what you need. All actions are now authentication protected.

Optional you can set some component configuration.
```php
$this->loadComponent("Wirecore/CakePHP_JWT.Jwt", [
    'tokenExpiration' => 900, // default is 900 seconds
    'headerParam' => 'Authorization', // default is Authorization
    'usersTable' => 'Users', // default is Users
    'unauthorizedExceptionText' => 'You are not authorized to access that location', // default is You are not authorized to access that location
    'encryptionKey' => '', // default is used the salt of your application
    'refreshTokenName' => 'refresh_token', // default is refresh_token
    'refreshTokenSecure' => false, // default is false
    'refreshTokenHttpOnly' => true, // default is true
    'hostAddPort' => false // if by generation the refresh token server path is not available, it used the host server variable. by enabling this option it add the current available port to the host 
]);
```

### Methods

To allow actions unauthenticated use this:
```php
$this->Jwt->allowUnauthenticated(['index']);
```

The Plugin matches automaticly a user by the transferd JWT Token. To get this User use:
```php
$this->Jwt->getIdentity();
```

To generate a new access token use this:
```php
$this->Jwt->generateAccessToken($userId);
```

To generate a new refresh token use this:
```php
$this->Jwt->generateRefreshToken($userId);
```

To set the refresh token cookie use this. The method generate a new refresh token.
```php
$this->Jwt->setRefreshTokenCookie($userId);
```

To refresh access and refresh token use this method:
```php
$this->Jwt->refreshTokens();
```

## Best Practise

Here is a example of a AuthController that you can use. You must change a bit things but the basic concept should be the same

```php
public function initialize():void{
    parent::initialize();
    $this->Jwt->allowUnauthenticated(['login', 'refreshToken']);
}

public function login(){

    $response = $this->getResponse();
    $data = $this->request->getData();

    // <-- checking user password here

    $userId = 123; // for exmaple here is userId 123

    // password correct
    $token = $this->Jwt->generateAccessToken($userId); // access token for 15 minute authentication
    $this->Jwt->setRefreshTokenCookie($userId); // refresh token for refreshing the access token

    $response = $response->withStatus(200);

    $this->set('token', $token);
    $this->viewBuilder()->setOption('serialize', 'token');
    $this->viewBuilder()->setClassName('Json');
    $this->setResponse($response);

}

public function refreshToken(){

    $response = $this->getResponse();

    // <-- checking user password here

    $token = $this->Jwt->refreshTokens(); // generate a new access token for 15 minutes and actualize the refresh token cookie

    $response = $response->withStatus(200);

    $this->set('token', $token);
    $this->viewBuilder()->setOption('serialize', 'token');
    $this->viewBuilder()->setClassName('Json');
    $this->setResponse($response);

}
```

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## Development Setup

In the root folder you find a docker-compose file what you can use to develop the plugin. Follow the introduction at the installation section, and change the following lines in the composer.json of the cakephp installation.
```php
"autoload": {
    "psr-4": {
        "App\\": "src/",
        "Wirecore\\CakePHP_JWT\\": "plugins/Wirecore/CakePHP_JWT/src/",
        "Wirecore\\CakePHP_JWT\\Test\\": "plugins/Wirecore/CakePHP_JWT/tests/"
    }
},
```
After adding the previous lines you need to run in the php-fpm container.
```php
composer dumpautoload
```

In addition you find a .devcontainer folder in the project that is recommended for development in VS Code.

## License
[MIT](https://choosealicense.com/licenses/mit/)