<?php

namespace Oro\Bundle\ImportExportBundle\Form\Type;

use Oro\Bundle\ImportExportBundle\Form\Model\ImportData;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportType extends AbstractType
{
    const NAME = 'oro_importexport_import';

    /**
     * @var ProcessorRegistry
     */
    protected $processorRegistry;

    public function __construct(ProcessorRegistry $processorRegistry)
    {
        $this->processorRegistry = $processorRegistry;
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('file', FileType::class);

        $processorChoices = $this->getImportProcessorsChoices($options['entityName']);
        $processorNames = array_values($processorChoices);

        $builder->add(
            'processorAlias',
            ChoiceType::class,
            array_merge(
                [
                    'choices' => $processorChoices,
                    'required' => true,
                    'empty_data' => reset($processorNames)
                ],
                $options['processorAliasOptions']
            )
        );
    }

    /**
     * @param string $entityName
     *
     * @return string[]
     */
    protected function getImportProcessorsChoices(string $entityName): array
    {
        $aliases = $this->processorRegistry->getProcessorAliasesByEntity(
            ProcessorRegistry::TYPE_IMPORT,
            $entityName
        );

        $result = [];
        foreach ($aliases as $alias) {
            $result[$this->generateProcessorLabel($alias)] = $alias;
        }

        return $result;
    }

    protected function generateProcessorLabel(string $alias): string
    {
        return sprintf('oro.importexport.import.%s', $alias);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' =>  ImportData::class,
                'processorAliasOptions' => [],
            ]
        );
        $resolver->setRequired(['entityName']);

        $resolver->setAllowedTypes('entityName', 'string');
        $resolver->setAllowedTypes('processorAliasOptions', 'array');
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
