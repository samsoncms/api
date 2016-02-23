<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 23.02.16 at 16:22
 */
namespace samsoncms\api\generator;

/**
 * API Controller generation
 * @package samsoncms\api\generator
 */
class API
{
    public function __construct()
    {

    }

    /**
     * GET http://www.example.com/customers/
     */
    public function generateListEntity()
    {

    }

    /**
     * One-to-many relation
     * GET http://www.example.com/customers/{parentID}/{relatedEntityName} - 200
     */
    public function generateListRelatedEntity()
    {

    }

    /**
     * POST http://www.example.com/customers/ - 201, redirect to /customers/{createdID}
     * POST http://www.example.com/customers/ - 204, if content is wrong
     * POST http://www.example.com/customers/{ID} - 404, if does not exists
     * POST http://www.example.com/customers/{ID} - 409 if already exists
     */
    public function generateCreateEntity()
    {

    }

    /**
     * POST http://www.example.com/customers/{parentID}/{relatedEntityName} - 201, redirect to /customers/{parentID}
     * POST http://www.example.com/customers/{parentID}/{relatedEntityName} - 404, if does not exists
     */
    public function generateCreateRelatedEntity()
    {

    }

    /**
     * GET http://www.example.com/customers/{customerID}
     */
    public function generateReadEntity()
    {

    }

    /**
     * PUT http://www.example.com/customers/{customerID} - 200, if ok
     * PUT http://www.example.com/customers/{customerID} - 204, if content is wrong
     * PUT http://www.example.com/customers/{customerID} - 404, if id not found
     */
    public function generateUpdateEntity()
    {

    }

    /**
     * DELETE http://www.example.com/customers/{customerID} - 200, if ok
     * DELETE http://www.example.com/customers/{customerID} - 404, if id not found
     */
    public function generateDeleteEntity()
    {

    }
}
