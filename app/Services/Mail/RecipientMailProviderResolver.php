<?php

namespace App\Services\Mail;

use Illuminate\Support\Facades\Cache;

class RecipientMailProviderResolver
{
    /**
     * Retorna: gmail | outlook | other
     */
    public function resolve(string $email): string
    {
        $domain = strtolower(substr(strrchr($email, '@') ?: '', 1));
        if (!$domain) return 'other';

        // dominios directos
        if (in_array($domain, ['gmail.com', 'googlemail.com'], true)) {
            return 'gmail';
        }

        if (in_array($domain, ['outlook.com', 'hotmail.com', 'live.com', 'msn.com'], true)) {
            return 'outlook';
        }

        // ✅ dinámico por MX (cache 7 días)
        return Cache::remember("mail:provider:{$domain}", now()->addDays(7), function () use ($domain) {
            $targets = $this->mxTargets($domain);

            if (empty($targets)) {
                return 'other';
            }

            foreach ($targets as $t) {
                $t = strtolower($t);

                // Google Workspace
                if (str_contains($t, 'google.com') || str_contains($t, 'l.google.com')) {
                    return 'gmail';
                }

                // Microsoft 365
                if (str_contains($t, 'protection.outlook.com') || str_contains($t, 'outlook.com')) {
                    return 'outlook';
                }
            }

            return 'other';
        });
    }

    private function mxTargets(string $domain): array
    {
        // Opción 1: dns_get_record
        $records = @dns_get_record($domain, DNS_MX);
        if (is_array($records) && count($records)) {
            return array_values(array_filter(array_map(
                fn ($r) => $r['target'] ?? null,
                $records
            )));
        }

        // Opción 2: getmxrr fallback
        $hosts = [];
        $weights = [];
        if (@getmxrr($domain, $hosts, $weights) && is_array($hosts)) {
            return array_values(array_filter($hosts));
        }

        return [];
    }
}