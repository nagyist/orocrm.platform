<?php

namespace Oro\Bundle\LocaleBundle\DoctrineExtensions\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateType;

/**
 * Converts date to php format and database format
 */
class UTCDateType extends DateType
{
    /** @var null| \DateTimeZone  */
    private static $utc = null;

    #[\Override]
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        /** @var \DateTime $value */
        $value->setTimeZone((self::$utc) ? self::$utc : (self::$utc = new \DateTimeZone('UTC')));

        return parent::convertToDatabaseValue($value, $platform);
    }

    #[\Override]
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        $val = \DateTime::createFromFormat(
            '!' . $platform->getDateFormatString(),
            $value,
            (self::$utc) ? self::$utc : (self::$utc = new \DateTimeZone('UTC'))
        );

        if (!$val) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }

        $errors = $val->getLastErrors();
        // date was parsed to completely not valid value
        if (false !== $errors && $errors['warning_count'] > 0 && (int)$val->format('Y') < 0) {
            return null;
        }

        return $val;
    }
}
