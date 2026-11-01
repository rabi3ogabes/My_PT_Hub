<?php

namespace App\Notifications;

/** Ad-hoc notification for services (automations, reminders) that only need title/body/link. */
class GenericNotification extends AppNotification
{
    /**
     * @param  array<string, string>  $data
     */
    public function __construct(
        private readonly string $categoryKey,
        private readonly string $titleText,
        private readonly string $bodyText,
        private readonly ?string $link = null,
        private readonly array $data = [],
        private readonly ?string $action = null,
    ) {}

    public function category(): string
    {
        return $this->categoryKey;
    }

    public function title(object $notifiable): string
    {
        return $this->titleText;
    }

    public function body(object $notifiable): string
    {
        return $this->bodyText;
    }

    public function deepLink(): ?string
    {
        return $this->link;
    }

    public function pushData(): array
    {
        return $this->data;
    }

    public function actionLabel(object $notifiable): ?string
    {
        return $this->action;
    }
}
