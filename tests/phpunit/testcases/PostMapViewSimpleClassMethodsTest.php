<?php

use PHPUnit\Framework\TestCase;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;
use function Brain\Monkey\Functions\when;
use function Brain\Monkey\Functions\expect;
use function Brain\Monkey\Actions\expectDone;
use function Brain\Monkey\Filters\expectApplied;

#include_once 'C:\Bitnami\wordpress-6.0.1-0\apps\wordpress\htdocs\wp-content\plugins\PostMapViewSimple\inc\PostMapViewSimpleClass.php';

final class PostMapViewSimpleClassMethodsTest extends TestCase {
	public function setUp(): void
    {
        parent::setUp();
        // Set up necessary WordPress functions and mocks
        expect('add_shortcode')
            ->andReturn(true);
        require_once 'C:\Bitnami\wordpress-6.0.1-0\apps\wordpress\htdocs\wp-content\plugins\PostMapTableView\inc\PostMapViewSimpleClass.php';
        setUp();
    }
	public function tearDown(): void {
		tearDown();
		parent::tearDown();
	}

	public function test_construct_with_default_atts() {
		
		expect('plugins_url')
            ->andReturn('http://localhost/wordpress/wp-content/plugins/PostMapTableView/');

		expect('wp_get_upload_dir')
            ->once()
            ->andReturn( ['basedir' => 'C:\Bitnami\wordpress-6.0.1-0\apps\wordpress\htdocs\wp-content\uploads'] );

        expect('plugin_dir_url')
            ->andReturn('http://localhost/wordpress/wp-content/plugins/PostMapTableView/');

		// Erwartetes Ergebnis nach shortcode_atts
        $attr = [];
        $expected_atts = [
            'numberposts' => 100,
            'post_type'   => 'post',
            'showmap'     => 'true',
            'showtable'   => 'true',
            'category'    => 'all',
            'headerhtml'  => '',
        ];

        // Mock die Funktion shortcode_atts
        expect('shortcode_atts')
            ->once()
            ->with([
                'numberposts' => 100,
                'post_type'   => 'post',
                'showmap'     => 'true',
                'showtable'   => 'true',
                'category'    => 'all',
                'headerhtml'  => '',
            ], $attr)
            ->andReturn($expected_atts);

        $tested = new mvbplugins\postmapviewsimple\PostMapViewSimple([]);
		$this->assertInstanceOf( '\mvbplugins\postmapviewsimple\PostMapViewSimple', $tested );

		$privateProp1 = new \ReflectionProperty( "mvbplugins\\postmapviewsimple\\PostMapViewSimple", "lenexcerpt" );
		$privateProp1->setAccessible( true );

		$privateProp2 = new \ReflectionProperty( "mvbplugins\\postmapviewsimple\\PostMapViewSimple", "titlelength" );
		$privateProp2->setAccessible( true );

		$privateProp3 = new \ReflectionProperty( "mvbplugins\\postmapviewsimple\\PostMapViewSimple", "numberposts" );
		$privateProp3->setAccessible( true );

		$privateProp4 = new \ReflectionProperty( "mvbplugins\\postmapviewsimple\\PostMapViewSimple", "category" );
		$privateProp4->setAccessible( true );

		$this->assertEquals( 150, $privateProp1->getValue( $tested ) );
		$this->assertEquals( 80, $privateProp2->getValue( $tested ) );
		$this->assertEquals( 100, $privateProp3->getValue( $tested ) );
		$this->assertEquals( 'all', $privateProp4->getValue( $tested ) );
	}

