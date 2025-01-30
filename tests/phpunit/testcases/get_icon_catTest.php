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
use function \mvbplugins\helpers\wp_postmap_get_icon_cat as wp_postmap_get_icon_cat;

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
                0 => array(
                    "category" => "Trekking",
                    "icon" => "hiking",
                    "icon-png" => "hiking.png"
                ),
                1 => array(
                    "category" => "Bike-Hike",
                    "icon" => "bike-hike",
                    "icon-png" => "mountainbiking-3.png"
                ),
                2 => array(
                    "category" => "Radfahren",
                    "icon" => "cycling",
                    "icon-png" => "cycling.png"
                ),
                3 => array(
                    "category" => "MTB",
                    "icon" => "MTB",
                    "icon-png" => "MTB.png"
                ),
                4 => array(
                    "category" => "Wandern",
                    "icon" => "hiking",
                    "icon-png" => "hiking2.png"
                ),
                5 => array(
                    "category" => "Bergtour",
                    "icon" => "mountain",
                    "icon-png" => "peak2.png"
                ),
                6 => array(
                    "category" => "Skitour",
                    "icon" => "skiing",
                    "icon-png" => "skiing.png"
                ),
                7 => array(
                    "category" => "Paddeln",
                    "icon" => "kayaking",
                    "icon-png" => "kayaking2.png"
                ),
                8 => array(
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
                    0 => array(
                        "category" => "Trekking",
                        "icon" => "hiking",
                        "icon-png" => "hiking.png"
                    ),
                    1 => array(
                        "category" => "Bike-Hike",
                        "icon" => "bike-hike",
                        "icon-png" => "mountainbiking-3.png"
                    ),
                    2 => array(
                        "category" => "Radfahren",
                        "icon" => "cycling",
                        "icon-png" => "cycling.png"
                    ),
                    3 => array(
                        "category" => "MTB",
                        "icon" => "MTB",
                        "icon-png" => "MTB.png"
                    ),
                    4 => array(
                        "category" => "Wandern",
                        "icon" => "hiking",
                        "icon-png" => "hiking2.png"
                    ),
                    5 => array(
                        "category" => "Bergtour",
                        "icon" => "mountain",
                        "icon-png" => "peak2.png"
                    ),
                    6 => array(
                        "category" => "Skitour",
                        "icon" => "skiing",
                        "icon-png" => "skiing.png"
                    ),
                    7 => array(
                        "category" => "Paddeln",
                        "icon" => "kayaking",
                        "icon-png" => "kayaking2.png"
                    ),
                    8 => array(
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

        $result = wp_postmap_get_icon_cat($arraytagnames, 'category');
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

        $arraytagnames = 'eintrag, bike&hike, test, karte, irgendwas';
        $expected = 'Bike-Hike';

        $result = wp_postmap_get_icon_cat($arraytagnames, 'category');
        $this->assertEquals($expected, $result);
        
        $arraytagnames = 'bike hike';
        $expected = 'Bike-Hike';

        $result = wp_postmap_get_icon_cat($arraytagnames, 'category');
        $this->assertEquals($expected, $result);

        $arraytagnames = 'bike-hike';
        $expected = 'Bike-Hike';

        $result = wp_postmap_get_icon_cat($arraytagnames, 'category');
        $this->assertEquals($expected, $result);

        $arraytagnames = 'Bike&hike';
        $expected = 'Bike-Hike';

        $result = wp_postmap_get_icon_cat($arraytagnames, 'category');
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

        $result = wp_postmap_get_icon_cat($arraytagnames, 'category');
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

        $result = wp_postmap_get_icon_cat($arraytagnames, 'icon');
        $this->assertEquals($expected, $result);

        $arraytagnames = 'Rad';
        $expected = 'cycling';

        $result = wp_postmap_get_icon_cat($arraytagnames, 'icon');
        $this->assertEquals($expected, $result);

        $arraytagnames = 'mTb';
        $expected = 'MTB';

        $result = wp_postmap_get_icon_cat($arraytagnames, 'icon');
        $this->assertEquals($expected, $result);

        $arraytagnames = 'Wander';
        $expected = 'hiking';

        $result = wp_postmap_get_icon_cat($arraytagnames, 'icon');
        $this->assertEquals($expected, $result);

        $arraytagnames = 'Berg';
        $expected = 'mountain';

        $result = wp_postmap_get_icon_cat($arraytagnames, 'icon');
        $this->assertEquals($expected, $result);

        $arraytagnames = 'Paddel';
        $expected = 'kayaking';

        $result = wp_postmap_get_icon_cat($arraytagnames, 'icon');
        $this->assertEquals($expected, $result);

        $arraytagnames = 'Reise';
        $expected = 'travel';

        $result = wp_postmap_get_icon_cat($arraytagnames, 'icon');
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

        $result = wp_postmap_get_icon_cat($arraytagnames, 'icon');
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

        $result = wp_postmap_get_icon_cat($arraytagnames, 'icon');
        $this->assertEquals($expected, $result);
    }
}