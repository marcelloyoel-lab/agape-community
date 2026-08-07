<?php

namespace App\Console\Commands;

use App\Services\WhatsApp\WahaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class ListWhatsAppGroupsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'waha:list-groups';

    /**
     * The console command description.
     */
    protected $description = 'Display all available WhatsApp groups.';

    public function __construct(
        private readonly WahaService $wahaService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $groups = $this->wahaService->groupList();

            if (empty($groups)) {
                $this->warn('No WhatsApp groups found.');

                return self::SUCCESS;
            }

            $this->info('Available WhatsApp Groups');
            $this->newLine();

            $this->table(
                ['Name', 'Group ID'],
                collect($groups)
                    ->map(fn (array $group) => [
                        $group['name'],
                        $group['id'],
                    ])
                    ->toArray()
            );

            $this->newLine();

            $this->comment(
                'Copy the desired Group ID into your .env file as WAHA_GROUP_ID='
            );

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error('Failed to retrieve WhatsApp groups.');

            Log::error('Failed to list WhatsApp groups.', [
                'exception' => $exception->getMessage(),
            ]);

            return self::FAILURE;
        }
    }
}