import { login2FA } from './lib/ovo-api.mjs';

export default async (req) => {
  if (req.method !== 'POST') {
    return Response.json({ error: 'Method not allowed' }, { status: 405 });
  }
  try {
    const { phone, deviceId } = await req.json();
    if (!phone) return Response.json({ error: 'phone is required' }, { status: 400 });

    const resolvedDeviceId = deviceId || crypto.randomUUID();
    const result = await login2FA(phone, resolvedDeviceId);
    return Response.json({ ok: true, ...result });
  } catch (err) {
    return Response.json(
      { error: err.message, details: err.data },
      { status: err.status || 500 },
    );
  }
};

export const config = {
  path: '/api/ovo/login',
};
