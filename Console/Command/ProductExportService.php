<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\MintSoft\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SoftCommerce\MintSoft\Api\ProductExportSubmitManagementInterface;

/**
 * Class ProductExportService
 * @package SoftCommerce\MintSoft\Console\Command
 */
class ProductExportService extends Command
{
    const COMMAND_NAME = 'softcommerce_mintsoft:product_export';

    const ID_FILTER = 'id';

    /**
     * @var State
     */
    private $_appState;

    /**
     * @var ProductExportSubmitManagementInterface
     */
    private $_productExportManagement;

    /**
     * ProductExportService constructor.
     * @param State $appState
     * @param ProductExportSubmitManagementInterface $productExportManagement
     * @param string|null $name
     */
    public function __construct(
        State $appState,
        ProductExportSubmitManagementInterface $productExportManagement,
        string $name = null
    ) {
        $this->_appState = $appState;
        $this->_productExportManagement = $productExportManagement;
        parent::__construct($name);
    }

    /**
     * Configure
     */
    protected function configure()
    {
        $this->setName('softcommerce_mintsoft:product_export')
            ->setDescription('Exports product to MintSoft.')
            ->setDefinition([
                new InputOption(
                    self::ID_FILTER,
                    '-i',
                    InputOption::VALUE_REQUIRED,
                    'ID Filter'
                )
            ]);

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     * @throws LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_appState->setAreaCode(Area::AREA_ADMINHTML);

        if ($idFilter = $input->getOption(self::ID_FILTER)) {
            $ids = explode(',', str_replace(' ', '', $idFilter));
            $result = [];
            foreach ($ids as $id) {
                $result[$id] = null;
            }
            $this->_productExportManagement->setSearchCriteria($result);
            $output->writeln(sprintf('<info>Exporting product by ID(s) %s.</info>', $idFilter));
        } else {
            $output->writeln(sprintf('<info>Exporting products.</info>'));
        }

        $this->_productExportManagement->execute();

        if ($response = $this->_productExportManagement->getResponse()) {
            print_r($response);
        }

        if ($errors = $this->_productExportManagement->getErrors()) {
            print_r($errors);
        }

        $output->writeln('<info>Done.</info>');

        return;
    }
}
