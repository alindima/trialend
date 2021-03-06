<?php

namespace App\Console;

use Auth;
use Mail;
use App\Mail\EmailNotification;
use App\Trial;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $trials=Trial::all();
            $now=Carbon::now();
            $now->timezone='UTC';

            foreach ($trials as $trial) {
                if($trial->end_at->lte($now)){
                    $trial->delete();
                }else if($now->diffInDays($trial->end_at) <= 1 && !$trial->notified){
                    Mail::to($trial->user)->send(new EmailNotification($trial->user, $trial));
                    $trial->notified=1;
                    $trial->save();
                }
            }

        })->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