	public function test_method_sanitize_geoaddress() {
		
		expect('plugins_url')
            ->andReturn('http://localhost/wordpress/wp-content/plugins/PostMapTableView/');

		expect('wp_get_upload_dir')
            ->once()
            ->andReturn( ['basedir' => 'C:\Bitnami\wordpress-6.0.1-0\apps\wordpress\htdocs\wp-content\uploads'] );

        expect('plugin_dir_url')
            ->andReturn('http://localhost/wordpress/wp-content/plugins/PostMapTableView/');

		// Erwartetes Ergebnis nach shortcode_atts
        $attr = [];
        $expected_atts = [
            'numberposts' => 100,
            'post_type'   => 'post',
            'showmap'     => 'true',
            'showtable'   => 'true',
            'category'    => 'all',
            'headerhtml'  => '',
        ];

        // Mock die Funktion shortcode_atts
        expect('shortcode_atts')
            ->once()
            ->with([
                'numberposts' => 100,
                'post_type'   => 'post',
                'showmap'     => 'true',
                'showtable'   => 'true',
                'category'    => 'all',
                'headerhtml'  => '',
            ], $attr)
            ->andReturn($expected_atts);

        $tested = new mvbplugins\postmapviewsimple\PostMapViewSimple([]);
		$this->assertInstanceOf( '\mvbplugins\postmapviewsimple\PostMapViewSimple', $tested );

		$privateProp1 = new \ReflectionProperty( "mvbplugins\\postmapviewsimple\\PostMapViewSimple", "lenexcerpt" );
		$privateProp1->setAccessible( true );

		$privateProp2 = new \ReflectionProperty( "mvbplugins\\postmapviewsimple\\PostMapViewSimple", "titlelength" );
		$privateProp2->setAccessible( true );

		$privateProp3 = new \ReflectionProperty( "mvbplugins\\postmapviewsimple\\PostMapViewSimple", "numberposts" );
		$privateProp3->setAccessible( true );

		$privateProp4 = new \ReflectionProperty( "mvbplugins\\postmapviewsimple\\PostMapViewSimple", "category" );
		$privateProp4->setAccessible( true );

		$this->assertEquals( 150, $privateProp1->getValue( $tested ) );
		$this->assertEquals( 80, $privateProp2->getValue( $tested ) );
		$this->assertEquals( 100, $privateProp3->getValue( $tested ) );
		$this->assertEquals( 'all', $privateProp4->getValue( $tested ) );

		$class = new \ReflectionClass( 'mvbplugins\postmapviewsimple\PostMapViewSimple' );
		$privateMethod = $class->getMethod( 'sanitize_geoaddress' );
		$privateMethod->setAccessible( TRUE );

		$out = $privateMethod->invoke( $tested, '' );
		$this->assertEquals( ['address'=>'none','state'=>'none','country'=>'none'], $out );
		
		$geoaddress = ['city'=>'city','state'=>'state','country'=>'country'];
		$out = $privateMethod->invoke( $tested, $geoaddress );
		$this->assertEquals( ['address'=>'city','state'=>'state','country'=>'country'], $out );

	}

