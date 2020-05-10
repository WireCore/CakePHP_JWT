<?php
declare(strict_types=1);

namespace Wirecore\CakePHP_JWT\Controller\Component;

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
        'tokenExpiration' => 86400
    ];

    public function startup(): void {
        $this->doIdentityCheck();
    }

    /**
       * 
       * Check the request for a authenticated method and if the method is a authenticated method it run the findIdentity method or return a unauthorized exception
    */
    protected function doIdentityCheck(): void {

        $request = $this->getController()->getRequest();
        $action = $request->getParam('action');
        if (in_array($action, $this->unauthenticatedActions, true)) {
            return;
        }

        $identity = $this->findIdentity();
        if(!$identity){
            throw new UnauthorizedException(
                'You are not authorized to access that location'
            );
        }
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
    public function getUnauthenticatedActions(): array{
        return $this->unauthenticatedActions;
    }

    /**
       * 
       * Return a user object if the sendet jwt token is valid and contain a correct user string
       *
       * @return object
       */
    public function findIdentity() {

        $token = $this->getJwtToken();

        if(!empty($token)){

            $jwtData = $this->decode($token);
            
            $users = TableRegistry::get('Users');
            $user = $users->get($jwtData['sub']);
            $this->user = $user;

            return $user;
        }

    }

    /**
       * 
       * Return the jwt token where are contained in the authorization header
       *
       * @return string
       */
    public function getJwtToken(){

        $controller = $this->getController();
        
        $request = $controller->getRequest();
        
        $jwtToken = $request->getHeaderLine('Authorization');

        return $jwtToken;

    }

    /**
       * 
       * return the user object of this component
       *
       * @return string
       */
    public function getIdentity(): ?IdentityInterface {
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
    public function encode($userId){
        
        $iss = Router::url('/', true); // hostname
        $sub = $userId; // subject
        $iat = strtotime(date('Y-m-d H:i:s')); // issued at
        $exp = $iat + $this->getConfig('tokenExpiration'); // expiration

        $key = Security::getSalt(); // salt
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

        return $jwt;
		
	}
    
    /**
       * 
       * return a decoded array from the given jwt token
       *
       * @return array
       */
	public function decode($jwt){
        
        $key = Security::getSalt(); // salt
        
        $decoded = JWT::decode($jwt, $key, array('HS256'));

        /*
        NOTE: This will now be an object instead of an associative array. To get
        an associative array, you will need to cast it as such:
        */

        $decoded_array = (array) $decoded;

        return $decoded_array;

	}

}