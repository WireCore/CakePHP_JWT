<?php
declare(strict_types=1);

namespace WireCore\CakePHP_Jwt\Test\TestCase\Controller\Component;

use Cake\TestSuite\TestCase;
use Cake\Http\Response;
use Cake\Http\ServerRequestFactory;
use Cake\Controller\Controller;
use Cake\Utility\Security;
use Cake\TestSuite\IntegrationTestTrait;

class JwtComponentTest extends TestCase {
    
    use IntegrationTestTrait;

    public function setUp(): void {
        parent::setUp();

        $this->request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/'],
            [],
            ['username' => 'mariano', 'password' => 'password']
        );

        $this->response = new Response();
    }

    public function testdoIdentityCheck(){
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function testAllowUnauthenticated(){

        $request = $this->request;

        $controller = new Controller($request, $this->response);
        $controller->loadComponent('CakePHP_JWT.Jwt');

        $controller->Jwt->allowUnauthenticated(['view']);
        $this->assertSame(['view'], $controller->Jwt->getUnauthenticatedActions());

        $controller->Jwt->allowUnauthenticated(['add', 'delete']);
        $this->assertSame(['add', 'delete'], $controller->Jwt->getUnauthenticatedActions());

    }

    public function testAddUnauthenticatedActions(){
        
        $request = $this->request;

        $controller = new Controller($request, $this->response);
        $controller->loadComponent('CakePHP_JWT.Jwt');

        $controller->Jwt->addUnauthenticatedActions(['view']);
        $this->assertSame(['view'], $controller->Jwt->getUnauthenticatedActions());

        $controller->Jwt->addUnauthenticatedActions(['delete']);
        $this->assertSame(['view','delete'], $controller->Jwt->getUnauthenticatedActions());

    }

    public function testGetUnauthenticatedActions(){
        
        $request = $this->request;

        $controller = new Controller($request, $this->response);
        $controller->loadComponent('CakePHP_JWT.Jwt');

        $this->assertSame([], $controller->Jwt->getUnauthenticatedActions());

    }

    public function testFindIdentity(){
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function testGetJwtToken(){
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function testGetIdentity(){
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function testEncode(){
        
        $request = $this->request;

        $controller = new Controller($request, $this->response);
        $controller->loadComponent('CakePHP_JWT.Jwt');

        Security::setSalt("secretSalt");

        $encodedToken = $controller->Jwt->encode(1);

        $this->assertSame(gettype($encodedToken),gettype("token"));

    }

    public function testDecode(){
        
        $request = $this->request;

        $controller = new Controller($request, $this->response);
        $controller->loadComponent('CakePHP_JWT.Jwt');

        Security::setSalt("secretSalt");

        $encodedToken = $controller->Jwt->encode(1);

        $decodedToken = $controller->Jwt->decode($encodedToken);

        $this->assertSame(1,$decodedToken['sub']);

    }

}