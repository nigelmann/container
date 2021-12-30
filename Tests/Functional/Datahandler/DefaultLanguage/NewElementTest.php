<?php

declare(strict_types=1);
namespace B13\Container\Tests\Functional\Datahandler\DefaultLanguage;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Tests\Functional\Datahandler\DatahandlerTest;
use TYPO3\CMS\Core\Utility\StringUtility;

class NewElementTest extends DatahandlerTest
{

    /**
     * @test
     */
    public function newElementAfterContainerSortElementAfterLastChild(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/new_element_after_container.xml');
        $newId = StringUtility::getUniqueId('NEW');
        $datamap = [
            'tt_content' => [
                $newId => [
                    'pid' => -1
                ]
            ]
        ];
        $this->dataHandler->start($datamap, [], $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();

        $newRecord = $this->fetchOneRecord('uid', 3);
        $lastChildInContainer = $this->fetchOneRecord('uid', 2);
        self::assertTrue($newRecord['sorting'] > $lastChildInContainer['sorting'], 'new element is not sorted after last child in container');
    }
}
