<?php
namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MailConfigService
{
     public static function setSMTP($branchId = null)
     {
         if (!safe_has_table('smtp_settings')) {
             return;
         }

         $smtp = DB::table('smtp_settings')->where('status', 1)
             ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
             ->first();

         if (!$smtp) {
             return;
         }

         $setting = Setting::when($branchId, fn($q) => $q->where('branch_id', $branchId))
             ->first() ?? Setting::first();

         $companyName = trim((string) ($setting->name ?? ''));
         $smtpFromName = trim((string) ($smtp->from_name ?? ''));
         $fromName = $companyName !== '' ? $companyName : ($smtpFromName !== '' ? $smtpFromName : config('app.name'));
         $fromName = str_replace(["\r", "\n"], '', $fromName);

         $fromAddress = trim((string) ($smtp->from_address ?: $smtp->username));
         $fromAddress = str_replace(["\r", "\n"], '', $fromAddress);

         Config::set('mail.default', 'smtp');

         Config::set('mail.mailers.smtp', [
             'transport'  => 'smtp',
             'host'       => $smtp->host,
             'port'       => $smtp->port,
             'encryption' => $smtp->encryption === 'none' ? null : $smtp->encryption,
             'username'   => $smtp->username,
             'password'   => decrypt($smtp->password),
             'timeout'    => null,
             'auth_mode'  => null,
         ]);

         Config::set('mail.from.name', $fromName);

         app()->forgetInstance('mail.manager');
         app()->forgetInstance('mailer');
         Mail::purge('smtp');
     }
}
