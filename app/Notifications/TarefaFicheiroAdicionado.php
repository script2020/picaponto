<?php

namespace App\Notifications;

use App\Models\Tarefa;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TarefaFicheiroAdicionado extends Notification
{
    use Queueable;

    public function __construct(
        public Tarefa $tarefa,
        public User $submetidoPor,
        public string $nomeOriginal
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Novo ficheiro em: ' . $this->tarefa->titulo)
            ->greeting('Olá, ' . $notifiable->name . '!')
            ->line($this->submetidoPor->name . ' submeteu um ficheiro na tarefa "' . $this->tarefa->titulo . '".')
            ->line('Ficheiro: **' . $this->nomeOriginal . '**')
            ->action('Ver Tarefa', url('/tarefas?detalhe=' . $this->tarefa->id))
            ->salutation('Picaponto');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'tarefa_id'     => $this->tarefa->id,
            'tarefa_titulo' => $this->tarefa->titulo,
            'mensagem'      => $this->submetidoPor->name . ' submeteu "' . $this->nomeOriginal . '" em "' . $this->tarefa->titulo . '"',
            'url'           => '/tarefas?detalhe=' . $this->tarefa->id,
            'tipo'          => 'ficheiro',
        ];
    }
}
