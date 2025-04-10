<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SendWhatsAppNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $chatId;
    protected $message;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($chatId, $message)
    {
        $this->chatId = $chatId;
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //     // ดึงข้อมูลจาก API แจ้งเตือนห้องประชุม
        //     $response = Http::get('http://pg-hrm.dms.go.th/sys/meetingRoom/api_noti.php');

        //     // ตรวจสอบว่าได้รับข้อมูลจาก API หรือไม่
        //     if ($response->successful()) {
        //         // รับข้อมูลจาก API ซึ่งอยู่ใน key 'str'
        //         $data = $response->json();

        //         // ดึงข้อความจาก key 'str' (ข้อมูลที่แสดงรายละเอียดของการจองห้องประชุม)
        //         $meetingDetails = $data['str'];

        //         $message = "การแจ้งเตือนห้องประชุม: \n" . $meetingDetails;

        //         Http::post('http://localhost:3000/send-message', [
        //             'chatId' => '', // ใช้ chat_id ที่ต้องการ
        //             'message' => $message,
        //         ]);
        //     }
        // }
        #######################################################################################################
        // ดึงข้อมูลจาก API แจ้งเตือนห้องประชุม
        Log::info("Job started with chatId: " . $this->chatId);

        // ดึงข้อมูลจาก API แจ้งเตือนห้องประชุม
        $response = Http::get('http://pg-hrm.dms.go.th/sys/meetingRoom/api_noti.php');

        if ($response->successful()) {
            Log::info("API data fetched successfully.");

            $data = $response->json();
            $meetingDetails = $data['str'];

            $message = $this->message ?: "การแจ้งเตือนห้องประชุม: \n" . $meetingDetails;

            // ส่งข้อความไปยัง Node.js API
            Http::post('http://localhost:3000/send-message', [
                'chatId' => $this->chatId, // ใช้ chat_id ที่รับมาจาก Constructor
                'message' => $message,
            ]);
            Log::info("Message sent successfully.");
        } else {
            Log::error("Failed to fetch data from API in job.");
        }
    }
}
