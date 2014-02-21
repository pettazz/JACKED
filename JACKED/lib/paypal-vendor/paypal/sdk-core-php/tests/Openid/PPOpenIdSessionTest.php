<?php
use PayPal\Common\PPApiContext;
use PayPal\Auth\Openid\PPOpenIdSession;
/**
 * Test class for PPOpenIdSession.
 *
 */
class PPOpenIdSessionTest extends \PHPUnit_Framework_TestCase {
	
	private $context;
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->context = new PPApiContext(
			array(
				'acct1.ClientId' => 'DummyId',
				'acct1.ClientSecret' => 'A8VERY8SECRET8VALUE0',
				'mode' => 'live'
			)
		);
	}
	
	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
	}
	

	/**
	 * @test
	 */
	public function testLoginUrlForMultipleScopes() {
	
		$redirectUri = 'http://mywebsite.com';
		$scope = array('this', 'that', 'and more');
	
		$expectedBaseUrl = "https://www.sandbox.paypal.com/webapps/auth/protocol/openidconnect/v1/authorize";
	
		$this->assertEquals($expectedBaseUrl . "?client_id=ProxyRP-01&response_type=code&scope=this+that+and+more+openid&redirect_uri=" . urlencode($redirectUri),
				PPOpenIdSession::getAuthorizationUrl($redirectUri, $scope), "Failed case - custom scope");
	
		$scope = array();
		$this->assertEquals($expectedBaseUrl . "?client_id=ProxyRP-01&response_type=code&scope=openid+profile+address+email+phone+" . urlencode("https://uri.paypal.com/services/paypalattributes") . "&redirect_uri=" . urlencode($redirectUri),
				PPOpenIdSession::getAuthorizationUrl($redirectUri, $scope), "Failed case - default scope");
	
		$scope = array('openid');
		$this->assertEquals($expectedBaseUrl . "?client_id=ProxyRP-01&response_type=code&scope=openid&redirect_uri=" . urlencode($redirectUri),
				PPOpenIdSession::getAuthorizationUrl($redirectUri, $scope), "Failed case - openid scope");
	}
	
	/**
	 * @test
	 */
	public function testLoginWithCustomConfig() {
	
		$redirectUri = 'http://mywebsite.com';
		$scope = array('this', 'that', 'and more');
	
		$expectedBaseUrl = "https://www.paypal.com/webapps/auth/protocol/openidconnect/v1/authorize";
			
		$this->assertEquals($expectedBaseUrl . "?client_id=DummyId&response_type=code&scope=this+that+and+more+openid&redirect_uri=" . urlencode($redirectUri),
				PPOpenIdSession::getAuthorizationUrl($redirectUri, $scope, $this->context), "Failed case - custom config");
	}
	
	/**
	 * @test
	 */
	public function testLogoutWithCustomConfig() {
		
		$redirectUri = 'http://mywebsite.com';
		$idToken = 'abc';
		
		$expectedBaseUrl = "https://www.paypal.com/webapps/auth/protocol/openidconnect/v1/endsession";
			
		$this->assertEquals($expectedBaseUrl . "?id_token=$idToken&redirect_uri=" . urlencode($redirectUri) . "&logout=true",
				PPOpenIdSession::getLogoutUrl($redirectUri, $idToken, $this->context), "Failed case - custom config");
	}
}
