<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class CustomVerifyEmail extends Notification
{
    use Queueable;

    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
            ['id' => $this->user->id, 'hash' => sha1($this->user->getEmailForVerification())]
        );

        return (new MailMessage)
            ->subject('Verifique seu E-mail - Tukula')
            ->greeting('Olá, Amigo da Tukula! 🌱')
            ->line('Clique abaixo para verificar seu e-mail e junte-se à nossa missão! 🌍')
            ->action('Verificar E-mail', $verificationUrl)
            ->success('Seu e-mail será essencial para nossa causa!')
            ->line('Verifique antes que o link expire! ⚠️')
            ->line('Se você não criou uma conta, ignore este e-mail.')
            ->salutation('Com Carinho, Equipe Tukula 💚');
    }

    public function toText($notifiable)
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
            ['id' => $this->user->id, 'hash' => sha1($this->user->getEmailForVerification())]
        );

        return "Tukula\n\nOlá, Amigo da Tukula! 🌿\n\nClique abaixo para verificar seu e-mail e junte-se à nossa missão! 🌍\n" . $verificationUrl . "\n\nSe tiver dificuldades, copie o link: " . $verificationUrl . " 🚀\n\nSe você não criou uma conta, ignore este e-mail. 🚫\n\nCom Carinho,\nEquipe Tukula 💚";
    }
}
