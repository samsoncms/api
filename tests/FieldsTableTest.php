<?php
namespace tests;

use samsoncms\api\Field;
use samsoncms\api\FieldsTable;
use samsoncms\api\Material;
use samsoncms\api\MaterialField;
use samsonframework\orm\QueryInterface;

require('src/generated/Material.php');
require('src/generated/Field.php');
require('src/generated/MaterialField.php');
require('src/generated/Structure.php');
require('src/generated/StructureField.php');

/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>
 * on 25.11.14 at 16:42
 */
class FieldsTableTest extends \PHPUnit_Framework_TestCase
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
	}

	public function testCreate()
	{
        $materialField = new MaterialField();
        $materialField[MaterialField::F_PRIMARY] = 1;
        $materialField[Field::F_PRIMARY] = 1;
        $materialField[Material::F_PRIMARY] = 1;
        $materialField[MaterialField::F_VALUE] = '1';
        $materialField[MaterialField::F_NUMERIC] = '1';
        $materialField[MaterialField::F_KEY] = '1';
        $materialField[MaterialField::F_DELETION] = true;

        $materialField2 = new MaterialField();
        $materialField2[MaterialField::F_PRIMARY] = 2;
        $materialField2[Field::F_PRIMARY] = 2;
        $materialField2[Material::F_PRIMARY] = 2;
        $materialField2[MaterialField::F_VALUE] = '2';
        $materialField2[MaterialField::F_NUMERIC] = '2';
        $materialField2[MaterialField::F_KEY] = '2';
        $materialField2[MaterialField::F_DELETION] = true;

        $this->query
            ->method('exec')
            ->willReturn(array($materialField, $materialField2));

//		$table = new FieldsTable($this->query, 1, 1);
	}
}
