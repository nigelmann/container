<?php

declare(strict_types=1);
namespace B13\Container\Tests\Functional\Datahandler\Localization;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Tests\Functional\Datahandler\DatahandlerTest;

class LocalizeSortingTest extends DatahandlerTest
{

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \TYPO3\TestingFramework\Core\Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->linkSiteConfigurationIntoTestInstance();
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/sys_language.xml');
    }

    /**
     * @return array
     */
    public function localizeKeepsSortingDataProvider(): array
    {
        return [
            ['cmdmap' => [
                'tt_content' => [
                    4 => ['copyToLanguage' => 1],
                    1 => ['copyToLanguage' => 1]
                ]
            ]],
            ['cmdmap' => [
                'tt_content' => [
                    1 => ['copyToLanguage' => 1],
                    4 => ['copyToLanguage' => 1]
                ]
            ]]
        ];
    }

    /**
     * @test
     * @dataProvider localizeKeepsSortingDataProvider
     */
    public function localizeKeepsSorting(array $cmdmap): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/localize_keeps_sorting.xml');
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        $translatedContainer1 = $this->fetchOneRecord('t3_origuid', 1);
        $translatedChild11 = $this->fetchOneRecord('t3_origuid', 2);
        $translatedChild12 = $this->fetchOneRecord('t3_origuid', 3);
        $translatedContainer2 = $this->fetchOneRecord('t3_origuid', 4);
        $translatedChild21 = $this->fetchOneRecord('t3_origuid', 5);
        self::assertTrue($translatedContainer1['sorting'] < $translatedChild11['sorting'], 'child-1-1 is sorted before container-1');
        self::assertTrue($translatedChild11['sorting'] < $translatedChild12['sorting'], 'child-1-2 is sorted before child-1-1');
        self::assertTrue($translatedChild12['sorting'] < $translatedContainer2['sorting'], 'container-2 is sorted before child-1-2');
        self::assertTrue($translatedContainer2['sorting'] < $translatedChild21['sorting'], 'child-2-1 is sorted before container-2');
    }
}
