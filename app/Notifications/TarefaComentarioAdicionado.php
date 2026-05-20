<?php

namespace App\Notifications;

use App\Models\Tarefa;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TarefaComentarioAdicionado extends Notification
{
    use Queueable;

    public function __construct(
        public Tarefa $tarefa,
        public User $comentadoPor,
        public string $conteudo
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Novo comentário em: ' . $this->tarefa->titulo)
            ->greeting('Olá, ' . $notifiable->name . '!')
            ->line($this->comentadoPor->name . ' comentou na tarefa "' . $this->tarefa->titulo . '".')
            ->line('> ' . $this->conteudo)
            ->action('Ver Comentário', url('/tarefas?detalhe=' . $this->tarefa->id))
            ->salutation('Picaponto');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'tarefa_id'     => $this->tarefa->id,
            'tarefa_titulo' => $this->tarefa->titulo,
            'mensagem'      => $this->comentadoPor->name . ' comentou em "' . $this->tarefa->titulo . '"',
            'url'           => '/tarefas?detalhe=' . $this->tarefa->id,
            'tipo'          => 'comentario',
        ];
    }
}
