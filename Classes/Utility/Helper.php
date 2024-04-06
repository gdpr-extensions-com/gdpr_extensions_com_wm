<?php

declare(strict_types=1);

namespace GdprExtensionsCom\GdprExtensionsComWm\Utility;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;

class Helper
{
    //   public function __construct(
//     private readonly RequestFactory $requestFactory,
    // ) {
    // }

    /**
     * getRootPage.
     *
     * @return (int))
     */
    public function getRootPage($pageUid)
    {
        $page = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(RootlineUtility::class, $pageUid);
        $rootLines = $page->get();

        if (!empty($rootLines)) {
            foreach ($rootLines as $rootLine) {
                if (!empty($rootLine['is_siteroot']) && $rootLine['is_siteroot']) {
                    return $rootLine['uid'];
                }
            }
        }

        return 0;
    }

  public function getConnectionState($rootPid)
  {
  }

  public function itemsProcFunc(&$params): void
  {
      $params['default'] = 'val1';
      var_dump("I'm here");
  }

  // public $configTable = 'tx_goreview_config';
  // public const REDIRECT_URL = TYPO3_GAUTH_REDIRECT_URL;
  // public const GOOGLE_VERIFY_TOKEN_URL = 'https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=';
  // public const ATTEMPTS = 3;
  protected \Google\Client|null $googleClient = null;

    /**
     * ifAccessTokenIsValid.
     *
     * @param string $access_token
     */
    public function ifAccessTokenIsValid($oken): bool
    {
        $valid = true;
        $additionalOptions = [
            'headers' => ['Cache-Control' => 'no-cache'],
            'allow_redirects' => false,
        ];
        // Get a PSR-7-compliant response object
        try {
            $response = $this->requestFactory->request(
                self::GOOGLE_VERIFY_TOKEN_URL.$this->getAccessTokenOfLoggedinUser()['access_token'],
                'GET',
                $additionalOptions
            );
        } catch (\GuzzleHttp\Exception\ClientException $ex) {
            $valid = false;
        }

        return $valid;
    }

    /**
     * getAccessTokenFromCurrentRootPage.
     */
    public function getAccessTokenFromCurrentRootPage($rootPid): string|bool
    {
        $token = [];
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_gdprextensionscomwm_domain_model_connections');
        $token = $queryBuilder
        ->select('apitoken')
        ->from('tx_gdprextensionscomwm_domain_model_connections')
        ->where(
            $queryBuilder->expr()->eq('rootpid', $this->getRootPage($rootPid))
        )
        ->execute()
        ->fetchAssociative();

        return isset($token['apitoken']) ? $token['apitoken'] : false;
    }

