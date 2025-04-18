<?php

namespace Oro\Bundle\ImportExportBundle\Configuration;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Import/Export configuration holder
 */
class ImportExportConfiguration extends ParameterBag implements ImportExportConfigurationInterface
{
    const FIELD_ROUTE_OPTIONS = 'routeOptions';
    const FIELD_ENTITY_CLASS = 'entityClass';
    const FIELD_FILE_PREFIX = 'filePrefix';

    const FIELD_EXPORT_JOB_NAME = 'exportJobName';
    const FIELD_EXPORT_PROCESSOR_ALIAS = 'exportProcessorAlias';
    const FIELD_EXPORT_BUTTON_LABEL = 'exportButtonLabel';
    const FIELD_EXPORT_POPUP_TITLE = 'exportPopupTitle';

    const FIELD_EXPORT_TEMPLATE_JOB_NAME = 'exportTemplateJobName';
    const FIELD_EXPORT_TEMPLATE_PROCESSOR_ALIAS = 'exportTemplateProcessorAlias';
    const FIELD_EXPORT_TEMPLATE_BUTTON_LABEL = 'exportTemplateButtonLabel';

    const FIELD_IMPORT_JOB_NAME = 'importJobName';
    const FIELD_IMPORT_PROCESSOR_ALIAS = 'importProcessorAlias';

    const FIELD_IMPORT_ENTITY_LABEL = 'importEntityLabel';
    const FIELD_IMPORT_STRATEGY_TOOLTIP = 'importStrategyTooltip';
    const FIELD_IMPORT_PROCESSORS_TO_CONFIRMATION_MESSAGE = 'importProcessorToConfirmationMessages';
    const FIELD_IMPORT_VALIDATION_JOB_NAME = 'importValidationJobName';
    const FIELD_IMPORT_VALIDATION_BUTTON_LABEL = 'importValidationButtonLabel';
    const FIELD_IMPORT_ADDITIONAL_NOTICES = 'importAdditionalNotices';
    const FIELD_IMPORT_PROCESSOR_TOPIC_NAME = 'importProcessorTopicName';

    #[\Override]
    public function getRouteOptions(): array
    {
        return $this->get(self::FIELD_ROUTE_OPTIONS, []);
    }

    #[\Override]
    public function getEntityClass(): string
    {
        return $this->get(self::FIELD_ENTITY_CLASS, '');
    }

    #[\Override]
    public function getFilePrefix()
    {
        return $this->get(self::FIELD_FILE_PREFIX);
    }

    #[\Override]
    public function getExportJobName()
    {
        return $this->get(self::FIELD_EXPORT_JOB_NAME);
    }

    #[\Override]
    public function getExportProcessorAlias()
    {
        return $this->get(self::FIELD_EXPORT_PROCESSOR_ALIAS);
    }

    #[\Override]
    public function getExportButtonLabel()
    {
        return $this->get(self::FIELD_EXPORT_BUTTON_LABEL);
    }

    #[\Override]
    public function getExportPopupTitle()
    {
        return $this->get(self::FIELD_EXPORT_POPUP_TITLE);
    }

    #[\Override]
    public function getExportTemplateJobName()
    {
        return $this->get(self::FIELD_EXPORT_TEMPLATE_JOB_NAME);
    }

    #[\Override]
    public function getExportTemplateProcessorAlias()
    {
        return $this->get(self::FIELD_EXPORT_TEMPLATE_PROCESSOR_ALIAS);
    }

    #[\Override]
    public function getImportJobName()
    {
        return $this->get(self::FIELD_IMPORT_JOB_NAME);
    }

    #[\Override]
    public function getImportProcessorAlias()
    {
        return $this->get(self::FIELD_IMPORT_PROCESSOR_ALIAS);
    }

    #[\Override]
    public function getImportEntityLabel()
    {
        return $this->get(self::FIELD_IMPORT_ENTITY_LABEL);
    }

    #[\Override]
    public function getImportValidationJobName()
    {
        return $this->get(self::FIELD_IMPORT_VALIDATION_JOB_NAME);
    }

    #[\Override]
    public function getImportValidationButtonLabel()
    {
        return $this->get(self::FIELD_IMPORT_VALIDATION_BUTTON_LABEL);
    }

    #[\Override]
    public function getImportStrategyTooltip()
    {
        return $this->get(self::FIELD_IMPORT_STRATEGY_TOOLTIP);
    }

    #[\Override]
    public function getImportProcessorsToConfirmationMessage(): array
    {
        return $this->get(self::FIELD_IMPORT_PROCESSORS_TO_CONFIRMATION_MESSAGE, []);
    }

    #[\Override]
    public function getImportAdditionalNotices(): array
    {
        return $this->get(self::FIELD_IMPORT_ADDITIONAL_NOTICES, []);
    }

    public function getImportProcessorTopicName(): ?string
    {
        return $this->get(self::FIELD_IMPORT_PROCESSOR_TOPIC_NAME);
    }
}
