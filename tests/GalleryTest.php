<?php
namespace tests;

use samson\activerecord\dbQuery;
use samsoncms\api\CMS;
use samsoncms\api\Field;
use samsoncms\api\FieldsTable;
use samsoncms\api\Gallery;
use samsoncms\api\Material;
use samsoncms\api\MaterialField;
use samsoncms\api\PodarokQuery;
use samsonframework\orm\Database;
use samsonframework\orm\Query;
use samsonframework\orm\QueryInterface;

require('src/generated/Material.php');
require('src/generated/Field.php');
require('src/generated/MaterialField.php');
require('src/generated/Structure.php');
require('src/generated/StructureField.php');

define('DEFAULT_LOCALE', '');

/**
 * Created by <myslyvyi@samsonos.com>
 * on 12.01.16 at 17:20
 */
class GalleryTest extends \PHPUnit_Framework_TestCase
{
    /** @var QueryInterface */
    protected $query;

	/** Tests init */
	public function setUp()
	{
        \samson\core\Error::$OUTPUT = false;

        // Create Request mock
        $this->query = $this->getMockBuilder('\samsonframework\orm\Query')
            ->disableOriginalConstructor()
            ->getMock();

        $materialfield = $this->getMockBuilder(MaterialField::ENTITY)
            ->disableOriginalConstructor()
            ->getMock();

        $gallery = $this->getMockBuilder(CMS::MATERIAL_IMAGES_RELATION_ENTITY)
            ->disableOriginalConstructor()
            ->getMock();

        $this->query->method('entity')->willReturn($this->query);
        $this->query->method('first')->willReturn($materialfield);
        $this->query->method('exec')->willReturn(array($gallery));
        $this->query->method('where')->willReturn($this->query);
        $this->query->method('count')->willReturn(10);
    }

	public function testCount()
	{
        $gallery = new Gallery($this->query, 11, 47);
        $this->assertEquals(10, $gallery->getCount());
	}

    public function testGetImages()
    {
        $gallery = new Gallery($this->query, 11, 47);
        $this->assertLessThan(count($gallery->getImages()), 0);
    }

    public function testGetImagesPage()
    {
        $gallery = new Gallery($this->query, 11, 47);
        $this->assertLessThan(count($gallery->getImages(1, 2)), 0);
    }
}
