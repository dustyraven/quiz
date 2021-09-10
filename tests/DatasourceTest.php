<?php declare(strict_types=1);

namespace Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use Quiz\Datasource;

final class DatasourceTest extends TestCase
{
    public function testDecodeJson()
    {
        $this->assertInstanceOf(Datasource::class, new Datasource(''));
    }

    public function testValidateData()
    {
        $datasource = new Datasource('{"userId":1,"answerId":2}');

        $this->assertInstanceOf(Datasource::class, $datasource->decodeJson()->validateData());
    }

    public function testValidateDataInvalidJSON()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Can not decode data.');

        (new Datasource('blah'))->decodeJson()->validateData();
    }

    public function testValidateDataMissingUserId()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User ID is missing.');

        (new Datasource('{"answerId":2}'))->decodeJson()->validateData();
    }

    public function testSetVariables()
    {
        $datasource = (new Datasource('{"userId":1,"answerId":2}'))
            ->decodeJson()
            ->validateData()
            ->setVariables();

        $this->assertSame(1, $datasource->getUserId());
        $this->assertSame(2, $datasource->getAnswerId());
    }
}
