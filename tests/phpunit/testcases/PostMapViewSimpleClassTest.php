<?php

namespace mvbplugins\helpers;

use PHPUnit\Framework\TestCase;
//use phpmock\phpunit\PHPMock;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;
use function Brain\Monkey\Functions\when;
use function Brain\Monkey\Functions\expect;
//use function Brain\Monkey\Actions\expectDone;
//use function Brain\Monkey\Filters\expectApplied;

class PostMapViewSimpleClassTest extends TestCase
{
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

    public function testShowPostMapWithTwoPosts()
    {
        expect('get_lastpostdate')
            ->andReturn('2025-01-27 12:00:00');

        expect('delete_transient')
            ->andReturn( true );

        expect('get_transient')
            ->andReturn( false );

        expect('set_transient')
            ->andReturn( true );

        expect('plugins_url')
            ->andReturn('http://localhost/wordpress/wp-content/plugins/PostMapTableView/');

        expect('plugin_dir_url')
            ->andReturn('http://localhost/wordpress/wp-content/plugins/PostMapTableView/');

        expect('get_option')
            ->once()
            ->andReturn( false );

        expect('wp_get_upload_dir')
            ->once()
            ->andReturn( ['basedir' => 'C:\Bitnami\wordpress-6.0.1-0\apps\wordpress\htdocs\wp-content\uploads'] );

        // Erwartetes Ergebnis nach shortcode_atts
        $attr = [];
        $expected_atts = [
            'numberposts' => 5,
            'post_type'   => 'post',
            'showmap'     => 'true',
            'showtable'   => 'false',
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

        // Simuliere den `extract`-Effekt (nicht direkt testen)
        extract($expected_atts);
        
        expect('wp_enqueue_style')
            ->andReturn(true);

        expect('wp_register_script')
            ->andReturn(true);

        expect('wp_enqueue_script')
            ->andReturn(true);

        // Simulierte Rückgabe von get_posts
        $mocked_posts = [
            (object) ['ID' => 1, 'post_title' => 'Post 1', 'post_content' => '<p>Content of Post 1</p>', 'post_excerpt' => ''],
            (object) ['ID' => 2, 'post_title' => 'Post 2', 'post_content' => '<p>Content of Post 2</p>', 'post_excerpt' => ''],
        ];

        $args = ['numberposts' => 5, 'post_type'   => 'post', 'post_status' => 'publish'];

        // Mock die Funktion get_posts
        expect('get_posts')
            ->once()
            ->with($args) // Die Argumente, die erwartet werden
            ->andReturn($mocked_posts); // Rückgabewerte, die simuliert werden
        
        expect('get_post_meta')
            ->atLeast()
            ->once()
            ->with($this->anything(), $this->anything(), $this->anything())
            ->andReturnUsing(function ($post_id, $meta_key, $single) {
                // Dynamische Rückgabe basierend auf dem Meta-Key
                switch ($meta_key) {
                    case 'lat':
                        return 12.98765;
                    case 'lon':
                        return 49.123456;
                    case 'geoadress':
                        return [serialize('address-' . $post_id), $meta_key, 'none'];
                    default:
                        return null;
                }
            });
        
        expect('wp_get_post_tags')
            ->andReturn( ['tag1', 'tag2'] );

        expect( 'plugin_dir_path' )
			//->once()
			->andReturn( dirname(dirname(dirname(__DIR__))) . '/' );

        expect( 'get_the_category' )
            ->andReturnUsing(function ($post_id) {
                return [(object) ['term_id' => $post_id, 'name' => 'Category '.$post_id, 'slug' => 'category-'.$post_id]];
            });

        expect('get_the_post_thumbnail_url')
            ->andReturn( 'http://localhost/wordpress/wp-content/uploads/2022/01/thumbnail-1.jpg' );

        expect('get_permalink')
            ->andReturn( 'http://localhost/wordpress/post-permalink' );

        expect('current_user_can')
            ->andReturn(false);

        expect('wp_localize_script')
            ->andReturn(true);

        //$output = \mvbplugins\postmapviewsimple\show_post_map($attr);
        $class = new \mvbplugins\postmapviewsimple\PostMapViewSimple($attr);
        $output = $class->show_post_map();

        $expected = '<div class="box1"><div id="map"></div><div id="map10_img"><a href="http://localhost/wordpress/wp-content/uploads/2022/01/thumbnail-1.jpg" data-title="Post 1" data-icon="travel" data-geo="lat:12.98765,lon:49.123456" data-link="http://localhost/wordpress/post-permalink">Content of Post 1...</a><a href="http://localhost/wordpress/wp-content/uploads/2022/01/thumbnail-1.jpg" data-title="Post 2" data-icon="travel" data-geo="lat:12.98765,lon:49.123456" data-link="http://localhost/wordpress/post-permalink">Content of Post 2...</a></div></div>';
        
        $this->assertEquals($expected, $output);
    }

    public function testShowPostMapWithTable()
    {
        expect('get_lastpostdate')
            ->andReturn('2025-01-27 12:00:00');

        expect('delete_transient')
            ->andReturn( true );

        expect('get_transient')
            ->andReturn( false );

        expect('set_transient')
            ->andReturn( true );

        
        expect('plugins_url')
            ->andReturn('http://localhost/wordpress/wp-content/plugins/PostMapTableView/');

        expect('plugin_dir_url')
            ->andReturn('http://localhost/wordpress/wp-content/plugins/PostMapTableView/');

        expect('get_option')
            ->once()
            ->andReturn( false );

        expect('wp_get_upload_dir')
            ->once()
            ->andReturn( ['basedir' => 'C:\Bitnami\wordpress-6.0.1-0\apps\wordpress\htdocs\wp-content\uploads'] );

        // Erwartetes Ergebnis nach shortcode_atts
        $attr = [];
        $expected_atts = [
            'numberposts' => 5,
            'post_type'   => 'post',
            'showmap'     => 'false',
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

        // Simuliere den `extract`-Effekt (nicht direkt testen)
        extract($expected_atts);
        
        expect('wp_enqueue_style')
            ->andReturn(true);

        expect('wp_register_script')
            ->andReturn(true);

        expect('wp_enqueue_script')
            ->andReturn(true);

        // Simulierte Rückgabe von get_posts
        $mocked_posts = [
            (object) ['ID' => 1, 'post_title' => 'Post 1', 'post_content' => '<p>Content of Post 1</p>', 'post_excerpt' => ''],
            (object) ['ID' => 2, 'post_title' => 'Post 2', 'post_content' => '<p>Content of Post 2</p>', 'post_excerpt' => ''],
        ];

        $args = ['numberposts' => 5, 'post_type'   => 'post', 'post_status' => 'publish'];

        // Mock die Funktion get_posts
        expect('get_posts')
            ->once()
            ->with($args) // Die Argumente, die erwartet werden
            ->andReturn($mocked_posts); // Rückgabewerte, die simuliert werden
        
        expect('get_post_meta')
            ->atLeast()
            ->once()
            ->with($this->anything(), $this->anything(), $this->anything())
            ->andReturnUsing(function ($post_id, $meta_key, $single) {
                // Dynamische Rückgabe basierend auf dem Meta-Key
                switch ($meta_key) {
                    case 'lat':
                        return 12.98765;
                    case 'lon':
                        return 49.123456;
                    case 'geoadress':
                        return [serialize('address-' . $post_id), $meta_key, 'none'];
                    default:
                        return null;
                }
            });
        
        expect('wp_get_post_tags')
            ->andReturn( ['tag1', 'tag2'] );

        expect( 'plugin_dir_path' )
			//->once()
			->andReturn( dirname(dirname(dirname(__DIR__))) . '/' );

        expect( 'get_the_category' )
            ->andReturnUsing(function ($post_id) {
                return [(object) ['term_id' => $post_id, 'name' => 'Category '.$post_id, 'slug' => 'category-'.$post_id]];
            });

        expect('get_the_post_thumbnail_url')
            ->andReturn( 'http://localhost/wordpress/wp-content/uploads/2022/01/thumbnail-1.jpg' );

        expect('get_permalink')
            ->andReturn( 'http://localhost/wordpress/post-permalink' );

        expect('current_user_can')
            ->andReturn(false);

        expect('wp_localize_script')
            ->andReturn(true);

        //$output = \mvbplugins\postmapviewsimple\show_post_map($attr);
        $class = new \mvbplugins\postmapviewsimple\PostMapViewSimple($attr);
        $output = $class->show_post_map();

        $expected = '<h4>Tourenübersicht</h4><p>Tabellarische Übersicht aller Touren- und Reiseberichte mit Filter- und Sortierfunktion<br></p><p>Die Kopfzeile ermöglicht die Suche in der Tabelle nach beliebigen Inhalten:</p><button id="tablereset" type="button">Reset Filter</button><table id="post_table"><thead><tr><th>Nr</th><th>Titel</th><th>Kategorie</th><th>Distanz</th><th>Aufstieg</th><th>Abstieg</th><th>Land</th><th>Region</th><th>Stadt</th></tr></thead><tbody><tr><td>1</td><td><a href="http://localhost/wordpress/post-permalink" target="_blank">Post 1</a></td><td>Reisebericht</td><td>0</td><td>0</td><td>0</td><td>none</td><td>none</td><td><a href="https://www.google.com/maps/place/12.987650,49.123456/@12.987650,49.123456,9z" target="_blank" rel="noopener noreferrer">none</a></td></tr><tr><td>2</td><td><a href="http://localhost/wordpress/post-permalink" target="_blank">Post 2</a></td><td>Reisebericht</td><td>0</td><td>0</td><td>0</td><td>none</td><td>none</td><td><a href="https://www.google.com/maps/place/12.987650,49.123456/@12.987650,49.123456,9z" target="_blank" rel="noopener noreferrer">none</a></td></tr></tbody></table>';
        
        $this->assertEquals($expected, $output);
    }
}