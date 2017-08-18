<?php

namespace Oro\Bundle\TestFrameworkBundle\Behat\Element;

use Symfony\Component\DomCrawler\Crawler;

class TableHeader extends Element
{
    /**
     * Try to guess header and return column number
     *
     * @param string $headerText Header of table column
     * @return int column number
     */
    public function getColumnNumber($headerText)
    {
        $crawler = new Crawler($this->getHtml());

        $i = 0;

        /** @var \DOMElement $th */
        foreach ($crawler->filter('th') as $th) {
            if (strtolower($th->textContent) === strtolower($headerText)) {
                return $i;
            }

            $i++;
        }

        self::fail(sprintf('Can\'t find link with "%s" header', $headerText));
    }

    /**
     * Checks if table header has such column name
     *
     * @param $columnName
     * @return bool
     */
    public function hasColumn($columnName)
    {
        $crawler = new Crawler($this->getHtml());

        /** @var \DOMElement $th */
        foreach ($crawler->filter('th') as $th) {
            if (strtolower($th->textContent) === strtolower($columnName)) {
                return true;
            }
        }
        return false;
    }
}