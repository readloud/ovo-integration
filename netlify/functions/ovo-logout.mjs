import { logout } from './lib/ovo-api.mjs';

export default async (req) => {
  const token = req.headers.get('Authorization');
  if (!token) {
    return Response.json({ error: 'Authorization header required' }, { status: 401 });
  }
  try {
    await logout(token);
    return Response.json({ ok: true, message: 'Logged out successfully' });
  } catch (err) {
    return Response.json({ error: err.message }, { status: err.status || 500 });
  }
};

export const config = {
  path: '/api/ovo/logout',
};
