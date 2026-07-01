<?php

namespace Modules\Loyalty\Console;

use Illuminate\Console\Command;
use Modules\Loyalty\Services\LoyaltyMemberEnrollmentService;

class EnrollMembersCommand extends Command
{
    protected $signature = 'loyalty:enroll-members
                            {--all-users : Include non-customer roles (admin, beautician, etc.)}
                            {--limit= : Maximum number of users to enroll}
                            {--dry-run : Show how many users would be enrolled without creating wallets}';

    protected $description = 'Create loyalty wallets for users who do not have one yet';


    public function handle(LoyaltyMemberEnrollmentService $enrollment): int
    {
        $customersOnly = ! $this->option('all-users');
        $missing = $enrollment->countMissing($customersOnly);

        if ($missing === 0) {
            $this->info('All eligible users already have a loyalty wallet.');

            return self::SUCCESS;
        }

        $scope = $customersOnly ? 'customers' : 'all users';

        if ($this->option('dry-run')) {
            $this->info("Would enroll {$missing} {$scope} without a loyalty wallet.");

            return self::SUCCESS;
        }

        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;
        $target = $limit ? min($missing, $limit) : $missing;

        $this->info("Enrolling up to {$target} {$scope}…");

        $bar = $this->output->createProgressBar($target);
        $bar->start();

        $result = $enrollment->enrollMissing(
            $customersOnly,
            $limit,
            function () use ($bar) {
                $bar->advance();
            }
        );

        $bar->finish();
        $this->newLine(2);
        $this->info("Enrolled {$result['enrolled']} loyalty member(s).");

        $remaining = $enrollment->countMissing($customersOnly);

        if ($remaining > 0) {
            $this->comment("{$remaining} eligible user(s) still without a wallet.");
        }

        return self::SUCCESS;
    }
}
