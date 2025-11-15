<?php

namespace mvbplugins\helpers;

use PHPUnit\Framework\TestCase;
use phpmock\phpunit\PHPMock;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;
//use function Brain\Monkey\Functions\when;
use function Brain\Monkey\Functions\expect;
//use function Brain\Monkey\Actions\expectDone;
//use function Brain\Monkey\Filters\expectApplied;

include PLUGIN_DIR . '\inc\geoaddress.php';
use function \mvbplugins\helpers\get_geoaddress as get_geoaddress;

class GetGeoaddressTest extends TestCase
{
    use PHPMock;
    private $postId = 1;
    private $lat = '52.5200';
    private $lon = '13.4050';

    public function setUp(): void {
		parent::setUp();
		setUp();
	}
	public function tearDown(): void {
		tearDown();
		parent::tearDown();
	}

    public function testFileGetContents()
    {
        $mock = $this->getFunctionMock(__NAMESPACE__, 'file_get_contents');
        $mock->expects($this->once())
             ->willReturn('Mocked file contents');

        $result = file_get_contents('some/file/path');
        $this->assertEquals('Mocked file contents', $result);
    }

    public function testFileGetContentsFailure()
    {
        expect( 'get_option' )
			->once()
			->andReturn( 'admin@example.com' );

		expect( 'home_url' )
			->once()
			->andReturn( 'https://example.com' );

        $mock = $this->getFunctionMock(__NAMESPACE__, 'file_get_contents');
        $mock->expects($this->once())
             ->willReturn(false);

        expect( '__')
            ->andReturn( 'Error' );

        expect( 'wp_mail')
            ->once()
            ->andReturn( true );

        $result = get_geoaddress($this->postId, $this->lat, $this->lon);
        $this->assertEquals('a:4:{s:7:"country";s:5:"Error";s:5:"state";s:5:"Error";s:4:"city";s:5:"Error";s:7:"village";s:5:"Error";}', $result);
    }
    
    public function testSuccessfulApiCall()
    {
        expect( 'get_option' )
			->once()
			->andReturn( 'admin@example.com' );

		expect( 'home_url' )
			->once()
			->andReturn( 'https://example.com' );

        expect( 'update_post_meta')
            ->once()
            ->andReturn( true );

        $result = get_geoaddress($this->postId, $this->lat, $this->lon);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);

        $result = maybe_unserialize($result);

        $this->assertArrayHasKey('country', $result);
        $this->assertEquals('Deutschland', $result['country']);
    }
    
}

// Mocks for global functions
function maybe_serialize($data) {
    return serialize($data);
}

function maybe_unserialize($data) {
    return unserialize($data);
}