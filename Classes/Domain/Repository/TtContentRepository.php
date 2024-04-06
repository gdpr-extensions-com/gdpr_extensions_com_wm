<?php

declare(strict_types=1);

namespace GdprExtensionsCom\GdprExtensionsComWm\Domain\Repository;


/**
 * This file is part of the "apiconnect" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2023 
 */

/**
 * The repository for Reviews
 */
class TtContentRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
   protected $objectType = '\GdprExtensionsCom\GdprExtensionsComWm\Domain\Model\Ttcontent';
}