	public function testGetStatisticsFromGpxfile()
    {
		expect('plugins_url')
		->andReturn('http://localhost/wordpress/wp-content/plugins/PostMapTableView/');

		expect('wp_get_upload_dir')
			->once()
			->andReturn( ['basedir' => 'C:\Bitnami\wordpress-6.0.1-0\apps\wordpress\htdocs\wp-content\uploads'] );

		expect('plugin_dir_url')
			->andReturn('http://localhost/wordpress/wp-content/plugins/PostMapTableView/');

		// Erwartetes Ergebnis nach shortcode_atts
		$attr = [];
		$expected_atts = [
			'numberposts' => 100,
			'post_type'   => 'post',
			'showmap'     => 'true',
			'showtable'   => 'true',
			'category'    => 'all',
			'headerhtml'  => '',
		];

		// Mock die Funktion shortcode_atts
		expect('shortcode_atts')
			->once()
			->with([
				'numberposts' => 100,
				'post_type'   => 'post',
				'showmap'     => 'true',
				'showtable'   => 'true',
				'category'    => 'all',
				'headerhtml'  => '',
			], $attr)
			->andReturn($expected_atts);
		
		expect('is_file')
			->atLeast()
			->once()
			->with($this->anything())
			->andReturnUsing(function ($path_to_gpxfile) {
                // Dynamische Rückgabe basierend auf dem Meta-Key
                if ($path_to_gpxfile === '') {
                    return false;
				} else {
					return true;
                }
            });

		when('simplexml_load_file')->alias(function ($path_to_gpxfile) {
			if ($path_to_gpxfile === 'file1') {
				return (object)[
					'metadata' => (object)[
						'desc' => 'Dist: 111 0 0 444 0 0 777'
					]
				];
			} elseif ($path_to_gpxfile === 'file2') {
				return (object)[
					'metadata' => (object)[
						'desc' => 'Dist: 17,0 km, Gain: 1214 m, Loss: 1181 m'
					]
				];
			} else {
				return (object)[
					'metadata' => (object)[
						'desc' => 'Dist: 17.111 km, Gain: 1214.22 m, Loss: 1181.333 m'
					]
				];
			}
		});
		
		when('number_format_i18n')->returnArg();
		
		$tested = new mvbplugins\postmapviewsimple\PostMapViewSimple([]);
		$this->assertInstanceOf( '\mvbplugins\postmapviewsimple\PostMapViewSimple', $tested );

		$class = new \ReflectionClass( 'mvbplugins\postmapviewsimple\PostMapViewSimple' );
		$privateMethod = $class->getMethod( 'get_statistics_from_gpxfile' );
		$privateMethod->setAccessible( TRUE );

		$out = $privateMethod->invoke( $tested, '' );
		$this->assertEquals( '0 0 0 0 0 0 0 0', $out );

		$out = $privateMethod->invoke( $tested, 'file1' );
		$this->assertEquals( 'Dist: 111 0 0 444 0 0 777', $out );

		$out = $privateMethod->invoke( $tested, 'file2' );
		$this->assertEquals( 'Dist: 17 km, Gain: 1214 m, Loss: 1181 m', $out );

		$out = $privateMethod->invoke( $tested, 'file3' );
		// the assertion here is because number_format_i18n is mocked and returns the same value as the input
		$this->assertEquals( 'Dist: 17.111 km, Gain: 1214.22 m, Loss: 1181.333 m', $out );
    }

	public function test_method_post_extract() {
		expect('plugins_url')
		->andReturn('http://localhost/wordpress/wp-content/plugins/PostMapTableView/');

		expect('wp_get_upload_dir')
			->once()
			->andReturn( ['basedir' => 'C:\Bitnami\wordpress-6.0.1-0\apps\wordpress\htdocs\wp-content\uploads'] );

		expect('plugin_dir_url')
			->andReturn('http://localhost/wordpress/wp-content/plugins/PostMapTableView/');

		// Erwartetes Ergebnis nach shortcode_atts
		$attr = [];
		$expected_atts = [
			'numberposts' => 100,
			'post_type'   => 'post',
			'showmap'     => 'true',
			'showtable'   => 'true',
			'category'    => 'all',
			'headerhtml'  => '',
		];

		// Mock die Funktion shortcode_atts
		expect('shortcode_atts')
			->once()
			->with([
				'numberposts' => 100,
				'post_type'   => 'post',
				'showmap'     => 'true',
				'showtable'   => 'true',
				'category'    => 'all',
				'headerhtml'  => '',
			], $attr)
			->andReturn($expected_atts);

		$tested = new mvbplugins\postmapviewsimple\PostMapViewSimple([]);
		$this->assertInstanceOf( '\mvbplugins\postmapviewsimple\PostMapViewSimple', $tested );

		$class = new \ReflectionClass( 'mvbplugins\postmapviewsimple\PostMapViewSimple' );
		$privateMethod = $class->getMethod( 'generate_the_excerpt' );
		$privateMethod->setAccessible( TRUE );

		$mocked_post = (object) ['ID' => 1, 'post_title' => 'Post 1', 'post_content' => '<p>Content of Post 1</p>', 'post_excerpt' => ''];
		$out = $privateMethod->invoke( $tested, $mocked_post, 100 );
		$this->assertEquals( 'Content of Post 1...', $out );
		
		$content = '<!-- wp:heading {"className":"wp-block-heading"} -->
		<h2 class="wp-block-heading" id="kurzbeschreibung"><strong>Kurzbeschreibung</strong></h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph -->
		<p>Lange, anspruchsvolle Bergtour vom Langen Sand über das Dürrnbachhorn in den Chiemgauer Alpen. Einsam und wild führt der Weg auf der Nordseite hoch zum Grat und wieder zurück in das Tal mit Weitsee und Mittersee. Tour nur für erfahrene Berggeher.</p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"className":"wp-block-heading"} -->
		<h2 class="wp-block-heading" id="bildergalerie-mit-karte-und-gpx-track">Bildergalerie mit Karte und GPX-Track</h2>
		<!-- /wp:heading -->

