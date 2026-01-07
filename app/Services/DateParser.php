<?php

namespace App\Services;

use Carbon\Carbon;

class DateParser
{
    public function parseTaskInput(string $input): array
    {
        $result = [
            'name' => $input,
            'date' => null,
            'time' => null,
            'recurrence_pattern' => null,
        ];

        $patterns = [
            'daily' => '/\b(daily|every day)\b/i',
            'weekdays' => '/\bweekdays\b/i',
            'weekends' => '/\bweekends\b/i',
            'weekly' => '/\bevery (\d+) weeks?\b/i',
            'day_of_week' => '/\b(monday|tuesday|wednesday|thursday|friday|saturday|sunday)s?\b/i',
            'multi_days' => '/\b(mon|tue|wed|thu|fri|sat|sun)(,(mon|tue|wed|thu|fri|sat|sun))+\b/i',
            'monthly_ordinal' => '/\bevery (first|second|third|fourth|last) (monday|tuesday|wednesday|thursday|friday|saturday|sunday)\b/i',
            'monthly_day' => '/\bevery (\d{1,2})(st|nd|rd|th)?\b/i',
            'yearly' => '/\b(yearly|every year)\b/i',
            'date_month_day' => '/\b(january|february|march|april|may|june|july|august|september|october|november|december) (\d{1,2})\b/i',
            'date_slash' => '/\b(\d{1,2})\/(\d{1,2})\b/',
            'date_iso' => '/\b(\d{4})-(\d{2})-(\d{2})\b/',
        ];

        if (preg_match($patterns['daily'], $input, $matches)) {
            $result['recurrence_pattern'] = 'daily';
            $result['date'] = Carbon::today()->format('Y-m-d');
            $result['name'] = trim(preg_replace($patterns['daily'], '', $input));
        } elseif (preg_match($patterns['weekdays'], $input, $matches)) {
            $result['recurrence_pattern'] = 'weekdays';
            $result['date'] = $this->getNextWeekday()->format('Y-m-d');
            $result['name'] = trim(preg_replace($patterns['weekdays'], '', $input));
        } elseif (preg_match($patterns['weekends'], $input, $matches)) {
            $result['recurrence_pattern'] = 'weekends';
            $result['date'] = $this->getNextWeekend()->format('Y-m-d');
            $result['name'] = trim(preg_replace($patterns['weekends'], '', $input));
        } elseif (preg_match($patterns['weekly'], $input, $matches)) {
            $weeks = (int) $matches[1];
            $result['recurrence_pattern'] = "every {$weeks} weeks";
            $result['date'] = Carbon::today()->format('Y-m-d');
            $result['name'] = trim(preg_replace($patterns['weekly'], '', $input));
        } elseif (preg_match($patterns['day_of_week'], $input, $matches)) {
            $dayName = ucfirst(strtolower($matches[1]));
            $result['recurrence_pattern'] = $dayName;
            $result['date'] = $this->getNextDayOfWeek($dayName)->format('Y-m-d');
            // Remove both "every" and the day name
            $result['name'] = trim(preg_replace('/\bevery\s+/i', '', preg_replace($patterns['day_of_week'], '', $input)));
        } elseif (preg_match($patterns['multi_days'], $input, $matches)) {
            $result['recurrence_pattern'] = $matches[0];
            $result['date'] = $this->getNextMultiDay($matches[0])->format('Y-m-d');
            $result['name'] = trim(preg_replace($patterns['multi_days'], '', $input));
        } elseif (preg_match($patterns['monthly_ordinal'], $input, $matches)) {
            $ordinal = $matches[1];
            $dayName = $matches[2];
            $result['recurrence_pattern'] = "every {$ordinal} {$dayName}";
            $result['date'] = $this->getNextOrdinalDay($ordinal, $dayName)->format('Y-m-d');
            $result['name'] = trim(preg_replace($patterns['monthly_ordinal'], '', $input));
        } elseif (preg_match($patterns['monthly_day'], $input, $matches)) {
            $day = (int) $matches[1];
            $result['recurrence_pattern'] = "every {$day}";
            $result['date'] = $this->getNextMonthDay($day)->format('Y-m-d');
            $result['name'] = trim(preg_replace($patterns['monthly_day'], '', $input));
        } elseif (preg_match($patterns['yearly'], $input, $matches)) {
            $result['recurrence_pattern'] = 'yearly';
            $result['date'] = Carbon::today()->addYear()->format('Y-m-d');
            $result['name'] = trim(preg_replace($patterns['yearly'], '', $input));
        } elseif (preg_match($patterns['date_month_day'], $input, $matches)) {
            $month = $matches[1];
            $day = (int) $matches[2];
            $result['date'] = $this->getNextMonthDayDate($month, $day)->format('Y-m-d');
            $result['name'] = trim(preg_replace($patterns['date_month_day'], '', $input));
        } elseif (preg_match($patterns['date_slash'], $input, $matches)) {
            $month = (int) $matches[1];
            $day = (int) $matches[2];
            $result['date'] = $this->getNextDate($month, $day)->format('Y-m-d');
            $result['name'] = trim(preg_replace($patterns['date_slash'], '', $input));
        } elseif (preg_match($patterns['date_iso'], $input, $matches)) {
            $year = (int) $matches[1];
            $month = (int) $matches[2];
            $day = (int) $matches[3];
            $result['date'] = Carbon::create($year, $month, $day)->format('Y-m-d');
            $result['name'] = trim(preg_replace($patterns['date_iso'], '', $input));
        }

        $result['name'] = trim($result['name']);
        if (empty($result['name'])) {
            $result['name'] = $input;
        }

        return $result;
    }

