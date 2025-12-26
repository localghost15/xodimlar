<?php

namespace App\Service;

use App\Entity\AbsenceRequest;

class TelegramService
{
    public function notifyManager(AbsenceRequest $request): void
    {
        // В будущем здесь будет реальный API запрос к Telegram Bot
        // Пока просто выводим данные в лог или dump (для dev режима)

        // В реальном Symfony проекте лучше использовать LoggerInterface
        // error_log('Telegram Notification: New Absence Request from ' . $request->getUser()->getFullName());

        // Для демонстрации (так как dump не виден в API response обычно, используем error_log)
        error_log(sprintf(
            "TELEGRAM NOTIFY: User: %s, Data: %s, Reason: %s",
            $request->getUser()->getFullName(),
            $request->getDate()->format('Y-m-d'),
            $request->getReason()
        ));
    }
}
