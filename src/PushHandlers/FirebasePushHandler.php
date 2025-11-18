<?php

namespace Audentio\LaravelNotifications\PushHandlers;

use App\Core;
use App\Models\UserPushQueue;
use App\Models\UserPushSubscription;
use Audentio\LaravelNotifications\PushResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Collection;

class FirebasePushHandler extends AbstractPushHandler
{
    const FCM_API_URL = 'https://fcm.googleapis.com/fcm/send';

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
        $pushResponses = [];
        foreach ($userPushQueues as $userPushQueue) {
            $requestPayload = $this->buildUserPushQueuePayload($userPushQueue);
            try {
                $client = new Client;

                $response = $client->post(static::FCM_API_URL, [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'Authorization' => 'key=' . config('audentioNotifications.handler_config.fcm.api_key'),
                    ],
                    RequestOptions::JSON => $requestPayload,
                ]);
            } catch (ClientException $e) {
                $response = $e->getResponse();
                Core::captureException($e);
                return [];
            } catch (ServerException $e) {
                $response = $e->getResponse();
                Core::captureException($e);
                return [];
            } catch (\Throwable $e) {
                $response = null;
                Core::captureException($e);
                return [];
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

            $pushResponses[] = $pushResponse;
        }

        return $pushResponses;
    }

    protected function buildUserPushQueuePayload(UserPushQueue $userPushQueue): array
    {
        /** @var UserPushSubscription $userPushSubscription */
        $userPushSubscription = $userPushQueue->userPushSubscription;

        return [
            'registration_ids' => [$userPushSubscription->token],
            'notification' => $userPushQueue->data,
        ];
    }

    protected function getQueueBatchSize(): int
    {
        return 10;
    }
}
