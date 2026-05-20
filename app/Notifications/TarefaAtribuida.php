<?php

namespace App\Notifications;

use App\Models\Tarefa;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TarefaAtribuida extends Notification
{
    use Queueable;

    public function __construct(
        public Tarefa $tarefa,
        public User $atribuidaPor
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nova tarefa atribuída: ' . $this->tarefa->titulo)
            ->greeting('Olá, ' . $notifiable->name . '!')
            ->line($this->atribuidaPor->name . ' atribuiu-te uma nova tarefa.')
            ->line('**' . $this->tarefa->titulo . '**')
            ->line('Prioridade: ' . $this->tarefa->prioridade_label)
            ->when($this->tarefa->data, fn($mail) => $mail->line('Data: ' . $this->tarefa->data->format('d/m/Y')))
            ->action('Ver Tarefa', url('/tarefas?detalhe=' . $this->tarefa->id))
            ->salutation('Picaponto');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'tarefa_id'     => $this->tarefa->id,
            'tarefa_titulo' => $this->tarefa->titulo,
            'mensagem'      => $this->atribuidaPor->name . ' atribuiu-te a tarefa "' . $this->tarefa->titulo . '"',
            'url'           => '/tarefas?detalhe=' . $this->tarefa->id,
            'tipo'          => 'atribuida',
        ];
    }
}
