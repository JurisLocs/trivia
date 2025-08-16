<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class QuestionService
{
    const MAX_TRIES = 12;
    const MIN_NUM = 0;

    public function generateQuestion(array $askedNumbers = []): array
    {
        $tries = 0;

        while ($tries < self::MAX_TRIES) {
            $tries++;

            $response = Http::timeout(5)->get('http://numbersapi.com/random/trivia?json');

            if ($response->failed()) {
                throw new \RuntimeException('Numbers API request failed.');
            }

            $data = $response->json();

            if (!isset($data['number'], $data['text']) || !is_numeric($data['number'])) {
                continue;
            }

            $answer = (int) $data['number'];

            if (in_array($answer, $askedNumbers, true)) {
                continue;
            }

            $rawText = (string) $data['text'];

            $pattern = '/\b' . preg_quote((string) $answer, '/') . '\b/u';
            $questionText = preg_replace($pattern, '____', $rawText);

            if ($questionText === null) {
                continue; // Regex failed, skip this question
            }
            if ($questionText === $rawText) {
                $questionText = "Which number fits this fact? " . $rawText;
            }

            $choices = $this->buildChoices($answer);

            return [
                'text'    => $questionText,
                'answer'  => $answer,
                'choices' => $choices,
                'source'  => $rawText,
            ];
        }

        throw new \RuntimeException('Could not generate a unique question.');
    }

    private function buildChoices(int $answer): array
    {
        $set = [$answer];
        $attempts = 0;
        while (count($set) < 4 && $attempts < 100) {
            $attempts++;

            $offsets = [1, 2, 3, 4, 5, 7, 10, 12, 15, 20, 25, 30, 50, 75, 100];
            $randOffset = $offsets[array_rand($offsets)];
            $sign = rand(0, 1) ? 1 : -1;
            $candidate = $answer + ($sign * $randOffset);

            if ($candidate < self::MIN_NUM) {
                continue;
            }
            if (!in_array($candidate, $set, true)) {
                $set[] = $candidate;
            }
        }

        while (count($set) < 4) {
            $pad = rand(self::MIN_NUM);
            if (!in_array($pad, $set, true)) $set[] = $pad;
        }

        shuffle($set);
        return $set;
    }
}
