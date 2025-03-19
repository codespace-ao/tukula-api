<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class CustomResetPassword extends Notification
{
    use Queueable;

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $resetUrl = URL::temporarySignedRoute(
            config('app.frontend_url') . '/reset-password',
            Carbon::now()->addMinutes(config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60)),
            ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()]
        );

        return (new MailMessage)
            ->subject('Redefina sua Senha - Tukula 🔑')
            ->greeting('Olá, Amigo da Tukula! 🌿')
            ->line('Clique no botão abaixo para redefinir sua senha e continuar conosco! 🔧')
            ->action('Redefinir Senha ✅', $resetUrl)
            ->line('Se você não solicitou isso, ignore este e-mail. 🚫')
            ->line('O link expira em 60 minutos. ⏰')
            ->salutation('Com Carinho, Equipe Tukula 💚');
    }

    public function toText($notifiable)
    {
        $resetUrl = URL::temporarySignedRoute(
            'password.reset',
            Carbon::now()->addMinutes(config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60)),
            ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()]
        );

        return "Tukula\n\nOlá, Amigo da Tukula! 🌿\n\nClique no link abaixo para redefinir sua senha e continuar conosco! 🔧\n" . $resetUrl . "\n\nSe você não solicitou isso, ignore este e-mail. 🚫\n\nO link expira em 60 minutos. ⏰\n\nCom Carinho,\nEquipe Tukula 💚";
    }
}
