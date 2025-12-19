<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class TestController extends Controller
{
    public function index()
    {
        return Inertia::render('Test/Index', [
            'message' => '¡Hola desde Laravel + Inertia.js + React!',
            'timestamp' => now()->toISOString(),
            'user' => auth()->user(),
            'features' => [
                'Inertia.js integrado',
                'React 18 con hooks',
                'Shadcn/ui components',
                'Laravel 10 backend',
                'Vite para desarrollo rápido'
            ]
        ]);
    }
}
