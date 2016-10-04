<?php
//[PHPCOMPRESSOR(remove,start)]
/**
 * Created by PhpStorm.
 * User: VITALYIEGOROV
 * Date: 28.11.15
 * Time: 15:06
 */
namespace samson\activerecord;

use samsonframework\orm\Record;

/**
 * This is emulated classes that should be generated by
 * ORM module. This is needed for IDE and databaseless testing.
 */
if (!class_exists('\samson\activerecord\materialfield', false)) {
    class MaterialField extends Record
    {
        /** @var integer Primary key */
        public $FieldID;

        /** @var bool Internal existence flag */
        public $Active;

        /** @var integer Material identifier */
        public $MaterialID;

        /** @var string Additional field value */
        public $Value;

        /** @var string Additional field value */
        public $numeric_value;

        /** @var string Additional field value */
        public $key_value;

        /** @var string Additional field locale */
        public $locale;
    }
}
//[PHPCOMPRESSOR(remove,end)]
