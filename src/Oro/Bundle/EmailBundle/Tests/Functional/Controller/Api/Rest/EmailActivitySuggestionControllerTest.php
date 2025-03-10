<?php

namespace Oro\Bundle\EmailBundle\Tests\Functional\Controller\Api\Rest;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class EmailActivitySuggestionControllerTest extends WebTestCase
{
    #[\Override]
    protected function setUp(): void
    {
        $this->markTestSkipped('Due to BAP-8365');

        $this->initClient([], self::generateApiAuthHeader());
        $this->loadFixtures(
            [
                'Oro\Bundle\EmailBundle\Tests\Functional\DataFixtures\LoadEmailActivityData',
                'Oro\Bundle\EmailBundle\Tests\Functional\DataFixtures\LoadEmailSuggestionData'
            ]
        );
    }

    public function testGetEntities()
    {
        $url = $this->getUrl(
            'oro_api_get_activity_email_suggestions',
            ['id' => $this->getReference('email_1')->getId()]
        );
        $this->client->jsonRequest('GET', $url);
        $entities = $this->getJsonResponseContent($this->client->getResponse(), 200);

        //2 not assigned users
        $this->assertCount(2, $entities);
    }

    public function testGetEntitiesWithPaging()
    {
        $url = $this->getUrl(
            'oro_api_get_activity_email_suggestions',
            ['id' => $this->getReference('email_1')->getId()]
        );
        $this->client->jsonRequest(
            'GET',
            $url . '?page=2&limit=1',
            [],
            ['HTTP_X-Include' => 'totalCount']
        );
        $response = $this->client->getResponse();
        $entities = $this->getJsonResponseContent($response, 200);
        $this->assertCount(1, $entities);

        //2 not assigned users
        $this->assertEquals(2, $response->headers->get('X-Include-Total-Count'));
    }
}
