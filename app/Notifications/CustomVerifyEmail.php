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
            ->greeting('Olá ' . $this->user->name . '!')
            ->line('Obrigado por se registrar no Tukula. Clique no botão abaixo para verificar seu e-mail.')
            ->action('Verificar E-mail', $verificationUrl)
            ->line('Este link expirará em ' . config('auth.verification.expire', 60) . ' minutos.')
            ->line('Se você não criou uma conta, ignore este e-mail.')
            ->salutation('Atenciosamente, Equipe Tukula');
    }

    public function toText($notifiable)
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
            ['id' => $this->user->id, 'hash' => sha1($this->user->getEmailForVerification())]
        );

        return "Tukula\n\nOlá " . ($this->user->name ?? 'amigo') . ",\n\nObrigado por se juntar à Tukula! Estamos felizes por você fazer parte da nossa missão de combater o desperdício alimentar e promover sustentabilidade.\n\nPor favor, verifique seu e-mail clicando no link abaixo:\n" . $verificationUrl . "\n\nEste link expirará em " . config('auth.verification.expire', 60) . " minutos.\n\nSe você não solicitou isso, por favor, ignore este e-mail. Juntos, podemos fazer a diferença!\n\nCom carinho,\nEquipe Tukula\n\n© " . date('Y') . " Tukula. Desinscrever-se: " . config('app.url') . "/unsubscribe";
    }
}