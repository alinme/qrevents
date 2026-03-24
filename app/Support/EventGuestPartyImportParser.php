<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class EventGuestPartyImportParser
{
    /**
     * @return array<int, array{name: string, phone: ?string, invited_attendees_count: int, notes: ?string}>
     */
    public function parse(string $contents): array
    {
        return collect(preg_split('/\\r\\n|\\r|\\n/', $contents) ?: [])
            ->map(fn (string $line): ?array => $this->parseLine($line))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array{name: string, phone: ?string, invited_attendees_count: int, notes: ?string}|null
     */
    private function parseLine(string $line): ?array
    {
        $cleanLine = trim(preg_replace('/\s+/', ' ', $line) ?? $line);
        if ($cleanLine === '') {
            return null;
        }

        if ($this->looksLikeHeaderRow($cleanLine)) {
            return null;
        }

        $tokens = $this->lineTokens($cleanLine);
        $name = null;
        $phone = null;
        $notes = [];
        $invitedCount = 1;

        foreach ($tokens as $token) {
            if ($phone === null && $this->looksLikePhone($token)) {
                $phone = $this->normalizePhone($token);

                continue;
            }

            if ($this->looksLikeAttendeeCount($token)) {
                $invitedCount = max(1, min(1000, (int) preg_replace('/\D+/', '', $token)));

                continue;
            }

            if ($name === null) {
                $name = $token;

                continue;
            }

            $notes[] = $token;
        }

        if ($name === null) {
            $fallbackName = trim(str_replace((string) $phone, '', $cleanLine));
            $name = $fallbackName !== '' ? trim($fallbackName, ",;|- \t") : null;
        }

        if ($name === null || mb_strlen($name) < 2) {
            return null;
        }

        return [
            'name' => $name,
            'phone' => $phone,
            'invited_attendees_count' => $invitedCount,
            'notes' => count($notes) > 0 ? implode(' · ', $notes) : null,
        ];
    }

    /**
     * @return list<string>
     */
    private function lineTokens(string $line): array
    {
        $delimiter = $this->detectedDelimiter($line);

        if ($delimiter !== null) {
            return collect(str_getcsv($line, $delimiter))
                ->map(fn (string $token): string => trim($token))
                ->filter(fn (string $token): bool => $token !== '')
                ->values()
                ->all();
        }

        return Collection::make(preg_split('/\s-\s| - | — | – /u', $line) ?: [$line])
            ->map(fn (string $token): string => trim($token))
            ->filter(fn (string $token): bool => $token !== '')
            ->values()
            ->all();
    }

    private function detectedDelimiter(string $line): ?string
    {
        foreach (["\t", ';', ','] as $delimiter) {
            if (str_contains($line, $delimiter)) {
                return $delimiter;
            }
        }

        return null;
    }

    private function looksLikeHeaderRow(string $line): bool
    {
        $normalized = Str::lower($line);

        return str_contains($normalized, 'name')
            || str_contains($normalized, 'family')
            || str_contains($normalized, 'nume')
            || str_contains($normalized, 'telefon')
            || str_contains($normalized, 'phone');
    }

    private function looksLikePhone(string $token): bool
    {
        $digits = preg_replace('/\D+/', '', $token) ?? '';

        return strlen($digits) >= 7 && strlen($digits) <= 15;
    }

    private function normalizePhone(string $token): string
    {
        return trim(preg_replace('/\s+/', ' ', $token) ?? $token);
    }

    private function looksLikeAttendeeCount(string $token): bool
    {
        $normalized = Str::lower(trim($token));

        return preg_match('/^\d{1,3}$/', $normalized) === 1
            || preg_match('/^\d{1,3}\s*(pax|pers|people|guests|invitati|invitați|attendees)$/u', $normalized) === 1;
    }
}
