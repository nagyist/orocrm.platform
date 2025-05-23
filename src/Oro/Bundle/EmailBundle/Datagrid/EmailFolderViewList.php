<?php

namespace Oro\Bundle\EmailBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Extension\GridViews\AbstractViewsList;
use Oro\Bundle\DataGridBundle\Extension\GridViews\View;
use Oro\Bundle\EmailBundle\Model\FolderType;
use Symfony\Contracts\Translation\TranslatorInterface;

class EmailFolderViewList extends AbstractViewsList
{
    /**
     * @var MailboxChoiceList
     */
    private $mailboxChoiceList;

    public function __construct(TranslatorInterface $translator, MailboxChoiceList $mailboxChoiceList)
    {
        parent::__construct($translator);
        $this->mailboxChoiceList = $mailboxChoiceList;
    }

    #[\Override]
    protected function getViewsList()
    {
        $views = [
            new View(
                $this->translator->trans('oro.email.datagrid.emailfolder.view.inbox'),
                [
                    'folder' => ['value' => [FolderType::INBOX]]
                ]
            ),
            new View(
                $this->translator->trans('oro.email.datagrid.emailfolder.view.sent'),
                [
                    'folder' => ['value' => [FolderType::SENT]]
                ]
            )
        ];

        $choiceList = $this->mailboxChoiceList->getChoiceList();

        foreach ($choiceList as $label => $id) {
            $mailboxLabel = $this->translator->trans('oro.email.datagrid.mailbox.view', ['%mailbox%' => $label]);
            $view = new View(
                $mailboxLabel,
                [
                    'mailbox' => ['value' => $id]
                ]
            );
            $view->setLabel(str_replace('\@', '@', $mailboxLabel));
            $views[] = $view;
        }

        return $views;
    }
}