    /**
     * saveAccessToken.
     */
    public function saveAccessToken(array $token): void
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->configTable);
        if ($this->ifUserHasAccessTokenAlready()) {
            // update the token
            $queryBuilder
            ->update($this->configTable)
            ->where(
                $queryBuilder->expr()->eq('fe_user', $queryBuilder->createNamedParameter($GLOBALS['TSFE']->fe_user->user['uid']))
            )
            ->set('access_token', $token['access_token'])
            ->executeStatement();
        } else {
            // insert new token
            $affectedRows = $queryBuilder
            ->insert($this->configTable)
            ->values([
                'access_token' => $token['access_token'],
                'refresh_token' => $token['refresh_token'],
                'fe_user' => $GLOBALS['TSFE']->fe_user->user['uid'],
            ])
            ->execute();
        }
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function removeAccessToken(): void
    {
        if ($this->ifUserHasAccessTokenAlready()) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->configTable);
            $queryBuilder->delete($this->configTable)
                ->where(
                    $queryBuilder->expr()->eq('fe_user', $GLOBALS['TSFE']->fe_user->user['uid'])
                )
                ->executeStatement();
        }
    }

    public function isAuthorized(): bool
    {
        return (bool) $GLOBALS['TSFE']->fe_user->user;
    }

    /**
     * @throws \Google\Exception
     */
    public function getAccountsWithPlaces(): array
    {
        $result = [];
        if ($this->isAuthorized() && $this->ifUserHasAccessTokenAlready()) {
            $accounts = $this->getAccounts();
            foreach ($accounts as $account) {
                $result[] = [
                    'name' => $account->getName(),
                    'accountName' => $account->getAccountName(),
                    'locations' => $this->getLocationsByAccountName($account->getName())['locations'],
                ];
            }
        }

        return $result;
    }

    /**
     * ifUserHasAccessTokenAlready.
     */
    public function ifUserHasAccessTokenAlready(): bool
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->configTable);
        $count = $queryBuilder
        ->count('uid')
        ->from($this->configTable)
        ->where(
            $queryBuilder->expr()->eq('fe_user', $queryBuilder->createNamedParameter($GLOBALS['TSFE']->fe_user->user['uid']))
        )
        ->execute()
        ->fetchOne();

        return $count ? true : false;
    }

    public function updateToken(): bool
    {
        $token = $this->getAccessTokenOfLoggedinUser();
        if (!$this->ifAccessTokenIsValid($token['access_token'])) {
            $client = new \Google\Client();
            $client->setAuthConfig(GeneralUtility::getFileAbsFileName('EXT:go_review/Resources/Private/Config/client_credentials.json'));
            $client->addScope('https://www.googleapis.com/auth/business.manage');
            $client->setRedirectUri(self::REDIRECT_URL);
            $client->fetchAccessTokenWithRefreshToken($token['refresh_token']);
            $this->saveAccessToken($client->getAccessToken());
        }

        return true;
    }

    /**
     * @throws \Google\Exception
     */
    public function initClient(): void
    {
        $token = $this->getAccessTokenOfLoggedinUser();
        $this->googleClient = new \Google\Client();
        $this->googleClient->setAuthConfig(GeneralUtility::getFileAbsFileName('EXT:go_review/Resources/Private/Config/client_credentials.json'));
        $this->googleClient->addScope('https://www.googleapis.com/auth/business.manage');
        $this->googleClient->setAccessToken($token);
    }

    /**
     * @return \Google\Service\MyBusinessAccountManagement\Account[]|null
     *
     * @throws \Google\Exception
     */
    public function getAccounts(int $attempts = 0): null|array
    {
        try {
            $this->initClient();
            $service = new \Google\Service\MyBusinessAccountManagement($this->googleClient);

            return $service->accounts->listAccounts()->getAccounts();
        } catch (\Google\Service\Exception $exception) {
            if ($attempts < self::ATTEMPTS && $exception->getCode() === 401) {
                $this->updateToken();

                return $this->getAccounts($attempts + 1);
            }

            return null;
        }
    }

    /**
     * @return mixed|null
     *
     * @throws \Google\Exception
     */
    public function getLocationsByAccountName(string $name, int $attempts = 0)
    {
        try {
            $this->initClient();
            $client = $this->googleClient->authorize();

            return json_decode($client->request('GET',
                'https://mybusinessbusinessinformation.googleapis.com/v1/'.$name.'/locations?read_mask=name,title',
            )->getBody()->getContents(), true);
        } catch (\Google\Service\Exception $exception) {
            if ($attempts < self::ATTEMPTS && $exception->getCode() === 401) {
                $this->updateToken();

                return $this->getLocationsByAccountName($name, $attempts + 1);
            }

            return null;
        }
    }

    /**
     * @return mixed|null
     *
     * @throws \Google\Exception
     */
    public function getReviewsByAccountAndLocationName(string $accountName, string $locationName, int $attempts = 0)
    {
        try {
            $this->initClient();
            $client = $this->googleClient->authorize();

            return json_decode($client->request('GET',
                'https://mybusiness.googleapis.com/v4/'.$accountName.'/'.$locationName.'/reviews',
            )->getBody()->getContents(), true);
        } catch (\Google\Service\Exception $exception) {
            if ($attempts < self::ATTEMPTS && $exception->getCode() === 401) {
                $this->updateToken();

                return $this->getReviewsByAccountAndLocationName($accountName, $locationName, $attempts + 1);
            }

            return null;
        }
    }
}
