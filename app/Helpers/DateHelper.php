<?php

if (!function_exists('jalaliDate')) {
    /**
     * تبدیل تاریخ میلادی به شمسی
     * 
     * @param mixed $date تاریخ میلادی (Carbon instance, string, یا null)
     * @param string $format فرمت خروجی (پیش‌فرض: 'Y/m/d H:i')
     * @return string تاریخ شمسی
     */
    function jalaliDate($date, $format = 'Y/m/d H:i')
    {
        if (!$date) {
            return '-';
        }

        try {
            $carbonDate = null;
            
            // اگر تاریخ به صورت string است، آن را به Carbon تبدیل می‌کنیم
            if (is_string($date)) {
                $carbonDate = \Carbon\Carbon::parse($date);
            } elseif ($date instanceof \Carbon\Carbon) {
                $carbonDate = $date;
            } elseif ($date instanceof \DateTime) {
                $carbonDate = \Carbon\Carbon::instance($date);
            } else {
                return (string)$date;
            }

            // استفاده از کتابخانه Morilog/Jalali برای تبدیل به شمسی
            if (class_exists(\Morilog\Jalali\Jalalian::class)) {
                $jalali = \Morilog\Jalali\Jalalian::fromCarbon($carbonDate);
                
                // فرمت را مستقیماً استفاده می‌کنیم (Jalalian از همان فرمت‌های PHP استفاده می‌کند)
                return $jalali->format($format);
            }

            // اگر کتابخانه موجود نبود، تاریخ میلادی را برمی‌گردانیم
            return $carbonDate->format($format);
        } catch (\Exception $e) {
            // در صورت خطا، تاریخ میلادی را برمی‌گردانیم
            try {
                if ($carbonDate) {
                    return $carbonDate->format($format);
                }
            } catch (\Exception $e2) {
                // ignore
            }
            return '-';
        }
    }
}

