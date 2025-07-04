<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Jobs\SendWhatsAppNotification;
use GuzzleHttp\Exception\RequestException;

class WhatsAppController extends Controller
{
 
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
