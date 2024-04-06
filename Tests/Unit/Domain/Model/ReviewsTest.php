<?php

declare(strict_types=1);

namespace GdprExtensionsCom\GdprExtensionsComWm\Tests\Unit\Domain\Model;

use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case
 */
class ReviewsTest extends UnitTestCase
{
    /**
     * @var \GdprExtensionsCom\GdprExtensionsComWm\Domain\Model\Reviews|MockObject|AccessibleObjectInterface
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->getAccessibleMock(
            \GdprExtensionsCom\GdprExtensionsComWm\Domain\Model\Reviews::class,
            ['dummy']
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getReviewIdReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getReviewId()
        );
    }

    /**
     * @test
     */
    public function setReviewIdForStringSetsReviewId(): void
    {
        $this->subject->setReviewId('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('reviewId'));
    }

    /**
     * @test
     */
    public function getReviewerProfilePhotoUrlReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getReviewerProfilePhotoUrl()
        );
    }

    /**
     * @test
     */
    public function setReviewerProfilePhotoUrlForStringSetsReviewerProfilePhotoUrl(): void
    {
        $this->subject->setReviewerProfilePhotoUrl('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('reviewerProfilePhotoUrl'));
    }

    /**
     * @test
     */
    public function getReviewerDisplayNameReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getReviewerDisplayName()
        );
    }

    /**
     * @test
     */
    public function setReviewerDisplayNameForStringSetsReviewerDisplayName(): void
    {
        $this->subject->setReviewerDisplayName('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('reviewerDisplayName'));
    }

    /**
     * @test
     */
    public function getStarRatingReturnsInitialValueForInt(): void
    {
        self::assertSame(
            0,
            $this->subject->getStarRating()
        );
    }

    /**
     * @test
     */
    public function setStarRatingForIntSetsStarRating(): void
    {
        $this->subject->setStarRating(12);

        self::assertEquals(12, $this->subject->_get('starRating'));
    }

    /**
     * @test
     */
    public function getCommentReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getComment()
        );
    }

    /**
     * @test
     */
    public function setCommentForStringSetsComment(): void
    {
        $this->subject->setComment('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('comment'));
    }

    /**
     * @test
     */
    public function getContentHashReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getContentHash()
        );
    }

    /**
     * @test
     */
    public function setContentHashForStringSetsContentHash(): void
    {
        $this->subject->setContentHash('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('contentHash'));
    }

    /**
     * @test
     */
    public function getRootPidReturnsInitialValueForInt(): void
    {
        self::assertSame(
            0,
            $this->subject->getRootPid()
        );
    }

    /**
     * @test
     */
    public function setRootPidForIntSetsRootPid(): void
    {
        $this->subject->setRootPid(12);

        self::assertEquals(12, $this->subject->_get('rootPid'));
    }
}
