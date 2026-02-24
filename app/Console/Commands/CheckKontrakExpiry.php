<?php

namespace App\Console\Commands;

use App\Services\KontrakNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckKontrakExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kontrak:check-expiry 
                            {--no-email : Skip sending email notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check contract expiry dates and create notifications for expired/expiring contracts';

    protected KontrakNotificationService $notificationService;

    public function __construct(KontrakNotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('========================================');
        $this->info('Kontrak Expiry Check - ' . now()->format('Y-m-d H:i:s'));
        $this->info('========================================');
        $this->newLine();

        Log::info('Kontrak Expiry Check started');

        try {
            $this->info('Checking contract expiry dates...');
            
            $result = $this->notificationService->checkAndUpdateKontrakStatus();

            $this->newLine();
            $this->info('Results:');
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Kontrak Expired (Habis)', $result['expired']],
                    ['Kontrak Expiring (Akan Habis)', $result['expiring']],
                    ['Notifikasi Dibuat', $result['notifications_created']],
                    ['Email Terkirim', $result['emails_sent']],
                ]
            );

            if (!empty($result['errors'])) {
                $this->newLine();
                $this->warn('Errors encountered:');
                foreach ($result['errors'] as $error) {
                    $this->error(' - ' . $error);
                }
            }

            $this->newLine();
            $this->info('✓ Kontrak expiry check completed successfully');

            Log::info('Kontrak Expiry Check completed', $result);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('Kontrak Expiry Check failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }
}
