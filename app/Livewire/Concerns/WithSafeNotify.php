<?php

namespace App\Livewire\Concerns;

/**
 * Trait to safely dispatch browser events from Livewire components.
 *
 * Wraps dispatch() in try-catch so that if the event bus has stale
 * listeners (e.g. after multiple navigations without cleanup), the
 * component doesn't crash and trigger a forced page reload.
 */
trait WithSafeNotify
{
    /**
     * Dispatch a 'notify' browser event safely, catching any Throwable.
     */
    protected function safeDispatch(string $type, string $message): void
    {
        try {
            $this->dispatch('notify', type: $type, message: $message);
        } catch (\Throwable) {
            // Silently ignore – the notification is non-critical UI feedback.
        }
    }

    /**
     * Dispatch a 'refresh-icons' browser event safely, catching any Throwable.
     */
    protected function safeRefreshIcons(): void
    {
        try {
            $this->dispatch('refresh-icons');
        } catch (\Throwable) {
            // Silently ignore – icon refresh is non-critical.
        }
    }
}
