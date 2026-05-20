<?php

namespace App\Notifications;

use App\Models\Tarefa;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TarefaEstadoAlterado extends Notification
{
    use Queueable;

    public function __construct(
        public Tarefa $tarefa,
        public User $alteradoPor,
        public string $estadoAnterior
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tarefa atualizada: ' . $this->tarefa->titulo)
            ->greeting('Olá, ' . $notifiable->name . '!')
            ->line($this->alteradoPor->name . ' atualizou o estado da tua tarefa.')
            ->line('**' . $this->tarefa->titulo . '**')
            ->line($this->tarefa->estado_label_from($this->estadoAnterior) . ' → ' . $this->tarefa->estado_label)
            ->action('Ver Tarefa', url('/tarefas?detalhe=' . $this->tarefa->id))
            ->salutation('Picaponto');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'tarefa_id'     => $this->tarefa->id,
            'tarefa_titulo' => $this->tarefa->titulo,
            'mensagem'      => $this->alteradoPor->name . ' alterou o estado de "' . $this->tarefa->titulo . '" para ' . $this->tarefa->estado_label,
            'url'           => '/tarefas?detalhe=' . $this->tarefa->id,
            'tipo'          => 'estado',
        ];
    }
}
