<?php

namespace Framelix\Buhax\Storable;

use Framelix\Framelix\Date;
use Framelix\Framelix\Db\Mysql;
use Framelix\Framelix\Storable\StorableExtended;

/**
 * Fixation
 * @property Attachment|null $attachment
 * @property Date $dateFrom
 * @property Date $dateTo
 */
class Fixation extends StorableExtended
{
    /**
     * Get min start and max end date for next fixation
     * @return Date[]
     */
    public static function getNextFixationDateRange(): array
    {
        $firstUnfixedIncome = Mysql::get()->fetchOne(
            "SELECT `date` FROM `" . Income::class . "` WHERE fixation IS NULL ORDER BY `date` ASC LIMIT 1"
        );
        $lastUnfixedIncome = Mysql::get()->fetchOne(
            "SELECT `date` FROM `" . Income::class . "` WHERE fixation IS NULL ORDER BY `date` DESC LIMIT 1"
        );
        $firstUnfixedOutgoing = Mysql::get()->fetchOne(
            "SELECT `date` FROM `" . Outgoing::class . "` WHERE fixation IS NULL ORDER BY `date` ASC LIMIT 1"
        );
        $lastUnfixedOutgoing = Mysql::get()->fetchOne(
            "SELECT `date` FROM `" . Outgoing::class . "` WHERE fixation IS NULL ORDER BY `date` DESC LIMIT 1"
        );
        $lastFixation = Fixation::getByConditionOne(sort: ['-dateTo']);
        if ($lastFixation) {
            $startDate = $lastFixation->dateTo->clone();
            $startDate->dateTime->modify("+ 1 day");
        } else {
            $startDate = Date::min(
                $firstUnfixedIncome,
                $firstUnfixedOutgoing,
                "now"
            );
            if(!$startDate){
                $startDate = Date::create('now');
            }
            $startDate->dateTime->setDayOfMonth(1);
        }
        $lastDate = Date::max(
            $lastUnfixedIncome,
            $lastUnfixedOutgoing,
            'now',
            $startDate
        );
        $lastDate = Date::min(
            $lastDate,
            $startDate->clone()->dateTime->setDate($startDate->dateTime->getYear(), 12, 31)
        );
        return [$startDate, $lastDate];
    }

    /**
     * Is this storable deletable
     * @return bool
     */
    public function isDeletable(): bool
    {
        $nextFixation = self::getByConditionOne('dateFrom > {0}', [$this->dateFrom]);
        if ($nextFixation) {
            return false;
        }
        return true;
    }

    /**
     * Delete from database
     * @param bool $force Force deletion even if isDeletable() is false
     */
    public function delete(bool $force = false): void
    {
        $storables = [Income::class, Outgoing::class, Income::class];
        foreach ($storables as $storableClass) {
            $this->getDb()->update($storableClass, ['fixation' => null], 'fixation = ' . $this);
        }
        $this->attachment?->delete($force);
        parent::delete($force);
    }


}