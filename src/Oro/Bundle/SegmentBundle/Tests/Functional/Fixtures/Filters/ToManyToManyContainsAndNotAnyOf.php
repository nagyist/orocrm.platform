<?php

namespace Oro\Bundle\SegmentBundle\Tests\Functional\Fixtures\Filters;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\QueryDesignerBundle\QueryDesigner\QueryDefinitionUtil;
use Oro\Bundle\SegmentBundle\Entity\Segment;
use Oro\Bundle\SegmentBundle\Entity\SegmentType;
use Oro\Bundle\UserBundle\Entity\Email;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * @link https://magecore.atlassian.net/browse/BAP-11180
 * ?focusedCommentId=134866&page=com.atlassian.jira.plugin.system.issuetabpanels:comment-tabpanel#comment-134866
 *
 * STR2
 */
class ToManyToManyContainsAndNotAnyOf implements FixtureInterface
{
    #[\Override]
    public function assert(\PHPUnit\Framework\Assert $assertions, array $actualData): void
    {
        $assertions->assertCount(1, $actualData);
        $assertions->assertEquals('b', $actualData[0]['c1']);
    }

    #[\Override]
    public function createData(EntityManagerInterface $em): void
    {
        $organization = $em
            ->getRepository(Organization::class)
            ->findOneBy([]);

        $businessUnitA = (new BusinessUnit())
            ->setName('a')
            ->setOrganization($organization);
        $businessUnitB = (new BusinessUnit())
            ->setName('b')
            ->setOrganization($organization);

        $user1 = (new User())
            ->setUsername('u1')
            ->setEmail('u1@example.com')
            ->setPassword('u1')
            ->addEmail(
                (new Email())
                    ->setEmail('email-to-check1@mail.com')
            )
            ->addEmail(
                (new Email())
                    ->setEmail('other-check@mail.com')
            )
            ->addEmail(
                (new Email())
                    ->setEmail('account-which-has-contact-with-such-email-shouldnt-appear@mail.com')
            )
            ->setBusinessUnits(new ArrayCollection([
                $businessUnitA,
            ]))
            ->setOrganization($organization);
        $user2 = (new User())
            ->setUsername('u2')
            ->setEmail('u2@example.com')
            ->setPassword('u2')
            ->addEmail(
                (new Email())
                    ->setEmail('email-to-check2@mail.com')
            )
            ->setBusinessUnits(new ArrayCollection([
                $businessUnitA,
                $businessUnitB,
            ]))
            ->setOrganization($organization);

        $em->persist($businessUnitA);
        $em->persist($businessUnitB);
        $em->persist($user1);
        $em->persist($user2);
    }

    #[\Override]
    public function createSegment(EntityManagerInterface $em): Segment
    {
        $organization = $em
            ->getRepository(Organization::class)
            ->findOneBy([]);

        $segment = (new Segment())
            ->setName('testing-segment')
            ->setOrganization($organization)
            ->setType(
                $em
                    ->getRepository(SegmentType::class)
                    ->findOneBy(['name' => SegmentType::TYPE_DYNAMIC])
            )
            ->setEntity(BusinessUnit::class)
            ->setDefinition(QueryDefinitionUtil::encodeDefinition([
                'filters' => [
                    [
                        'columnName' => 'users+Oro\Bundle\UserBundle\Entity\User::'
                            . 'emails+Oro\Bundle\UserBundle\Entity\Email::email',
                        'criterion' => [
                            'filter' => 'string',
                            'data' => [
                               'value' => 'email-to-check',
                               'type' => '1'
                            ],
                        ],
                    ],
                    'AND',
                    [
                        'columnName' => 'users+Oro\Bundle\UserBundle\Entity\User::'
                            . 'emails+Oro\Bundle\UserBundle\Entity\Email::email',
                        'criterion' => [
                            'filter' => 'string',
                            'data' => [
                                'value' => 'account-which-has-contact-with-such-email-shouldnt-appear@mail.com',
                                'type' => '7'
                            ],
                        ],
                    ],
                ],
                'columns' => [
                    [
                       'name' => 'name',
                       'label' => 'Account name',
                       'sorting' => '',
                       'func' => null
                    ],
                 ],
            ]));

        $em->persist($segment);

        return $segment;
    }
}
