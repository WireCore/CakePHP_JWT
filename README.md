# CakePHP_JWT

CakePHP_JWT is a Plugin for CakePHP 4 that allows you to authenticate users by JSON Web Tokens

## Installation

Use the package manager [composer](https://getcomposer.org) to install CakePHP_JWT.

```bash
composer require Wirecore/CakePHP_JWT
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

### Methods

To allow actions unauthenticated use this:
```php
$this->Jwt->allowUnauthenticated(['index']);
```

The Plugin matches automaticly a user by the transferd JWT Token. To get this User use:
```php
$this->Jwt->getIdentity();
```

To encode a user id and sent it the a user use this:
```php
$this->Jwt->encode($userId);
```

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
[MIT](https://choosealicense.com/licenses/mit/)