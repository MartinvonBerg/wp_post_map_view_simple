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

include 'C:\Bitnami\wordpress-6.0.1-0\apps\wordpress\htdocs\wp-content\plugins\PostMapTableView\inc\get_icon_cat.php';
use function \mvbplugins\helpers\wp_postmap_load_category_mapping as wp_postmap_load_category_mapping;
use function \mvbplugins\helpers\wp_postmap_get_cat as wp_postmap_get_cat;
use function \mvbplugins\helpers\wp_postmap_get_icon as wp_postmap_get_icon;

/**
 * Class GetIconCatTest
 *
 * Tests for the wp_postmap_load_category_mapping function.
 */
class GetIconCatTest extends TestCase
{
    /**
     * Test loading category mapping from a valid JSON file.
     */
    public function testLoadCategoryMappingFromValidFile()
    {
        $file = WP_PLUGIN_DIR . '/PostMapTableView/settings/category_mapping.json';
        $expected = [
            'default' => ['category' => 'Reisebericht', 'icon' => 'travel', 'icon-png' => 'campingcar.png'],
            'mapping' => array(
                "Trekk" => array(
                    "category" => "Trekking",
                    "icon" => "hiking",
                    "icon-png" => "hiking.png"
                ),
                "bike&&hike" => array(
                    "category" => "Bike-Hike",
                    "icon" => "bike-hike",
                    "icon-png" => "mountainbiking-3.png"
                ),
                "Radfahren" => array(
                    "category" => "Radfahren",
                    "icon" => "cycling",
                    "icon-png" => "cycling.png"
                ),
                "MTB" => array(
                    "category" => "MTB",
                    "icon" => "MTB",
                    "icon-png" => "MTB.png"
                ),
                "Wander" => array(
                    "category" => "Wandern",
                    "icon" => "hiking",
                    "icon-png" => "hiking2.png"
                ),
                "Bergtour" => array(
                    "category" => "Bergtour",
                    "icon" => "mountain",
                    "icon-png" => "peak2.png"
                ),
                "skitour" => array(
                    "category" => "Skitour",
                    "icon" => "skiing",
                    "icon-png" => "skiing.png"
                ),
                "Paddeln" => array(
                    "category" => "Paddeln",
                    "icon" => "kayaking",
                    "icon-png" => "kayaking2.png"
                ),
                "reisebericht" => array(
                    "category" => "Reisebericht",
                    "icon" => "travel",
                    "icon-png" => "campingcar.png"
                )
            )
        ];

        $result = wp_postmap_load_category_mapping($file);
        $this->assertEquals($expected['default'], $result['default']);
        $this->assertEquals($expected['mapping'], $result['mapping']);
    }

