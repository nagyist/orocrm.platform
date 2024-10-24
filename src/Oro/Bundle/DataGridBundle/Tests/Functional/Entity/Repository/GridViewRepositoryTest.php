<?php

namespace Oro\Bundle\DataGridBundle\Tests\Functional\Entity\Repository;

use Oro\Bundle\DataGridBundle\Entity\GridView;
use Oro\Bundle\DataGridBundle\Entity\Repository\GridViewRepository;
use Oro\Bundle\DataGridBundle\Tests\Functional\DataFixtures\LoadGridViewData;
use Oro\Bundle\DataGridBundle\Tests\Functional\DataFixtures\LoadGridViewUserData;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;

class GridViewRepositoryTest extends AbstractDataGridRepositoryTest
{
    /** @var GridViewRepository */
    protected $repository;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([LoadGridViewUserData::class, LoadUser::class]);
        $this->repository = self::getContainer()->get('doctrine')->getRepository(GridView::class);
    }

    #[\Override]
    protected function getUserReference(): string
    {
        return LoadUser::USER;
    }

    public function testFindGridViews()
    {
        $user = $this->getUser();
        $views = $this->repository->findGridViews($this->aclHelper, $user, 'testing-grid');

        $this->assertCount(2, $views);
        $this->assertGridViewExists($this->getReference(LoadGridViewData::GRID_VIEW_2), $views);
        $this->assertGridViewExists($this->getReference(LoadGridViewData::GRID_VIEW_3), $views);
    }

    public function testFindDefaultGridView()
    {
        $user = $this->getUser();
        $view = $this->repository->findDefaultGridView($this->aclHelper, $user, 'testing-grid');

        $this->assertNotNull($view);
        $this->assertGridViewExists($this->getReference(LoadGridViewData::GRID_VIEW_3), [$view]);
    }

    public function testFindDefaultGridViewsCheckOwner()
    {
        $user = $this->getUser();
        $gridView = $this->getReference(LoadGridViewData::GRID_VIEW_1);
        $views = $this->repository->findDefaultGridViews($this->aclHelper, $user, $gridView, true);

        $this->assertCount(1, $views);
        $this->assertGridViewExists($this->getReference(LoadGridViewData::GRID_VIEW_3), $views);
    }

    public function testFindDefaultGridViewsWithoutCheckOwner()
    {
        $user = $this->getUser();
        $gridView = $this->getReference(LoadGridViewData::GRID_VIEW_1);
        $views = $this->repository->findDefaultGridViews($this->aclHelper, $user, $gridView, false);

        $this->assertCount(1, $views);
        $this->assertGridViewExists($this->getReference(LoadGridViewData::GRID_VIEW_3), $views);
    }
}
