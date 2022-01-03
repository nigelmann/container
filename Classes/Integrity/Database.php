<?php

declare(strict_types=1);

namespace B13\Container\Integrity;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Database implements SingletonInterface
{
    private $fields = ['uid', 'pid', 'sys_language_uid', 'CType', 'l18n_parent', 't3_origuid', 'colPos', 'tx_container_parent', 'sorting'];

    /**
     * @return QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        return $queryBuilder;
    }

    /**
     * @param array $cTypes
     * @return array
     */
    public function getTranslatedContainerRecords(array $cTypes): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $stm = $queryBuilder
            ->select(...$this->fields)
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->in(
                    'CType',
                    $queryBuilder->createNamedParameter($cTypes, Connection::PARAM_STR_ARRAY)
                ),
                $queryBuilder->expr()->neq(
                    'l10n_source',
                    $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                )
            )
            ->execute();
        $rows = [];
        while ($result = $stm->fetch()) {
            $rows[$result['uid']] = $result;
        }
        return $rows;
    }

    /**
     * @return array
     */
    public function getTranslatedContainerChildRecords(): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $stm = $queryBuilder
            ->select(...$this->fields)
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->gt(
                    'tx_container_parent',
                    $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->neq(
                    'l10n_source',
                    $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                )
            )
            ->execute();
        $rows = [];
        while ($result = $stm->fetch()) {
            $rows[$result['uid']] = $result;
        }
        return $rows;
    }

    /**
     * @param array $cTypes
     * @return array
     */
    public function getContainerRecords(array $cTypes): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $stm = $queryBuilder
            ->select(...$this->fields)
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->in(
                    'CType',
                    $queryBuilder->createNamedParameter($cTypes, Connection::PARAM_STR_ARRAY)
                ),
                $queryBuilder->expr()->eq(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                )
            )
            ->execute();
        $rows = [];
        while ($result = $stm->fetch()) {
            $rows[$result['uid']] = $result;
        }
        return $rows;
    }

    public function getContainerRecordsFreeMode(array $cTypes): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $stm = $queryBuilder
            ->select(...$this->fields)
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->in(
                    'CType',
                    $queryBuilder->createNamedParameter($cTypes, Connection::PARAM_STR_ARRAY)
                ),
                $queryBuilder->expr()->neq(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'l18n_parent',
                    $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                )
            )
            ->execute();
        $rows = [];
        while ($result = $stm->fetch()) {
            $rows[$result['uid']] = $result;
        }
        return $rows;
    }

    /**
     * @return array
     */
    public function getContainerChildRecords(): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $stm = $queryBuilder
            ->select(...$this->fields)
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->gt(
                    'tx_container_parent',
                    $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                )
            )
            ->execute();
        $rows = [];
        while ($result = $stm->fetch()) {
            $rows[$result['uid']] = $result;
        }
        return $rows;
    }

    public function getContentElementAfter(array $record): ?array
    {
        // todo not required
        $queryBuilder = $this->getQueryBuilder();
        $row = $queryBuilder
            ->select(...$this->fields)
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->gt(
                    'sorting',
                    $queryBuilder->createNamedParameter($record['sorting'], Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter($record['sys_language_uid'], Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'pid',
                    $queryBuilder->createNamedParameter($record['pid'], Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'colPos',
                    $queryBuilder->createNamedParameter($record['colPos'], Connection::PARAM_INT)
                )
            )
            ->orderBy('sorting')
            ->setMaxResults(1)
            ->execute()
            ->fetch();
        if ($row === false) {
            return null;
        }
        return $row;
    }
}