    /**
     * Test loading category mapping from a non-existent file.
     */
    public function testLoadCategoryMappingFromNonExistentFile()
    {
        $file = __DIR__ . '/settings/non_existent_file.json';
        $expected = [
            'default' => ['category' => 'Reisebericht', 'icon' => 'travel', 'icon-png' => 'campingcar.png'],
            'mapping' => []
        ];

        $result = wp_postmap_load_category_mapping($file);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test loading category mapping from an invalid JSON file.
     */
    public function testLoadCategoryMappingFromInvalidFile()
    {
        $file =  '../testdata/invalid_category_mapping.json';
        $expected = [
            'default' => ['category' => 'Reisebericht', 'icon' => 'travel', 'icon-png' => 'campingcar.png'],
            'mapping' => []
        ];

        $result = wp_postmap_load_category_mapping($file);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test loading category mapping with no file parameter (default file).
     */
    public function testLoadCategoryMappingWithNoFileParameter()
    {
        expect( 'plugin_dir_path' )
			->once()
			->andReturn( dirname(dirname(dirname(__DIR__))) . '/' );

        $expected = [
            'default' => ['category' => 'Reisebericht', 'icon' => 'travel', 'icon-png' => 'campingcar.png'],
            'mapping' => array(
                "Trekk" => array(
                    "category" => "Trekking",
                    "icon" => "hiking",
                    "icon-png" => "hiking.png"
                ),
                "bike&&hike" => array(
                    "category" => "Bike-Hike",
                    "icon" => "bike-hike",
                    "icon-png" => "mountainbiking-3.png"
                ),
                "Radfahren" => array(
                    "category" => "Radfahren",
                    "icon" => "cycling",
                    "icon-png" => "cycling.png"
                ),
                "MTB" => array(
                    "category" => "MTB",
                    "icon" => "MTB",
                    "icon-png" => "MTB.png"
                ),
                "Wander" => array(
                    "category" => "Wandern",
                    "icon" => "hiking",
                    "icon-png" => "hiking2.png"
                ),
                "Bergtour" => array(
                    "category" => "Bergtour",
                    "icon" => "mountain",
                    "icon-png" => "peak2.png"
                ),
                "skitour" => array(
                    "category" => "Skitour",
                    "icon" => "skiing",
                    "icon-png" => "skiing.png"
                ),
                "Paddeln" => array(
                    "category" => "Paddeln",
                    "icon" => "kayaking",
                    "icon-png" => "kayaking2.png"
                ),
                "reisebericht" => array(
                    "category" => "Reisebericht",
                    "icon" => "travel",
                    "icon-png" => "campingcar.png"
                )
            )
        ];

        $result = wp_postmap_load_category_mapping();
        $this->assertEquals($expected['default'], $result['default']);
        $this->assertEquals($expected['mapping'], $result['mapping']);
    }

    /**
     * Test getting category for a post with matching tags.
     */
    public function testGetCatWithMatchingTags()
    {
        expect( 'plugin_dir_path' )
			->once()
			->andReturn( dirname(dirname(dirname(__DIR__))) . '/' );
        
        $arraytagnames = 'Trekk';
        $expected = 'Trekking';

        $result = wp_postmap_get_cat($arraytagnames);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test getting category for a post with multiple matching tags.
     */
    public function testGetCatWithMultipleMatchingTags()
    {
        expect( 'plugin_dir_path' )
			->once()
			->andReturn( dirname(dirname(dirname(__DIR__))) . '/' );

        $arraytagnames = 'bike, hike';
        $expected = 'Bike-Hike';

        $result = wp_postmap_get_cat($arraytagnames);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test getting category for a post with no matching tags.
     */
    public function testGetCatWithNoMatchingTags()
    {
        expect( 'plugin_dir_path' )
			->once()
			->andReturn( dirname(dirname(dirname(__DIR__))) . '/' );

        $arraytagnames = 'unknown, tag';
        $expected = 'Reisebericht';

        $result = wp_postmap_get_cat($arraytagnames);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test getting category for a post with matching tags.
     */
    public function testGetIconWithMatchingTags()
    {
        expect( 'plugin_dir_path' )
			->once()
			->andReturn( dirname(dirname(dirname(__DIR__))) . '/' );
        
        $arraytagnames = 'Trekk';
        $expected = 'hiking';

        $result = wp_postmap_get_icon($arraytagnames);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test getting category for a post with multiple matching tags.
     */
    public function testGetIconWithMultipleMatchingTags()
    {
        expect( 'plugin_dir_path' )
			->once()
			->andReturn( dirname(dirname(dirname(__DIR__))) . '/' );

        $arraytagnames = 'bike, hike';
        $expected = 'bike-hike';

        $result = wp_postmap_get_icon($arraytagnames);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test getting category for a post with no matching tags.
     */
    public function testGetIconWithNoMatchingTags()
    {
        expect( 'plugin_dir_path' )
			->once()
			->andReturn( dirname(dirname(dirname(__DIR__))) . '/' );
            
        $arraytagnames = 'unknown, tag';
        $expected = 'travel';

        $result = wp_postmap_get_icon($arraytagnames);
        $this->assertEquals($expected, $result);
    }
}