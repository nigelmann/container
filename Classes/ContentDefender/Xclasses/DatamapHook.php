<?php

declare(strict_types=1);

namespace B13\Container\ContentDefender\Xclasses;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\ContentDefender\ContainerColumnConfigurationService;
use IchHabRecht\ContentDefender\Hooks\DatamapDataHandlerHook;
use IchHabRecht\ContentDefender\Repository\ContentRepository;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class DatamapHook extends DatamapDataHandlerHook
{

    /**
     * @var ContainerColumnConfigurationService
     */
    protected $containerColumnConfigurationService;

    protected $mapping = [];

    public function __construct(
        ContentRepository $contentRepository = null,
        ContainerColumnConfigurationService $containerColumnConfigurationService = null
    ) {
        $this->containerColumnConfigurationService = $containerColumnConfigurationService ?? GeneralUtility::makeInstance(ContainerColumnConfigurationService::class);
        parent::__construct($contentRepository);
    }

    /**
     * @param DataHandler $dataHandler
     */
    public function processDatamap_beforeStart(DataHandler $dataHandler): void
    {
        if (isset($dataHandler->datamap['tt_content']) && is_array($dataHandler->datamap['tt_content'])) {
            foreach ($dataHandler->datamap['tt_content'] as $id => $values) {
                if (
                    isset($values['tx_container_parent']) &&
                    $values['tx_container_parent'] > 0 &&
                    isset($values['colPos']) &&
                    $values['colPos'] > 0
                ) {
                    if (MathUtility::canBeInterpretedAsInteger($id)) {
                        // proof me
                        $this->mapping[(int)$id] = [
                            'containerId' => (int)$values['tx_container_parent'],
                            'colPos' => (int)$values['colPos']
                        ];
                    }
                    if ($this->containerColumnConfigurationService->isMaxitemsReachedByContainenrId((int)(int)$values['tx_container_parent'], (int)$values['colPos'])) {
                        unset($dataHandler->datamap['tt_content'][$id]);
                        $dataHandler->log(
                            'tt_content',
                            $id,
                            1,
                            0,
                            1,
                            'The command couldn\'t be executed due to reached maxitems configuration',
                            28
                        );
                    }
                }
            }
        }
        parent::processDatamap_beforeStart($dataHandler);
    }

    /**
     * @param array $columnConfiguration
     * @param array $record
     * @return bool
     */
    protected function isRecordAllowedByRestriction(array $columnConfiguration, array $record)
    {
        if (isset($this->mapping[$record['uid']])) {
            $columnConfiguration = $this->containerColumnConfigurationService->override(
                $columnConfiguration,
                $this->mapping[$record['uid']]['containerId'],
                $this->mapping[$record['uid']]['colPos']
            );
        }
        return parent::isRecordAllowedByRestriction($columnConfiguration, $record);
    }

    /**
     * @param array $columnConfiguration
     * @param array $record
     * @return bool
     */
    protected function isRecordAllowedByItemsCount(array $columnConfiguration, array $record)
    {
        if (isset($this->mapping[$record['uid']])) {
            return true;
        }
        return parent::isRecordAllowedByItemsCount($columnConfiguration, $record);
    }
}
