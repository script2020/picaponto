<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificacaoController extends Controller
{
    public function marcarLida(string $id)
    {
        $notificacao = Auth::user()->notifications()->findOrFail($id);
        $notificacao->markAsRead();

        $url = $notificacao->data['url'] ?? '/tarefas';

        return redirect($url);
    }

    public function marcarTodasLidas()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back();
    }
}