    protected function getNextWeekday(): Carbon
    {
        $date = Carbon::today();
        if ($date->isWeekend()) {
            return $date->next(Carbon::MONDAY);
        }
        return $date;
    }

    protected function getNextWeekend(): Carbon
    {
        $date = Carbon::today();
        if ($date->isSaturday() || $date->isSunday()) {
            return $date;
        }
        return $date->next(Carbon::SATURDAY);
    }

    protected function getNextDayOfWeek(string $dayName): Carbon
    {
        $date = Carbon::today();
        $targetDay = constant('Carbon\Carbon::' . strtoupper($dayName));

        if ($date->dayOfWeek === $targetDay) {
            return $date->addWeek();
        }

        return $date->next($targetDay);
    }

    protected function getNextMultiDay(string $days): Carbon
    {
        $dayMap = [
            'sun' => Carbon::SUNDAY,
            'mon' => Carbon::MONDAY,
            'tue' => Carbon::TUESDAY,
            'wed' => Carbon::WEDNESDAY,
            'thu' => Carbon::THURSDAY,
            'fri' => Carbon::FRIDAY,
            'sat' => Carbon::SATURDAY,
        ];

        $dayParts = explode(',', strtolower($days));
        $targetDays = array_map(function ($day) use ($dayMap) {
            return $dayMap[trim($day)] ?? null;
        }, $dayParts);
        $targetDays = array_filter($targetDays);

        $date = Carbon::today();
        $found = false;

        for ($i = 0; $i < 7; $i++) {
            if (in_array($date->dayOfWeek, $targetDays)) {
                $found = true;
                break;
            }
            $date->addDay();
        }

        return $date;
    }

    protected function getNextOrdinalDay(string $ordinal, string $dayName): Carbon
    {
        $date = Carbon::today()->startOfMonth();
        $targetDay = constant('Carbon\Carbon::' . strtoupper($dayName));

        $occurrences = [];
        while ($date->month === Carbon::today()->month) {
            if ($date->dayOfWeek === $targetDay) {
                $occurrences[] = $date->copy();
            }
            $date->addDay();
        }

        $ordinalMap = [
            'first' => 0,
            'second' => 1,
            'third' => 2,
            'fourth' => 3,
            'last' => count($occurrences) - 1,
        ];

        $index = $ordinalMap[strtolower($ordinal)] ?? 0;
        $targetDate = $occurrences[$index] ?? Carbon::today();

        if ($targetDate->isPast()) {
            return $this->getNextOrdinalDay($ordinal, $dayName);
        }

        return $targetDate;
    }

    protected function getNextMonthDay(int $day): Carbon
    {
        $date = Carbon::today();

        if ($date->day > $day) {
            $date->addMonth();
        }

        $date->day = min($day, $date->daysInMonth);

        return $date;
    }

    protected function getNextMonthDayDate(string $month, int $day): Carbon
    {
        $monthNum = Carbon::parse($month . ' 1')->month;
        $year = Carbon::today()->year;

        $date = Carbon::create($year, $monthNum, $day);

        if ($date->isPast()) {
            $date->addYear();
        }

        return $date;
    }

    protected function getNextDate(int $month, int $day): Carbon
    {
        $year = Carbon::today()->year;
        $date = Carbon::create($year, $month, $day);

        if ($date->isPast()) {
            $date->addYear();
        }

        return $date;
    }

    public function getNextOccurrence(string $recurrencePattern, Carbon $currentDate): ?Carbon
    {
        if (!$recurrencePattern) {
            return null;
        }

        if ($recurrencePattern === 'daily') {
            return $currentDate->copy()->addDay();
        }

        if ($recurrencePattern === 'weekdays') {
            $next = $currentDate->copy()->addDay();
            while ($next->isWeekend()) {
                $next->addDay();
            }
            return $next;
        }

        if ($recurrencePattern === 'weekends') {
            $next = $currentDate->copy()->addDay();
            while (!$next->isWeekend()) {
                $next->addDay();
            }
            return $next;
        }

        if (preg_match('/^every (\d+) weeks?$/', $recurrencePattern, $matches)) {
            $weeks = (int) $matches[1];
            return $currentDate->copy()->addWeeks($weeks);
        }

        if (preg_match('/^(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday)$/i', $recurrencePattern)) {
            return $this->getNextDayOfWeek($recurrencePattern);
        }

        if (preg_match('/^(mon|tue|wed|thu|fri|sat|sun)(,(mon|tue|wed|thu|fri|sat|sun))+$/i', $recurrencePattern)) {
            return $this->getNextMultiDay($recurrencePattern);
        }

        if (preg_match('/^every (first|second|third|fourth|last) (monday|tuesday|wednesday|thursday|friday|saturday|sunday)$/i', $recurrencePattern, $matches)) {
            return $this->getNextOrdinalDay($matches[1], $matches[2]);
        }

        if (preg_match('/^every (\d{1,2})$/', $recurrencePattern, $matches)) {
            $day = (int) $matches[1];
            return $this->getNextMonthDay($day);
        }

        if ($recurrencePattern === 'yearly') {
            return $currentDate->copy()->addYear();
        }

        return null;
    }
}
