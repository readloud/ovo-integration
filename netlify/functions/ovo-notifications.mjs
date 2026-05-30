import { getAllNotifications, getUnreadCount } from './lib/ovo-api.mjs';

export default async (req) => {
  const token = req.headers.get('Authorization');
  if (!token) {
    return Response.json({ error: 'Authorization header required' }, { status: 401 });
  }
  try {
    const [notifications, unread] = await Promise.all([
      getAllNotifications(token).catch(() => null),
      getUnreadCount(token).catch(() => null),
    ]);
    return Response.json({ ok: true, notifications, unread });
  } catch (err) {
    return Response.json({ error: err.message }, { status: err.status || 500 });
  }
};

export const config = {
  path: '/api/ovo/notifications',
};
