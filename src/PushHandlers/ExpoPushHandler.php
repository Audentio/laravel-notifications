<?php

namespace Audentio\LaravelNotifications\PushHandlers;

use App\Models\UserPushQueue;
use App\Models\UserPushSubscription;
use Audentio\LaravelNotifications\PushResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Collection;

class ExpoPushHandler extends AbstractPushHandler
{
    const EXPO_API_URL = 'https://exp.host/--/api/v2/push/send';

    public function getIdentifier(): string
    {
        return 'expo';
    }

    public function dispatchPushNotifications(Collection $userPushQueues): PushResponse
    {
        list($idMap, $requestPayload) = $this->buildRequestPayload($userPushQueues);

        try {
            $client = new Client;

            $response = $client->post(static::EXPO_API_URL, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                RequestOptions::JSON => $requestPayload,
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            // TODO log to Sentry
        } catch (ServerException $e) {
            $response = $e->getResponse();
            // TODO log to Sentry
        } catch (\Throwable $e) {
            $response = null;
        }

        $pushResponse = new PushResponse($userPushQueues);

        if ($response && $response->getStatusCode() === 200) {
            $data = json_decode($response->getBody()->getContents());
            if (!isset($data->data)) {
                // This should never be possible, but just in case it should be handled.
                $pushResponse->logDelayPushIds($idMap);
            } else {
                foreach ($data->data as $key=>$push) {
                    $id = $idMap[$key];
                    if ($push->status === 'ok') {
                        $pushResponse->logSuccessId($id);
                        continue;
                    }

                    if (!isset($push->details->error)) {
                        $pushResponse->logDelayPushId($id);
                        continue;
                    }

                    $error = $push->details->error;
                    switch($error) {
                        case 'DeviceNotRegistered':
                            $pushResponse->logCancelPushSubscriptionId($id);
                            break;

                        case 'MismatchSenderId':
                        case 'MessageTooBig':
                            $pushResponse->logCancelPushId($id);
                            break;

                        case 'MessageRateExceeded':
                            $pushResponse->logDelayPushId($id);
                            break;

                        default: $pushResponse->logCancelPushId($id);
                    }
                }
            }
        } else {
            $pushResponse->logDelayPushIds($idMap);
        }

        return $pushResponse;
    }

    protected function buildRequestPayload(Collection $userPushQueues): array
    {
        $ids = [];
        $payload = [];
        /** @var UserPushQueue $userPushQueue */
        foreach ($userPushQueues as $userPushQueue) {
            $ids[] = $userPushQueue->id;
            $payload[] = $this->buildUserPushQueuePayload($userPushQueue);
        }

        return [$ids, $payload];
    }

    protected function buildUserPushQueuePayload(UserPushQueue $userPushQueue): array
    {
        /** @var UserPushSubscription $userPushSubscription */
        $userPushSubscription = $userPushQueue->userPushSubscription;

        return array_merge($userPushQueue->data, [
            'to' => $userPushSubscription->token,
        ]);
    }

    protected function getQueueBatchSize(): int
    {
        return 10;
    }
}