		<!-- wp:shortcode -->
		[gpxview imgpath="Alben_Website/Deutschland/Bergtour-Langer-Sand-Duerrnbachhorn" gpxfile="Wanderung-Langer-Sand-Duerrnbachhorn.gpx"]
		<!-- /wp:shortcode -->

		<!-- wp:heading {"className":"wp-block-heading"} -->
		<h2 class="wp-block-heading" id="tourenbeschreibung"><strong>Tourenbeschreibung</strong> der Bergtour vom Langen Sand über das Dürrnbachhorn</h2>
		<!-- /wp:heading -->

		<!-- wp:heading {"level":3} -->
		<h3 class="wp-block-heading">Aufstieg</h3>
		<!-- /wp:heading -->

		<!-- wp:paragraph -->
		<p>Die Bergtour vom Langen Sand über das Dürrnbachhorn startet an der Wildbachfurt "Langer Sand", die noch auf der Bundesstraße zu überqueren ist. Man geht direkt in das Bachbett des nur zur Schneeschmelze führenden Baches und biegt nach wenigen Metern links und dann wieder rechts auf den schmalen Weg ab. Andere Tourenbeschreibungen führen den Wanderer hier weiter durch das Bachbett, was angesichts des schönen Weges durch den Buchenwald keine gute Empfehlung ist.</p>
		<!-- /wp:paragraph -->

		<!-- wp:paragraph -->
		<p>Nach ca. 1,5 km durch den Buchenwald geht man einer unscheinbaren Weggabelung links über den leicht zugewachsenen Weg weiter. Der Weg führt nun stets gut erkennbar über 350 Höhenmeter bis zur Gabelung vor dem Ristrichtkopf. Hier zweigen wir nach Süden ab und mühen uns auch hier schon durch den von Latschen zugewachsenen Weg hoch zum Hochbrunstkopf, der mit seiner tollen Aussicht zu einer ersten Rast einlädt.</p>
		<!-- /wp:paragraph -->';

		$mocked_post = (object) ['ID' => 1, 'post_title' => 'Post 1', 'post_content' => $content, 'post_excerpt' => ''];
		$out = $privateMethod->invoke( $tested, $mocked_post, 100 );
		$this->assertEquals( 'Lange, anspruchsvolle Bergtour vom Langen Sand über das Dürrnbachhorn in den Chiemgauer Alpen. Ein...', $out );

		$privateProp1 = new \ReflectionProperty( "mvbplugins\\postmapviewsimple\\PostMapViewSimple", "useWPExcerptExtraction" );
		$privateProp1->setAccessible( true );
		$privateProp1->setValue( $tested, false );

		$out = $privateMethod->invoke( $tested, $mocked_post, 100 );
		$this->assertEquals( 'Lange, anspruchsvolle Bergtour vom Langen Sand über das Dürrnbachhorn in den Chiemgauer Alpen. Ein...', $out );

	}
}