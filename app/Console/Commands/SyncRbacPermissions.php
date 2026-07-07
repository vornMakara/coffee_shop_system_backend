<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncRbacPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rbac:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync permissions from config/permissions.php to the database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $groups = config('permissions.groups');

        if (!$groups) {
            $this->error('No permissions found in config/permissions.php');
            return Command::FAILURE;
        }

        $count = 0;

        foreach ($groups as $groupName => $permissions) {
            foreach ($permissions as $permissionName => $description) {
                $perm = \App\Modules\Auth\Models\Permission::firstOrCreate(
                    ['name' => $permissionName],
                    [
                        'group_name' => $groupName,
                        'description' => $description
                    ]
                );

                if ($perm->wasRecentlyCreated) {
                    $this->info("Created permission: {$permissionName}");
                    $count++;
                } else {
                    // Update description/group if changed
                    $perm->update([
                        'group_name' => $groupName,
                        'description' => $description
                    ]);
                }
            }
        }

        $this->info("Successfully synced permissions. {$count} new permissions created.");
        return Command::SUCCESS;
    }
}
