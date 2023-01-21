<?php

namespace Sync\Workers;

use AmoCRM\OAuth2\Client\Provider\AmoCRM;
use Illuminate\Support\Carbon;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Sync\ApiService;
use Sync\DatabaseFunctions;
use Illuminate\Database\Capsule\Manager as Capsule;
use Sync\Models\Account;

class UpdateTokensWorker extends BaseWorker
{
    /** @var string Просматриваемая очередь. */
    protected string $queue = 'update';

    /**
     * Обновление токенов
     *
     * @param $data
     * @param OutputInterface $output
     * @return void
     */
    public function process($data, OutputInterface $output)
    {
        (new DatabaseFunctions())->getConnection();
        $account = Account::where(
            'access_token->expires',
            '<',
            Carbon::now()->addHours($data)->timestamp
        )
            ->get()
            ->sortByDesc('id')
            ->first();
        $newAccessToken = (new ApiService())->refreshToken($account->access_token);
        Account::query()->update([
            'access_token' => json_encode($newAccessToken)
        ]);
    }
}
