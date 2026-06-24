<?php

namespace Modules\GoogleIntegration\Console;

class RetryFailedGoogleSheetsSyncCommand extends BackfillGoogleSheetsCommand
{
    protected $signature = 'google-sheets:retry-failed
                            {--limit=50 : Maximum number of orders to retry}
                            {--order= : Retry a single order ID only}';

    protected $description = 'Retry Google Sheets sync for completed orders that never synced successfully';
}
