import { ref } from 'vue';

export interface Notification {
  id: string;
  message: string;
  type: 'success' | 'error' | 'info' | 'warning';
  duration?: number;
}

const notifications = ref<Notification[]>([]);

export function useNotification() {
  const addNotification = (
    message: string,
    type: 'success' | 'error' | 'info' | 'warning' = 'info',
    duration: number = 3000
  ) => {
    const id = Math.random().toString(36).substr(2, 9);
    const notification: Notification = {
      id,
      message,
      type,
      duration,
    };

    notifications.value.push(notification);

    if (duration > 0) {
      setTimeout(() => {
        removeNotification(id);
      }, duration);
    }

    return id;
  };

  const removeNotification = (id: string) => {
    const index = notifications.value.findIndex((n) => n.id === id);
    if (index > -1) {
      notifications.value.splice(index, 1);
    }
  };

  const success = (message: string, duration?: number) =>
    addNotification(message, 'success', duration);
  const error = (message: string, duration?: number) => addNotification(message, 'error', duration);
  const info = (message: string, duration?: number) => addNotification(message, 'info', duration);
  const warning = (message: string, duration?: number) =>
    addNotification(message, 'warning', duration);

  return {
    notifications,
    addNotification,
    removeNotification,
    success,
    error,
    info,
    warning,
  };
}
