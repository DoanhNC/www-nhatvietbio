<?php

namespace App\Http\Controllers\Rest;

use App\Http\Controllers\Controller;
use App\Models\EEmailTemplate;
use App\Models\ESetting;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class EmailTemplatesController extends Controller
{
    /**
     * List all templates
     */
    public function index()
    {
        $templates = EEmailTemplate::orderBy('label')->get();

        return response()->json([
            'status' => true,
            'data' => $templates,
        ]);
    }

    /**
     * Get single template
     */
    public function show($id)
    {
        $template = EEmailTemplate::find($id);

        if (!$template) {
            return ApiResponse::error('Template không tồn tại', 404);
        }

        return response()->json([
            'status' => true,
            'data' => $template,
        ]);
    }

    /**
     * Update template
     */
    public function update(Request $request, $id)
    {
        $template = EEmailTemplate::find($id);

        if (!$template) {
            return ApiResponse::error('Template không tồn tại', 404);
        }

        $validator = Validator::make($request->all(), [
            'label' => 'nullable|string|max:100',
            'subject' => 'nullable|string|max:255',
            'cc_emails' => 'nullable|array',
            'cc_emails.*' => 'email',
            'bcc_emails' => 'nullable|array',
            'bcc_emails.*' => 'email',
            'content' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        $template->update($request->only([
            'label',
            'subject',
            'cc_emails',
            'bcc_emails',
            'content',
            'is_active'
        ]));

        return ApiResponse::success(null, 200, 'Cập nhật mẫu email thành công');
    }

    /**
     * Test send template
     */
    public function test(Request $request, $id)
    {
        $template = EEmailTemplate::find($id);

        if (!$template) {
            return ApiResponse::error('Template không tồn tại', 404);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'test_data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        try {
            // Apply SMTP settings from database
            ESetting::applyMailConfig();

            $testData = $request->test_data ?? [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '0123456789',
                'message' => 'Đây là nội dung thử nghiệm.',
                'reset_link' => url('/admin/reset-password/test-token'),
            ];

            $content = $template->render($testData);
            $subject = $template->getRenderedSubject($testData);
            $toEmail = $request->email;

            Mail::html($content, function ($message) use ($toEmail, $subject, $template) {
                $message->to($toEmail)->subject($subject);

                // Add CC
                foreach ($template->getCcEmailsArray() as $cc) {
                    $message->cc($cc);
                }

                // Add BCC
                foreach ($template->getBccEmailsArray() as $bcc) {
                    $message->bcc($bcc);
                }
            });

            return ApiResponse::success(null, 200, 'Gửi email thử theo mẫu thành công');
        } catch (\Exception $e) {
            return ApiResponse::error('Gửi email thất bại: ' . $e->getMessage(), 500);
        }
    }
}
