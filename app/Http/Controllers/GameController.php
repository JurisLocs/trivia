<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\QuestionService;

class GameController extends Controller
{
    public function __construct(private QuestionService $questions) {}

    public function index(Request $request)
    {
        $game = $request->session()->get('game');

        if (!$game) {
            return view('game', ['view' => 'landing']);
        }

        if (($game['status'] ?? 'idle') === 'playing' && empty($game['current'])) {
            try {
                $q = $this->questions->generateQuestion($game['asked']);
                $game['current'] = $q;
                $request->session()->put('game', $game);
            } catch (\Throwable $e) {
                $game['error'] = 'Failed to load a question, try again!';
                $request->session()->put('game', $game);
            }
        }

        return view('game', [
            'view' => ($game['status'] === 'over') ? 'summary' : 'play',
            'game' => $game,
        ]);
    }

    public function start(Request $request)
    {
        $game = [
            'status'   => 'playing',
            'score'    => 0,
            'count'    => 0,
            'limit'    => 20,
            'asked'    => [],
            'current'  => null,
            'last'     => null,
            'error'    => null,
        ];

        try {
            $q = $this->questions->generateQuestion($game['asked']);
            $game['current'] = $q;
        } catch (\Throwable $e) {
            $game['error'] = 'Failed to load a question, try again!';
        }

        $request->session()->put('game', $game);

        return redirect()->route('game.index');
    }

    public function guess(Request $request)
    {
        $request->validate([
            'choice' => ['required','integer'],
        ]);

        $game = $request->session()->get('game');

        if (!$game || ($game['status'] ?? 'idle') !== 'playing' || empty($game['current'])) {
            return redirect()->route('game.index');
        }

        $choice = (int) $request->input('choice');
        $current = $game['current'];
        $correct = (int) $current['answer'];

        // Record the asked number (for no repeats)
        $game['asked'][] = $correct;

        $isCorrect = $choice === $correct;
        if ($isCorrect) {
            $game['score']++;
        }

        $game['count']++;
        $game['last'] = [
            'question' => $current['text'],
            'source'   => $current['source'],
            'answer'   => $correct,
            'picked'   => $choice,
            'correct'  => $isCorrect,
        ];

        if (!$isCorrect || $game['count'] >= $game['limit']) {
            $game['status']  = 'over';
            $game['current'] = null;
            $request->session()->put('game', $game);
            return redirect()->route('game.index');
        }

        try {
            $q = $this->questions->generateQuestion($game['asked']);
            $game['current'] = $q;
            $game['error'] = null;
        } catch (\Throwable $e) {
            $game['current'] = null;
            $game['error'] = 'Failed to load a question. Press "Continue" to try again.';
        }

        $request->session()->put('game', $game);

        return redirect()->route('game.index');
    }

    public function restart(Request $request)
    {
        $request->session()->forget('game');
        return redirect()->route('game.index');
    }
}
