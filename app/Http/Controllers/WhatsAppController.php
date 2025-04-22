<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Jobs\SendWhatsAppNotification;
use GuzzleHttp\Exception\RequestException;

class WhatsAppController extends Controller
{
    // public function send(Request $request)
    // {
    //     // Validate incoming data
    //     $validated = $request->validate([
    //         'chat_id' => 'required|string',
    //         'message' => 'required|string',
    //     ]);

    //     // ส่งคำขอไปยัง Node.js API
    //     $response = Http::post('http://localhost:3000/send-message', [
    //         'chatId' => $validated['chat_id'],
    //         'message' => $validated['message'],
    //     ]);

    //     // ตอบกลับตามผลลัพธ์
    //     if ($response->successful()) {
    //         return response()->json(['status' => 'success']);
    //     } else {
    //         return response()->json(['status' => 'error', 'error' => $response->body()]);
    //     }
    // }

    // public function send(Request $request)
    // {
    //     $validated = $request->validate([
    //         'chat_id' => 'required|string',
    //     ]);

    //     // $response = Http::get('https://pg-hrm.dms.go.th/sys/meetingRoom/api_noti.php');
    //     $response = Http::withOptions([
    //         'verify' => false,  // ปิดการตรวจสอบ SSL
    //     ])->get('https://pg-hrm.dms.go.th/sys/meetingRoom/api_noti.php');


    //     if ($response->successful()) {
    //         $data = $response->json();

    //         $meetingDetails = $data['str'];

    //         $message =  $meetingDetails;

    //         // ส่งข้อความไปยัง WhatsApp Group ผ่าน Node.js API
    //         $sendResponse = Http::post('http://localhost:3000/send-message', [
    //             'chatId' => $validated['chat_id'],
    //             'message' => $message,
    //         ]);

    //         // $sendResponse = Http::post('https://pg-hrm.dms.go.th/send-message', [
    //         //     'chatId' => $validated['chat_id'],
    //         //     'message' => $message,
    //         // ]);

    //         // ตรวจสอบผลลัพธ์จาก Node.js API
    //         if ($sendResponse->successful()) {
    //             return response()->json(['status' => 'success']);
    //         } else {
    //             return response()->json(['status' => 'error', 'error' => $sendResponse->body()]);
    //         }
    //     } else {
    //         // ถ้าไม่สามารถดึงข้อมูลจาก API ได้
    //         return response()->json(['status' => 'error', 'error' => 'ไม่สามารถดึงข้อมูลจาก API ได้'], 500);
    //     }
    // }


    //ทดสอบ
    //     public function send(Request $request)
    //     {
    //         $validated = $request->validate([
    //             'chat_id' => 'required|string',
    //         ]);

    //         // สร้าง Guzzle Client
    //         $client = new Client();

    //         try {
    //             // ดึงข้อมูลจาก API
    //             $response = $client->get('https://pg-hrm.dms.go.th/sys/meetingRoom/api_noti.php', [
    //                 // สามารถตั้งค่าเพิ่มเติม เช่น การตั้งค่า SSL, timeouts หรืออื่นๆ ได้
    //                 'verify' => false, // ปิดการตรวจสอบ SSL ถ้ามีปัญหากับใบรับรอง
    //             ]);

    //             // ตรวจสอบว่าเรียก API สำเร็จ
    //             if ($response->getStatusCode() == 200) {
    //                 $data = json_decode($response->getBody()->getContents(), true);

    //                 $meetingDetails = $data['str'];
    //                 $message =  $meetingDetails;

    //                 // ส่งข้อความไปยัง Node.js API
    //                 $sendResponse = $client->post('http://localhost:3000/send-message', [
    //                     'json' => [
    //                         'chatId' => $validated['chat_id'],
    //                         'message' => $message,
    //                     ],
    //                 ]);

    //                 // ตรวจสอบผลลัพธ์จาก Node.js API
    //                 if ($sendResponse->getStatusCode() == 200) {
    //                     return response()->json(['status' => 'success']);
    //                 } else {
    //                     return response()->json(['status' => 'error', 'error' => 'Error from Node.js API']);
    //                 }
    //             } else {
    //                 return response()->json(['status' => 'error', 'error' => 'Failed to fetch data from the API'], 500);
    //             }
    //         } catch (RequestException $e) {
    //             // จัดการข้อผิดพลาดจาก Guzzle
    //             return response()->json(['status' => 'error', 'error' => 'Error occurred while making the request: ' . $e->getMessage()], 500);
    //         }
    //     }
    // }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'chat_id' => 'required|string',
        ]);

        // ดึงข้อมูลจาก API
        $response = Http::get('API');

        if ($response->successful()) {
            $data = $response->json();
            $meetingDetails = $data['str'];
            $message = "การแจ้งเตือนห้องประชุม: \n" . $meetingDetails;

            // Dispatch Job พร้อมกับ chat_id และ message
            dispatch(new SendWhatsAppNotification($validated['chat_id'], $message));

            return response()->json(['status' => 'success']);
        } else {
            return response()->json(['status' => 'error', 'error' => 'Failed to fetch data from the API'], 500);
        }
    }
}
