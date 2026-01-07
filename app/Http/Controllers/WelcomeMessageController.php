<?php

namespace App\Http\Controllers;

use App\Models\WelcomeMessage;
use App\Models\WelcomeMessageLog;
use App\Models\Report;
use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;

class WelcomeMessageController extends Controller
{
    /**
     * Display the welcome messages page.
     */
    public function index(): View
    {
        $welcomeMessages = WelcomeMessage::with(['filters', 'logs'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $reports = Report::where('type', 'welcome')->get();
        $settings = Settings::first();

        return view('welcome-messages.index', compact(
            'welcomeMessages',
            'reports',
            'settings'
        ));
    }

    /**
     * Store a new welcome message.
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pattern_code' => 'nullable|string|max:50',
            'pattern_text' => 'required_with:pattern_code|string',
            'is_active' => 'boolean',
            'send_delay_minutes' => 'integer|min:0',
            'send_once_per_resident' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $welcomeMessage = WelcomeMessage::create([
                'title' => $request->title,
                'description' => $request->description,
                'pattern_code' => $request->pattern_code,
                'pattern_text' => $request->pattern_text,
                'is_active' => $request->boolean('is_active', true),
                'send_delay_minutes' => $request->integer('send_delay_minutes', 0),
                'send_once_per_resident' => $request->boolean('send_once_per_resident', true),
            ]);

            // Add default filter if provided
            if ($request->has('default_filter_field') && $request->default_filter_field) {
                $welcomeMessage->filters()->create([
                    'table_name' => 'residents',
                    'field_name' => $request->default_filter_field,
                    'operator' => $request->default_filter_operator ?? '=',
                    'value' => $request->default_filter_value ?? 'active',
                    'logical_operator' => 'and',
                    'priority' => 0,
                ]);
            }

            return redirect()->route('welcome-messages.index')
                ->with('success', 'پیام خوش‌آمدگویی با موفقیت ایجاد شد.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'خطا در ایجاد پیام خوش‌آمدگویی: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update welcome message settings.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'welcome_report_id' => 'nullable|exists:reports,id',
            'welcome_start_date' => 'nullable|date',
            'welcome_check_interval_minutes' => 'integer|min:1',
            'welcome_system_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $settings = Settings::first();
            if (!$settings) {
                $settings = new Settings();
            }

            $settings->welcome_report_id = $request->welcome_report_id;
            $settings->welcome_start_date = $request->welcome_start_date;
            $settings->welcome_check_interval_minutes = $request->integer('welcome_check_interval_minutes', 1);
            $settings->welcome_system_active = $request->boolean('welcome_system_active', false);
            $settings->save();

            return redirect()->route('welcome-messages.index')
                ->with('success', 'تنظیمات با موفقیت ذخیره شد.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'خطا در ذخیره تنظیمات: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Toggle welcome message status.
     */
    public function toggleStatus($id): RedirectResponse
    {
        try {
            $welcomeMessage = WelcomeMessage::findOrFail($id);
            $welcomeMessage->is_active = !$welcomeMessage->is_active;
            $welcomeMessage->save();

            $status = $welcomeMessage->is_active ? 'فعال' : 'غیرفعال';
            return redirect()->route('welcome-messages.index')
                ->with('success', "پیام خوش‌آمدگویی {$status} شد.");

        } catch (\Exception $e) {
            return redirect()->route('welcome-messages.index')
                ->with('error', 'خطا در تغییر وضعیت: ' . $e->getMessage());
        }
    }

    /**
     * Delete welcome message.
     */
    public function destroy($id): RedirectResponse
    {
        try {
            $welcomeMessage = WelcomeMessage::findOrFail($id);
            $welcomeMessage->delete();

            return redirect()->route('welcome-messages.index')
                ->with('success', 'پیام خوش‌آمدگویی با موفقیت حذف شد.');

        } catch (\Exception $e) {
            return redirect()->route('welcome-messages.index')
                ->with('error', 'خطا در حذف پیام: ' . $e->getMessage());
        }
    }

    /**
     * Display welcome message logs.
     */
    public function logs(): View
    {
        $logs = WelcomeMessageLog::with(['welcomeMessage'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('welcome-messages.logs', compact('logs'));
    }

    /**
     * Manually trigger welcome message processing.
     */
    public function process(): RedirectResponse
    {
        try {
            \Artisan::call('welcome:process');
            
            return redirect()->route('welcome-messages.index')
                ->with('success', 'پردازش پیام‌های خوش‌آمدگویی با موفقیت انجام شد.');

        } catch (\Exception $e) {
            return redirect()->route('welcome-messages.index')
                ->with('error', 'خطا در پردازش پیام‌ها: ' . $e->getMessage());
        }
    }
}
