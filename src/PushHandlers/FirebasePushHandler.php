<?php

namespace Audentio\LaravelNotifications\PushHandlers;

use App\Core;
use App\Models\UserPushQueue;
use App\Models\UserPushSubscription;
use Audentio\LaravelNotifications\PushResponse;
use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Collection;

class FirebasePushHandler extends AbstractPushHandler
{
    const CACHE_VERSION = '2025-12-02 v1';
    const CACHE_KEY = 'audentioNotifications_fcm_access_token';

    public function getIdentifier(): string
    {
        return 'fcm';
    }

    /**
     * @param  Collection  $userPushQueues
     * @return PushResponse[]
     */
    public function dispatchPushNotifications(Collection $userPushQueues): array
    {
        if (!class_exists(ServiceAccountCredentials::class)) {
            throw new \LogicException('Google Auth library is required for Firebase push notifications. ' .
                'Please install google/auth via composer.');
        }

        $serviceAccount = $this->getServiceAccount();
        $accessToken = $this->getAccessToken();
        $projectId = $serviceAccount['project_id'];
        $pushResponses = [];

        foreach ($userPushQueues as $userPushQueue) {
            $requestPayload = $this->buildUserPushQueuePayload($userPushQueue);
            $pushResponse = new PushResponse(collect([$userPushQueue]));
            try {
                $client = new Client;

                $request = $client->post('https://fcm.googleapis.com/v1/projects/' . $projectId . '/messages:send', [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $accessToken,
                    ],
                    RequestOptions::JSON => [
                        'message' => $requestPayload,
                    ],
                ]);
                $pushResponse->logSuccessId($userPushQueue->id);
            } catch (ClientException $e) {
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                if ($statusCode == 429) {
                    $pushResponse->logDelayPushId($userPushQueue->id);
                } {
                    $pushResponse->logCancelPushId($userPushQueue->id);
                }
            } catch (ServerException $e) {
                $pushResponse->logCancelPushId($userPushQueue->id);
            }
            $pushResponses[] = $pushResponse;
        }

        return $pushResponses;
    }

    protected function buildUserPushQueuePayload(UserPushQueue $userPushQueue): array
    {
        /** @var UserPushSubscription $userPushSubscription */
        $userPushSubscription = $userPushQueue->userPushSubscription;
        $data = $userPushQueue->data;

        return [
            'token' => $userPushSubscription->token,
            'notification' => [
                'title' => 'Notification',
                'body' => $data['message'],
            ],
            'data' => $data,
        ];
    }

    protected function getQueueBatchSize(): int
    {
        return 10;
    }

    private function getAccessToken(): string
    {
        $accessData = $this->getAccessDataFromCache();
        if (!$accessData) {
            $accessData = $this->getNewAccessData();
        }

        return $accessData['access_token'];
    }

    private function getNewAccessData(bool $store = true): array
    {
        $serviceAccount = $this->getServiceAccount();
        $credentials = new ServiceAccountCredentials(
            'https://www.googleapis.com/auth/firebase.messaging',
            $serviceAccount
        );

        $requestTime = time();
        $accessData = $credentials->fetchAuthToken();
        $accessData['expires_at'] = $accessData['expires_in'] + $requestTime;
        $accessData['version'] = static::CACHE_VERSION;
        if ($store) {
            \Cache::put(static::CACHE_KEY, $accessData, $accessData['expires_in'] - 60);
        }

        return $accessData;
    }

    private function getAccessDataFromCache(): ?array
    {
        $accessData = \Cache::get(static::CACHE_KEY);
        if (!$accessData) {
            return null;
        }

        if (empty($accessData['expires_at']) || $accessData['expires_at'] <= time() + 60) {
            return null;
        }

        if (empty($accessData['version']) || $accessData['version'] !== static::CACHE_VERSION) {
            return null;
        }

        return $accessData;
    }

    private function getServiceAccount(): array
    {
        return json_decode(base64_decode(config('audentioNotifications.handler_config.fcm.service_account_base64')), true);
    }
}
