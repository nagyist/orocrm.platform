<?php

namespace Oro\Bundle\MigrationBundle\Tests\Unit\Migration;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use PHPUnit\Framework\TestCase;

class QueryBagTest extends TestCase
{
    public function testBag(): void
    {
        $queries = new QueryBag();
        $queries->addPreQuery('query1');
        $queries->addPreQuery('query2');
        $queries->addPostQuery('query3');
        $queries->addQuery('query4');

        $this->assertEquals(
            ['query1', 'query2'],
            $queries->getPreQueries()
        );
        $this->assertEquals(
            ['query3', 'query4'],
            $queries->getPostQueries()
        );

        $queries->clear();
        $this->assertCount(0, $queries->getPreQueries());
        $this->assertCount(0, $queries->getPostQueries());
    }
}
