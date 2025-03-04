<?php

namespace Oro\Bundle\OrganizationBundle\Tests\Functional\Grid;

use Oro\Bundle\DataGridBundle\Datagrid\ManagerInterface;
use Oro\Bundle\DataGridBundle\Extension\Action\ActionExtension;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class GridTest extends WebTestCase
{
    #[\Override]
    protected function setUp(): void
    {
        $this->initClient(
            [],
            array_merge($this->generateBasicAuthHeader(), ['HTTP_X-CSRF-Header' => 1])
        );
    }

    /**
     * @dataProvider userSubGridNotContainActionsAndMassActionsProvider
     */
    public function testUserSubGridNotContainActionsAndMassActions(string $gridName, array $gridParams)
    {
        /** @var ManagerInterface $datagridManager */
        $datagridManager = $this->client->getContainer()->get('oro_datagrid.datagrid.manager');
        $datagrid = $datagridManager->getDatagridByRequestParams($gridName, $gridParams);
        $massActions = $datagrid->getConfig()->offsetGetOr(MassActionExtension::ACTION_KEY, []);
        $this->assertEmpty(
            $massActions,
            sprintf(
                'Next mass actions present at user sub-datagrids: "%s", but shouldn\'t!',
                implode('", "', array_keys($massActions))
            )
        );

        $nonApplicableActions = array_keys(
            array_intersect_key(
                array_flip(
                    [
                        'reset_password',
                        'user_activate',
                        'user_disable',
                        'user_enable'
                    ]
                ),
                $datagrid->getConfig()->offsetGetOr(ActionExtension::ACTION_KEY, [])
            )
        );

        $this->assertEmpty(
            $nonApplicableActions,
            'Non-applicable actions exist at datagrid !'
        );
    }

    public function userSubGridNotContainActionsAndMassActionsProvider(): array
    {
        return [
            "Grid 'bu-update-users-grid'" => [
                'gridName' => 'bu-update-users-grid',
                'gridParams' => [

                ],
            ],
            "Grid 'bu-view-users-grid'" => [
                'gridName' => 'bu-view-users-grid',
                'gridParams' => [
                    'business_unit_id' => null,
                ],
            ],
        ];
    }
}
