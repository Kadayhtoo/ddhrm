<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveStatusChanged extends Notification
{
    use Queueable;

    protected $leaveRequest;
    protected $actor;
    /**
     * Create a new notification instance.
     */
    public function __construct($leaveRequest, $actor)
    {
        $this->leaveRequest = $leaveRequest;
        $this->actor = $actor;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $actorName = $this->actor->name;
        $action = $this->leaveRequest->status; 
        
        $role = $this->actor->hasRoleSlug('hr') ? 'HR' : 'Approver';

        return [
            'message' => "{$role} ({$actorName}) has {$action} the leave request.",
            'url'     => '/leave-requests/',
        ];
    }
}
