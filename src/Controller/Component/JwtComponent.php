<?php
declare(strict_types=1);

namespace WireCore\CakePHP_JWT\Controller\Component;

use Cake\Controller\Component;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Utility\Security;
use Firebase\JWT\JWT;
use Cake\Routing\Router;
use Cake\ORM\TableRegistry;

class JwtComponent extends Component {

    private $userId;
    protected $unauthenticatedActions = [];

    protected $_defaultConfig = [
        'accessTokenExpiration' => 900, // in seconds
        'refreshTokenExpiration' => 60, // in days
        'headerParam' => 'Authorization',
        'usersTable' => 'Users',
        'unauthorizedExceptionText' => 'You are not authorized to access that location',
        'encryptionKey' => '',
        'refreshTokenName' => 'refresh_token',
        'refreshTokenSecure' => false,
        'refreshTokenHttpOnly' => true,
        'hostAddPort' => false,
    ];

    public function startup(): void {

        // set encryption key if empty
        if(empty($this->getConfig('encryptionKey')) == true){
            $this->setConfig('encryptionKey', Security::getSalt());
        }

        $identity = $this->doIdentityCheck();
    }

    /**
       * 
       * Check the request for a authenticated method and if the method is a authenticated method it run the findIdentity method or return a unauthorized exception
    */
    protected function doIdentityCheck(){

        $request = $this->getController()->getRequest();
        $action = $request->getParam('action');
        if (in_array($action, $this->unauthenticatedActions, true)) {
            return true;
        }

        $identity = $this->findIdentity();

        if(!$identity){
            throw new UnauthorizedException(
                $this->getConfig('unauthorizedExceptionText')
            );
        }
    }

    /**
     * Return the refresh token expiration in seconds from the configured days in config
     *
     * @return integer
     */
    protected function getRefreshTokenExpirationInSeconds(): int {

        return $this->getConfig('refreshTokenExpiration') * 86400;

    }

    /**
       * 
       * Add actions from a controller to the unauthenticated actions
       *
       * @param array $actions  the actions that are dont need a authentication
       */
    public function allowUnauthenticated(array $actions){
        $this->unauthenticatedActions = $actions;

        return $this;
    }

    /**
       * 
       * Add actions from a controller to the existing unauthenticated actions
       *
       * @param array $actions  the actions that are dont need a authentication
       */
    public function addUnauthenticatedActions(array $actions){
        $this->unauthenticatedActions = array_merge($this->unauthenticatedActions, $actions);
        $this->unauthenticatedActions = array_values(array_unique($this->unauthenticatedActions));

        return $this;
    }

    /**
       * 
       * Return a list of actions that are dont need a authentication
       *
       * @return array
       */
    public function getUnauthenticatedActions(): array {
        return $this->unauthenticatedActions;
    }

    /**
       * 
       * Return a user object if the sendet jwt token is valid and contain a correct user string
       *
       * @return object
       */
      public function findIdentity(){

        $token = $this->getAccessToken();

        if(!empty($token)){

            $jwtData = $this->decode($token);
            
            $user = TableRegistry::get($this->getConfig('usersTable'))->find('all')->where(['id' => $jwtData['sub']])->toArray();
            if(!empty($user)){
                $user = $user[0];
                $this->user = $user;
            } else {
                $user = null;
            }

            return $user;
        
        } else {

            return null;
        
        }

    }

    /**
       * 
       * Return the jwt token where are contained in the authorization header
       *
       * @return string
       */
    public function getAccessToken(){

        $controller = $this->getController();
        
        $request = $controller->getRequest();
        
        $jwtToken = $request->getHeaderLine($this->getConfig('headerParam'));

        return $jwtToken;

    }

    /**
       * 
       * return the user object of this component
       *
       * @return string
       */
    public function getIdentity() {
        return $this->user;
    }

    /**
       * 
       * implemented events from the cakephp component
       *
       */
    public function implementedEvents(): array{
        return [
            'Controller.startup' => 'startup',
        ];
    }

    /* JWT functions */

    /**
       * 
       * encode the given user id to a valid jwt token
       *
       * @param int $userId  the userId that are contained in the jwt token
       */
    public function encode($userId, $tokenType = 'access'){
        
        $iss = Router::url('/', true); // hostname
        $sub = $userId; // subject
        $iat = strtotime(date('Y-m-d H:i:s')); // issued at
        $key = $this->getConfig('encryptionKey'); // key
        
        // expiration
        switch($tokenType){
            case 'access':
                $exp = $iat + $this->getConfig('accessTokenExpiration');
                break;
            case 'refresh':
                $exp = $iat + $this->getRefreshTokenExpirationInSeconds();
                break;
            default:
                $exp = $iat + $this->getConfig('accessTokenExpiration');
        }

        $payload = array(
            "iss" => $iss,
            "sub" => $sub,
            "iat" => $iat,
            "exp" => $exp
        );

        /**
         * IMPORTANT:
         * You must specify supported algorithms for your application. See
         * https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
         * for a list of spec-compliant algorithms.
         */
        $jwt = JWT::encode($payload, $key);

        $accessToken = [
            'jwt' => $jwt,
            'expiration' => $exp
        ];

        return $accessToken;
		
	}
    
    /**
       * 
       * return a decoded array from the given jwt token
       *
       * @return array
       */
	public function decode($jwt){
        
        $key = $this->getConfig('encryptionKey');
        
        $decoded = JWT::decode($jwt, $key, array('HS256'));

        $decoded_array = (array) $decoded;

        return $decoded_array;

	}

    /**
     * Generate a new access token with the user Id
     *
     * @param int $userId
     * @return array
     */
    public function generateAccessToken($userId): array {
        return $this->encode($userId, 'access');
    }

    /**
     * Generate a new refresh token with the user Id
     *
     * @param int $userId
     * @return array
     */
    public function generateRefreshToken($userId): array {
        return $this->encode($userId, 'refresh');
    }

    /**
     * Generate a new Refresh token and set it to the defined named cookie
     *
     * @param int $userId
     * @return bool
     */
    public function setRefreshTokenCookie($userId): bool {

        $name = $this->getConfig('refreshTokenName');
        $refreshToken = $this->generateRefreshToken($userId)['jwt'];
        $expiration = strtotime(date('Y-m-d H:i:s')) + $this->getRefreshTokenExpirationInSeconds();
        $path = '/';
        $currentUrl = parse_url($_SERVER['HTTP_HOST']);
        if(isset($currentUrl['path'])){
            $host = $currentUrl['path'];
        } else {
            if($this->getConfig('hostAddPort') == true){
                $host = $currentUrl['host'].':'.$currentUrl['port'];
            } else {
                $host = $currentUrl['host'];
            }
        }
        $secure = $this->getConfig('refreshTokenSecure');
        $httpOnly = $this->getConfig('refreshTokenHttpOnly');

        return setcookie($name, $refreshToken, $expiration, $path, $host, $secure, $httpOnly);

    }

    /**
     * Refresh the access and the refresh token.
     *
     */
    public function refreshTokens(){

        if(isset($_COOKIE['refresh_token'])){

            $refreshToken = $_COOKIE['refresh_token'];
            $decodedRefreshToken = $this->decode($refreshToken);

            $accessToken = $this->generateAccessToken($decodedRefreshToken['sub']);
            $this->setRefreshTokenCookie($decodedRefreshToken['sub']);

            return $accessToken;

        } else {
            return false;
        }

    }

}