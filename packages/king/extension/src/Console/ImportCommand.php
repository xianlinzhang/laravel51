<?php

namespace King\ExtensionForLaravel\Console;

use King\ExtensionForLaravel\ExtensionManage;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class ImportCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'extension:import {extension?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a extension';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $extension = $this->argument('extension');

        if (empty($extension) || !Arr::has(ExtensionManage::$extensions, $extension)) {
            if (!empty(ExtensionManage::$extensions)){
                $extension = $this->choice('Please choose a extension to import', array_keys(ExtensionManage::$extensions));
            }else{
                $this->info("Extension [$extension] don't exsit.");
                return;
            }

        }

        $className = Arr::get(ExtensionManage::$extensions, $extension);

        if (!class_exists($className) || !method_exists($className, 'import')) {
            $this->error("Invalid Extension [$className]");

            return;
        }

        call_user_func([$className, 'import'], $this);

        $this->info("Extension [$className] imported");
    }
}
