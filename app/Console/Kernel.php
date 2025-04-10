<?php

namespace App\Console;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Jobs\SendWhatsAppNotification;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        // $schedule->job(new SendWhatsAppNotification())->dailyAt('06:00');
        // $schedule->job(new SendWhatsAppNotification())->everyMinute();
        $schedule->call(function () {
            Log::info("Fetching API data...");
    
            // ดึงข้อมูลจาก API
            // $response = Http::get('https://pg-hrm.dms.go.th/sys/meetingRoom/api_noti.php');
            $response = Http::withOptions([
                'verify' => false, // ปิดการตรวจสอบ SSL
            ])->get('https://pg-hrm.dms.go.th/sys/meetingRoom/api_noti.php');
            
            // ถ้าการเรียก API สำเร็จ
            if ($response->successful()) {
                Log::info("Successfully fetched data from API.");
    
                $data = $response->json();
                $meetingDetails = $data['str'];
                $message = "การแจ้งเตือนห้องประชุม: \n" . $meetingDetails;
                
                // กำหนด chat_id ที่ต้องการ
                $chatId = '120363396335102170@g.us'; // chat_id
                    
                // Dispatch job และส่งค่า chatId, message
                SendWhatsAppNotification::dispatch($chatId, $message);
            } else {
                Log::error("Failed to fetch data from API.");
            }
        })
        // ->dailyAt('06:00');
        ->everyMinute();
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
